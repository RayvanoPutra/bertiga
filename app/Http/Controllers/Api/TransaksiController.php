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
        }

        return response()->json(['message' => 'Transaksi berhasil disetujui.']);
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
