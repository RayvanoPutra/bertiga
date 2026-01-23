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
    /**
     * FUNGSI BARU: Potongan Massal (Bisa dijalankan lewat Task Scheduler atau Tombol)
     * Ini akan mencari semua nasabah siswa yang belum dipotong admin di tahun berjalan.
     */
    public function cronJobPotongan(Request $request)
    {
        try {
            // 1. Ambil Pengaturan
            $setPotongan = Pengaturan::where('nama_pengaturan', 'jumlah_potongan')->first();
            $setTipe = Pengaturan::where('nama_pengaturan', 'tipe_potongan')->first();
            
            $nominal = $setPotongan ? (int)$setPotongan->nilai : 0;
            $tipe = $setTipe ? $setTipe->nilai : 'tahun'; // bulan / tahun
            
            if ($nominal <= 0) return response()->json(['message' => 'Nominal potongan 0, dibatalkan.'], 200);

            $tahunIni = date('Y');
            $bulanIni = date('m');

            // 2. Cari Nasabah Siswa yang saldonya cukup dan BELUM bayar di periode ini
            $nasabahs = Nasabah::where('jenis_rekening', 'siswa')
                ->where('saldo', '>=', $nominal)
                ->whereDoesntHave('transaksi', function($q) use ($tipe, $tahunIni, $bulanIni) {
                    $q->whereHas('jenisTransaksi', function($j) {
                        $j->where('nama_jenis', 'Biaya Admin');
                    });
                    
                    if ($tipe == 'tahun') {
                        $q->whereYear('tgl_transaksi', $tahunIni);
                    } else {
                        $q->whereYear('tgl_transaksi', $tahunIni)->whereMonth('tgl_transaksi', $bulanIni);
                    }
                })->get();

            $count = 0;
            $jenisAdmin = JenisTransaksi::where('nama_jenis', 'Biaya Admin')->first();

            if (!$jenisAdmin) return response()->json(['message' => 'Jenis Transaksi Biaya Admin tidak ditemukan.'], 404);

            foreach ($nasabahs as $nasabah) {
                DB::transaction(function () use ($nasabah, $nominal, $jenisAdmin, $tipe, $tahunIni, $bulanIni, &$count) {
                    $saldo_skrg = $nasabah->saldo;
                    $saldo_baru = $saldo_skrg - $nominal;

                    $nasabah->update(['saldo' => $saldo_baru]);

                    Transaksi::create([
                        'kode_transaksi' => 'ADM-AUTO-' . time() . '-' . rand(100, 999),
                        'no_rekening' => $nasabah->no_rekening,
                        'kode_jenis' => $jenisAdmin->kode_jenis,
                        'tgl_transaksi' => now(),
                        'jumlah' => $nominal,
                        'status' => 'success',
                        'keterangan_nasabah' => 'Potongan Otomatis Admin ' . ($tipe == 'tahun' ? "Tahun $tahunIni" : "Bulan $bulanIni/$tahunIni"),
                        'nama_saat_transaksi' => $nasabah->nama,
                        'saldo_sebelum' => $saldo_skrg,
                        'saldo_setelah' => $saldo_baru,
                    ]);
                    $count++;
                });
            }

            return response()->json(['message' => "Berhasil memproses $count nasabah."]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // --- REQUEST SETOR (Android) ---
    public function requestSetor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|integer|min:1000',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $nasabah = $request->user();
        $jenisSetor = JenisTransaksi::where('nama_jenis', 'Setor Tunai')->firstOrFail();

        $transaksi = Transaksi::create([
            'kode_transaksi' => 'TRX-' . time() . '-' . rand(100, 999),
            'no_rekening' => $nasabah->no_rekening,
            'kode_jenis' => $jenisSetor->kode_jenis,
            'jumlah' => $request->jumlah,
            'status' => 'pending',
            'keterangan_nasabah' => 'Request Setor Tunai',
            'nama_saat_transaksi' => $nasabah->nama,
            'kelas_saat_transaksi' => $nasabah->kelas ? $nasabah->kelas->nama_kelas : null,
            'jurusan_saat_transaksi' => ($nasabah->kelas && $nasabah->kelas->jurusan) ? $nasabah->kelas->jurusan->nama_jurusan : null,
            'saldo_sebelum' => $nasabah->saldo,
            'saldo_setelah' => 0,
        ]);

        return response()->json(['message' => 'Transaksi berhasil dibuat.', 'data' => $transaksi], 201);
    }

    // --- REQUEST TARIK (Android) ---
    public function requestTarik(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|integer|min:1000',
            'keterangan_nasabah' => 'required|string|min:5',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $nasabah = $request->user();
        if ($nasabah->saldo < $request->jumlah) {
            return response()->json(['message' => 'Saldo Anda tidak mencukupi.'], 422);
        }

        $jenisTarik = JenisTransaksi::where('nama_jenis', 'Tarik Tunai')->firstOrFail();

        $transaksi = Transaksi::create([
            'kode_transaksi' => 'TRX-' . time() . '-' . rand(100, 999),
            'no_rekening' => $nasabah->no_rekening,
            'kode_jenis' => $jenisTarik->kode_jenis,
            'jumlah' => $request->jumlah,
            'status' => 'pending',
            'keterangan_nasabah' => $request->keterangan_nasabah,
            'nama_saat_transaksi' => $nasabah->nama,
            'kelas_saat_transaksi' => $nasabah->kelas ? $nasabah->kelas->nama_kelas : null,
            'jurusan_saat_transaksi' => ($nasabah->kelas && $nasabah->kelas->jurusan) ? $nasabah->kelas->jurusan->nama_jurusan : null,
            'saldo_sebelum' => $nasabah->saldo,
            'saldo_setelah' => 0,
        ]);

        return response()->json(['message' => 'Permintaan tarik tunai berhasil.', 'data' => $transaksi], 201);
    }

    // --- APPROVE (Web Petugas) ---
    public function approve(Request $request, $kode_transaksi)
    {
        try {
            DB::transaction(function () use ($request, $kode_transaksi) {
                $transaksi = Transaksi::with('jenisTransaksi')->where('kode_transaksi', $kode_transaksi)->firstOrFail();

                if ($transaksi->status != 'pending') {
                    throw ValidationException::withMessages(['status' => 'Transaksi sudah diproses.']);
                }

                $nasabah = Nasabah::where('no_rekening', $transaksi->no_rekening)->lockForUpdate()->firstOrFail();
                $saldo_sebelum = $nasabah->saldo;
                $jenisNama = $transaksi->jenisTransaksi->nama_jenis; 

                if ($jenisNama == 'Setor Tunai') {
                    $saldo_setelah = $saldo_sebelum + $transaksi->jumlah;
                } elseif ($jenisNama == 'Tarik Tunai') {
                    if ($saldo_sebelum < $transaksi->jumlah) {
                        throw ValidationException::withMessages(['saldo' => 'Saldo nasabah tidak mencukupi.']);
                    }
                    $saldo_setelah = $saldo_sebelum - $transaksi->jumlah;
                } else {
                    throw new \Exception("Jenis transaksi tidak valid.");
                }

                $nasabah->update(['saldo' => $saldo_setelah]);
                $transaksi->update([
                    'status' => 'success',
                    'kode_petugas' => $request->user()->kode_petugas,
                    'tgl_transaksi' => now(),
                    'saldo_sebelum' => $saldo_sebelum,
                    'saldo_setelah' => $saldo_setelah,
                ]);

                // --- LOGIKA AUTO-DEBIT (Trigger saat setor sebagai backup) ---
                if ($jenisNama == 'Setor Tunai' && $nasabah->jenis_rekening == 'siswa') {
                    $setPotongan = Pengaturan::where('nama_pengaturan', 'jumlah_potongan')->first();
                    $nominalBiaya = $setPotongan ? (int)$setPotongan->nilai : 12000;
                    
                    $tahunIni = date('Y');
                    $sudahBayar = Transaksi::where('no_rekening', $nasabah->no_rekening)
                        ->whereHas('jenisTransaksi', fn($q) => $q->where('nama_jenis', 'Biaya Admin'))
                        ->whereYear('tgl_transaksi', $tahunIni)
                        ->exists();

                    if (!$sudahBayar && $nasabah->saldo >= $nominalBiaya) {
                        $jenisAdmin = JenisTransaksi::where('nama_jenis', 'Biaya Admin')->first();
                        if ($jenisAdmin) {
                            $saldo_skrg = $nasabah->saldo;
                            $saldo_baru = $saldo_skrg - $nominalBiaya;
                            $nasabah->update(['saldo' => $saldo_baru]);

                            Transaksi::create([
                                'kode_transaksi' => 'ADM-' . time() . '-' . rand(100, 999),
                                'no_rekening' => $nasabah->no_rekening,
                                'kode_jenis' => $jenisAdmin->kode_jenis,
                                'kode_petugas' => $request->user()->kode_petugas,
                                'tgl_transaksi' => now(),
                                'jumlah' => $nominalBiaya,
                                'status' => 'success',
                                'keterangan_nasabah' => 'Potongan Admin Tahun ' . $tahunIni,
                                'nama_saat_transaksi' => $nasabah->nama,
                                'saldo_sebelum' => $saldo_skrg,
                                'saldo_setelah' => $saldo_baru,
                            ]);
                        }
                    }
                }
            });
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Transaksi berhasil disetujui.']);
    }

    // --- REJECT ---
    public function reject(Request $request, $kode_transaksi)
    {
        $transaksi = Transaksi::where('kode_transaksi', $kode_transaksi)->where('status', 'pending')->firstOrFail();
        $transaksi->update([
            'status' => 'rejected',
            'kode_petugas' => $request->user()->kode_petugas,
            'tgl_transaksi' => now(),
        ]);
        return response()->json(['message' => 'Transaksi ditolak.']);
    }

    // --- GET DATA (Pending & History) ---
    public function getPending() {
        return response()->json(Transaksi::with(['nasabah', 'jenisTransaksi'])->where('status', 'pending')->orderBy('created_at', 'asc')->get());
    }

    public function getHistoryNasabah(Request $request) {
        return response()->json(Transaksi::with('jenisTransaksi')->where('no_rekening', $request->user()->no_rekening)->where('status', '!=', 'pending')->orderBy('tgl_transaksi', 'desc')->get());
    }

    public function getHistoryAdmin() {
        return response()->json(Transaksi::with(['nasabah', 'jenisTransaksi', 'petugas'])->where('status', '!=', 'pending')->orderBy('tgl_transaksi', 'desc')->get());
    }
}