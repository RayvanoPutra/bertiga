<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Nasabah;
use App\Models\JenisTransaksi;
<<<<<<< HEAD
use App\Models\Pengaturan;
=======
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransaksiController extends Controller
{
<<<<<<< HEAD
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
=======
    //method nasabah do transaksi
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
    public function requestSetor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|integer|min:1000',
        ]);
<<<<<<< HEAD
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
=======
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

>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
    public function requestTarik(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|integer|min:1000',
            'keterangan_nasabah' => 'required|string|min:5',
        ]);
<<<<<<< HEAD
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
=======
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
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
            'jumlah' => $request->jumlah,
            'status' => 'pending',
            'keterangan_nasabah' => $request->keterangan_nasabah,
            'nama_saat_transaksi' => $nasabah->nama,
<<<<<<< HEAD
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
=======
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
    public function approve(Request $request, $id_transaksi)
    {
        // Gunakan DB::transaction untuk keamanan data
        try {
            DB::transaction(function () use ($request, $id_transaksi) {

                // 1. Ambil data transaksi & nasabah. Kunci datanya agar aman.
                $transaksi = Transaksi::with('jenisTransaksi')
                    ->where('id', $id_transaksi)
                    ->firstOrFail();

                // 2. Cek apakah transaksi masih 'pending'
                if ($transaksi->status != 'pending') {
                    throw ValidationException::withMessages([
                        'status' => ['Transaksi ini sudah diproses sebelumnya.']
                    ]);
                }

                $nasabah = Nasabah::where('no_rekening', $transaksi->no_rekening)
                    ->lockForUpdate() // KUNCI baris nasabah ini agar tidak ada proses lain
                    ->firstOrFail();

                // 3. Ambil data 'live' untuk snapshot saldo
                $saldo_sebelum = $nasabah->saldo;
                $saldo_setelah = 0;
                $jenis = $transaksi->jenisTransaksi->nama_jenis;

                // 4. Hitung saldo baru berdasarkan jenis transaksi
                if ($jenis == 'Setor Tunai') {
                    $saldo_setelah = $saldo_sebelum + $transaksi->jumlah;
                } elseif ($jenis == 'Tarik Tunai') {
                    // Cek saldo sekali lagi (just in case)
                    if ($saldo_sebelum < $transaksi->jumlah) {
                        throw ValidationException::withMessages([
                            'saldo' => ['Saldo nasabah tidak mencukupi untuk ditarik.']
                        ]);
                    }
                    $saldo_setelah = $saldo_sebelum - $transaksi->jumlah;
                } else {
                    throw new \Exception('Jenis transaksi tidak valid untuk approval.');
                }

                // 5. UPDATE tabel nasabah (saldo 'live' nya)
                $nasabah->update(['saldo' => $saldo_setelah]);

                // 6. UPDATE tabel transaksi (snapshot & status)
                $transaksi->update([
                    'status' => 'approved',
                    'petugas_id' => $request->user()->id, // Petugas yg sedang login
                    'tgl_transaksi' => now(), // Diisi saat di-approve
                    'saldo_sebelum' => $saldo_sebelum, // Snapshot saldo sebelum
                    'saldo_setelah' => $saldo_setelah, // Snapshot saldo setelah
                ]);
            });
        } catch (ValidationException $e) {
            // Tangkap error validasi (saldo tidak cukup / status salah)
            return response()->json(['message' => 'Gagal menyetujui transaksi.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Tangkap error lainnya
            return response()->json(['message' => 'Terjadi kesalahan server.', 'error' => $e->getMessage()], 500);
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
        }

        return response()->json(['message' => 'Transaksi berhasil disetujui.']);
    }

<<<<<<< HEAD
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
=======
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
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
