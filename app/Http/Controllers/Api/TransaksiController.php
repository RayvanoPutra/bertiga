<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Nasabah;
use App\Models\JenisTransaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransaksiController extends Controller
{
    //method nasabah do transaksi
    public function requestSetor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|integer|min:1000',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //ambil data nasabah yg sdg login melalui token
        $nasabah = $request->user();
        //ambil jurusan dan jelas jika ia siswa utk snapshot
        $kelas = $nasabah->kelas;
        $jurusan = $kelas ? $kelas->jurusan : null;
        $jenisSetor = JenisTransaksi::where('nama_jenis', 'Setor Tunai')->firstOrFail();

        //buat transaksi status menunggu
        $transaksi = Transaksi::create([
            'no_rekening' => $nasabah->no_rekening,
            'jenis_transaksi_id' => $jenisSetor->id,
            'petugas_id' => null, //null karena blm ada petugas yg approve
            'tgl_transaksi' => null, // Belum di-approve
            'jumlah' => $request->jumlah,
            'status' => 'pending',
            'keterangan_nasabah' => 'Request Setor Tunai',
            //data historis
            'nama_saat_transaksi' => $nasabah->nama,
            'kelas_saat_transaksi' => $kelas ? $kelas->nama_kelas : null,
            'jurusan_saat_transaksi' => $jurusan ? $jurusan->nama_jurusan : null,
            'saldo_sebelum' => 0,
            'saldo_setelah' => 0,
        ]);
        return response()->json([
            'message' => 'Transaksi berhasil dibuat dan menunggu disetujui petugas.',
            'data' => $transaksi
        ], 201);
    }

    public function requestTarik(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|integer|min:1000',
            'keterangan_nasabah' => 'required|string|min:5',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $nasabah = $request->user();

        //cek saldo asli
        if ($nasabah->saldo < $request->jumlah) {
            return response()->json(['message' => 'Saldo Anda tidak mencukupi untuk melakukan penarikan ini.'], 422);
        }

        $kelas = $nasabah->kelas;
        $jurusan = $kelas ? $kelas->jurusan : null;
        $jenisTarik = JenisTransaksi::where('nama_jenis', 'Tarik Tunai')->firstOrFail();

        $transaksi = Transaksi::create([
            'no_rekening' => $nasabah->no_rekening,
            'jenis_transaksi_id' => $jenisTarik->id,
            'jumlah' => $request->jumlah,
            'status' => 'pending',
            'keterangan_nasabah' => $request->keterangan_nasabah,
            'nama_saat_transaksi' => $nasabah->nama,
            'kelas_saat_transaksi' => $kelas ? $kelas->nama_kelas : null,
            'jurusan_saat_transaksi' => $jurusan ? $jurusan->nama_jurusan : null,
            'saldo_sebelum' => 0,
            'saldo_setelah' => 0,
        ]);

        return response()->json([
            'message' => 'Permintaan tarik tunai berhasil dibuat dan menunggu persetujuan.',
            'data' => $transaksi
        ], 201);
    }

    //method petugas di transaksi
    // --- GANTI KODE LAMA DENGAN INI ---
    // --- METHOD APPROVE YANG SUDAH DISESUAIKAN ---
    public function approve(Request $request, $id_transaksi)
    {
        try {
            DB::transaction(function () use ($request, $id_transaksi) {
                
                // 1. Ambil & Validasi Transaksi Utama
                $transaksi = Transaksi::with('jenisTransaksi')->where('id', $id_transaksi)->firstOrFail();
                if ($transaksi->status != 'pending') {
                    throw ValidationException::withMessages(['status' => 'Transaksi sudah diproses.']);
                }
                
                // Kunci data nasabah
                $nasabah = Nasabah::where('no_rekening', $transaksi->no_rekening)->lockForUpdate()->firstOrFail();
                $saldo_sebelum = $nasabah->saldo;
                $jenis = $transaksi->jenisTransaksi->nama_jenis;
                $saldo_setelah = 0;

                // 2. Proses Transaksi UTAMA (Setor/Tarik)
                if ($jenis == 'Setor Tunai') {
                    $saldo_setelah = $saldo_sebelum + $transaksi->jumlah;
                } elseif ($jenis == 'Tarik Tunai') {
                     if ($saldo_sebelum < $transaksi->jumlah) throw new \Exception("Saldo kurang");
                     $saldo_setelah = $saldo_sebelum - $transaksi->jumlah;
                } else {
                     throw new \Exception("Jenis transaksi tidak valid.");
                }

                // Simpan perubahan saldo ke Nasabah
                $nasabah->update(['saldo' => $saldo_setelah]);
                
                // Update status transaksi utama jadi APPROVED
                $transaksi->update([
                    'status' => 'approved',
                    'petugas_id' => $request->user()->id,
                    'tgl_transaksi' => now(),
                    'saldo_sebelum' => $saldo_sebelum,
                    'saldo_setelah' => $saldo_setelah,
                ]);

                // ============================================================
                // 3. FITUR AUTO-DEBIT BIAYA ADMIN (DISESUAIKAN)
                // ============================================================
                
                if ($jenis == 'Setor Tunai') {
                    
                    // A. Ambil nominal biaya dari tabel 'pengaturan'
                    // PERBAIKAN: Menggunakan 'nama_pengaturan' dan 'nilai'
                    $settingBiaya = \App\Models\Pengaturan::where('nama_pengaturan', 'biaya_admin')->first(); 
                    
                    // Jika setting tidak ditemukan, default ke 12000
                    $nominalBiaya = $settingBiaya ? (int)$settingBiaya->nilai : 12000; 

                    // B. Cek: Apakah nasabah SUDAH bayar admin tahun ini?
                    $tahunIni = date('Y');
                    $sudahBayar = Transaksi::where('no_rekening', $nasabah->no_rekening)
                        ->whereHas('jenisTransaksi', function($q) {
                            $q->where('nama_jenis', 'Biaya Admin');
                        })
                        ->whereYear('created_at', $tahunIni)
                        ->exists();

                    // C. Jika BELUM bayar dan Saldo Cukup
                    if (!$sudahBayar && $nasabah->saldo >= $nominalBiaya) {
                        
                        $jenisAdmin = JenisTransaksi::where('nama_jenis', 'Biaya Admin')->first();

                        if ($jenisAdmin) {
                            // --- LAKUKAN PEMOTONGAN ---
                            $saldo_sekarang = $nasabah->saldo;
                            $saldo_baru = $saldo_sekarang - $nominalBiaya;

                            // 1. Kurangi Saldo
                            $nasabah->update(['saldo' => $saldo_baru]);

                            // 2. Buat Transaksi Biaya Admin
                            Transaksi::create([
                                'no_rekening' => $nasabah->no_rekening,
                                'jenis_transaksi_id' => $jenisAdmin->id,
                                'petugas_id' => $request->user()->id,
                                'tgl_transaksi' => now(),
                                'jumlah' => $nominalBiaya,
                                'status' => 'approved', 
                                'keterangan_nasabah' => 'Potongan Biaya Admin Pertahun ',
                                'nama_saat_transaksi' => $nasabah->nama,
                                'kelas_saat_transaksi' => $nasabah->kelas ? $nasabah->kelas->nama_kelas : null,
                                'jurusan_saat_transaksi' => $nasabah->kelas && $nasabah->kelas->jurusan ? $nasabah->kelas->jurusan->nama_jurusan : null,
                                'saldo_sebelum' => $saldo_sekarang,
                                'saldo_setelah' => $saldo_baru,
                            ]);
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Transaksi disetujui.']);
    }
    public function reject(Request $request, $id_transaksi)
    {
        $transaksi = Transaksi::where('id', $id_transaksi)->where('status', 'pending')->firstOrFail();

        $transaksi->update([
            'status' => 'rejected',
            'petugas_id' => $request->user()->id, // Petugas yg menolak
        ]);

        return response()->json(['message' => 'Transaksi berhasil ditolak.']);
    }

    public function getPending(Request $request)
    {
        $pending = Transaksi::with(['nasabah', 'jenisTransaksi']) // Ambil relasinya
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc') // Tampilkan yg paling lama dulu
            ->get();
        return response()->json($pending);
    }

    public function getHistoryNasabah(Request $request)
    {
        $nasabah = $request->user();
        $history = Transaksi::with('jenisTransaksi') // Ambil relasi jenis
            ->where('no_rekening', $nasabah->no_rekening)
            ->where('status', '!=', 'pending') // Hanya tampilkan yg sudah final
            ->orderBy('tgl_transaksi', 'desc') // Tampilkan yg terbaru dulu
            ->get();
        return response()->json($history);
    }
}
