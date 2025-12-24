<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Nasabah;
use App\Models\JenisTransaksi;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransaksiController extends Controller
{
    // --- REQUEST SETOR (Untuk Android) ---
    public function requestSetor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|integer|min:1000',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $nasabah = $request->user();
        
        // Cari Jenis Transaksi (SETOR)
        $jenisSetor = JenisTransaksi::where('nama_jenis', 'Setor Tunai')->firstOrFail();

        // Ambil Data Snapshot (Kelas & Jurusan saat ini)
        $kelas = $nasabah->kelas;
        $jurusan = $kelas ? $kelas->jurusan : null;

        $transaksi = Transaksi::create([
            'kode_transaksi' => 'TRX-' . time() . '-' . rand(100, 999),
            'no_rekening' => $nasabah->no_rekening,
            'kode_jenis' => $jenisSetor->kode_jenis, // String
            'kode_petugas' => null,
            'tgl_transaksi' => null, // null karena belum disetujui
            'jumlah' => $request->jumlah,
            'status' => 'pending',
            'keterangan_nasabah' => 'Request Setor Tunai',
            
            // --- DATA HISTORIS (PENTING UNTUK LAPORAN) ---
            'nama_saat_transaksi' => $nasabah->nama,
            'kelas_saat_transaksi' => $kelas ? $kelas->nama_kelas : null,
            'jurusan_saat_transaksi' => $jurusan ? $jurusan->nama_jurusan : null,
            'saldo_sebelum' => $nasabah->saldo, // Simpan saldo saat request
            'saldo_setelah' => 0, // Akan diisi saat approve
        ]);

        return response()->json([
            'message' => 'Transaksi berhasil dibuat dan menunggu disetujui petugas.',
            'data' => $transaksi
        ], 201);
    }

    // --- REQUEST TARIK (Untuk Android) ---
    public function requestTarik(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|integer|min:1000',
            'keterangan_nasabah' => 'required|string|min:5',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $nasabah = $request->user();

        // Cek Saldo
        if ($nasabah->saldo < $request->jumlah) {
            return response()->json(['message' => 'Saldo Anda tidak mencukupi.'], 422);
        }

        $jenisTarik = JenisTransaksi::where('nama_jenis', 'Tarik Tunai')->firstOrFail();
        
        // Ambil Data Snapshot
        $kelas = $nasabah->kelas;
        $jurusan = $kelas ? $kelas->jurusan : null;

        $transaksi = Transaksi::create([
            'kode_transaksi' => 'TRX-' . time() . '-' . rand(100, 999),
            'no_rekening' => $nasabah->no_rekening,
            'kode_jenis' => $jenisTarik->kode_jenis,
            'kode_petugas' => null,
            'jumlah' => $request->jumlah,
            'status' => 'pending',
            'keterangan_nasabah' => $request->keterangan_nasabah,
            'tgl_transaksi' => null,
            
            // --- DATA HISTORIS (PENTING) ---
            'nama_saat_transaksi' => $nasabah->nama,
            'kelas_saat_transaksi' => $kelas ? $kelas->nama_kelas : null,
            'jurusan_saat_transaksi' => $jurusan ? $jurusan->nama_jurusan : null,
            'saldo_sebelum' => $nasabah->saldo,
            'saldo_setelah' => 0,
        ]);

        return response()->json([
            'message' => 'Permintaan tarik tunai berhasil dibuat.',
            'data' => $transaksi
        ], 201);
    }

    // --- APPROVE (Untuk Web Petugas) ---
    public function approve(Request $request, $kode_transaksi)
    {
        try {
            DB::transaction(function () use ($request, $kode_transaksi) {

                // 1. Ambil Transaksi
                $transaksi = Transaksi::with('jenisTransaksi')
                    ->where('kode_transaksi', $kode_transaksi)
                    ->firstOrFail();

                if ($transaksi->status != 'pending') {
                    throw ValidationException::withMessages(['status' => 'Transaksi ini sudah diproses.']);
                }

                // 2. Kunci Data Nasabah
                $nasabah = Nasabah::where('no_rekening', $transaksi->no_rekening)
                    ->lockForUpdate()
                    ->firstOrFail();

                $saldo_sebelum = $nasabah->saldo;
                $saldo_setelah = 0;
                
                // Ambil Nama Jenis Transaksi untuk logika
                $jenisNama = $transaksi->jenisTransaksi->nama_jenis; 

                // 3. Hitung Saldo
                if ($jenisNama == 'Setor Tunai') {
                    $saldo_setelah = $saldo_sebelum + $transaksi->jumlah;
                } elseif ($jenisNama == 'Tarik Tunai') {
                    if ($saldo_sebelum < $transaksi->jumlah) {
                        throw ValidationException::withMessages(['saldo' => 'Saldo nasabah tidak mencukupi saat diproses.']);
                    }
                    $saldo_setelah = $saldo_sebelum - $transaksi->jumlah;
                } else {
                    throw new \Exception("Jenis transaksi tidak valid untuk approval manual.");
                }

                // 4. Update Nasabah
                $nasabah->update(['saldo' => $saldo_setelah]);

                // 5. Update Transaksi
                $transaksi->update([
                    'status' => 'success',
                    'kode_petugas' => $request->user()->kode_petugas, // Simpan siapa yg approve
                    'tgl_transaksi' => now(),
                    'saldo_sebelum' => $saldo_sebelum, // Simpan snapshot saldo
                    'saldo_setelah' => $saldo_setelah,
                ]);

                // ============================================================
                // 6. LOGIKA AUTO-DEBIT BIAYA ADMIN (Khusus Setor Tunai Siswa)
                // ============================================================
                
                if ($jenisNama == 'Setor Tunai' && $nasabah->jenis_rekening == 'siswa') {
                    
                    // A. Ambil nominal biaya dari Pengaturan
                    $settingBiaya = Pengaturan::where('nama_pengaturan', 'biaya_admin')->first(); 
                    $nominalBiaya = $settingBiaya ? (int)$settingBiaya->nilai : 12000; 

                    // B. Cek apakah sudah bayar tahun ini?
                    $tahunIni = date('Y');
                    $sudahBayar = Transaksi::where('no_rekening', $nasabah->no_rekening)
                        ->whereHas('jenisTransaksi', function($q) {
                            $q->where('nama_jenis', 'Biaya Admin');
                        })
                        ->whereYear('created_at', $tahunIni)
                        ->exists();

                    // C. Eksekusi Potong Jika Belum Bayar
                    if (!$sudahBayar && $nasabah->saldo >= $nominalBiaya) {
                        
                        $jenisAdmin = JenisTransaksi::where('nama_jenis', 'Biaya Admin')->first();

                        if ($jenisAdmin) {
                            $saldo_skrg = $nasabah->saldo;
                            $saldo_baru = $saldo_skrg - $nominalBiaya;

                            // Kurangi Saldo
                            $nasabah->update(['saldo' => $saldo_baru]);

                            // Buat Transaksi Admin (Langsung Success)
                            Transaksi::create([
                                'kode_transaksi' => 'ADM-' . time() . '-' . rand(100, 999),
                                'no_rekening' => $nasabah->no_rekening,
                                'kode_jenis' => $jenisAdmin->kode_jenis,
                                'kode_petugas' => $request->user()->kode_petugas,
                                'tgl_transaksi' => now(),
                                'jumlah' => $nominalBiaya,
                                'status' => 'success', 
                                'keterangan_nasabah' => 'Potongan Biaya Admin Tahun ' . $tahunIni,
                                
                                // Snapshot Historis
                                'nama_saat_transaksi' => $nasabah->nama,
                                'kelas_saat_transaksi' => $nasabah->kelas ? $nasabah->kelas->nama_kelas : null,
                                'jurusan_saat_transaksi' => $nasabah->kelas && $nasabah->kelas->jurusan ? $nasabah->kelas->jurusan->nama_jurusan : null,
                                'saldo_sebelum' => $saldo_skrg,
                                'saldo_setelah' => $saldo_baru,
                            ]);
                        }
                    }
                }
            });
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Gagal: ' . $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error Server: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Transaksi berhasil disetujui.']);
    }

    // --- REJECT ---
    public function reject(Request $request, $kode_transaksi)
    {
        $transaksi = Transaksi::where('kode_transaksi', $kode_transaksi)
            ->where('status', 'pending')
            ->firstOrFail();

        $transaksi->update([
            'status' => 'rejected',
            'kode_petugas' => $request->user()->kode_petugas,
            'tgl_transaksi' => now(),
        ]);

        return response()->json(['message' => 'Transaksi berhasil ditolak.']);
    }

    // --- GET PENDING ---
    public function getPending(Request $request)
    {
        $pending = Transaksi::with(['nasabah', 'jenisTransaksi'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
        return response()->json($pending);
    }

    // --- GET HISTORY NASABAH ---
    public function getHistoryNasabah(Request $request)
    {
        $nasabah = $request->user();
        $history = Transaksi::with('jenisTransaksi')
            ->where('no_rekening', $nasabah->no_rekening)
            ->where('status', '!=', 'pending')
            ->orderBy('tgl_transaksi', 'desc')
            ->get();
        return response()->json($history);
    }
    
    // --- GET HISTORY ADMIN ---
    public function getHistoryAdmin()
    {
         $history = Transaksi::with(['nasabah', 'jenisTransaksi', 'petugas'])
            ->where('status', '!=', 'pending')
            ->orderBy('tgl_transaksi', 'desc')
            ->get();
        return response()->json($history);
    }
}