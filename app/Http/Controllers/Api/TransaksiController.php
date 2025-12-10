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
    public function requestTransaksi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_jenis' => 'required|in:SETOR,TARIK',
            'jumlah' => 'required|integer|min:1000',
            'keterangan_nasabah' => 'nullable|required_if:kode_jenis,TARIK|string|max:100',
        ], [
            'keterangan_nasabah.required_if' => 'Keterangan wajib diisi untuk penarikan.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //ambil data nasabah yg sdg login melalui token
        $nasabah = $request->user();
        // 3. Cek Saldo (Khusus Tarik Tunai)
        if ($request->kode_jenis == 'TARIK' && $nasabah->saldo < $request->jumlah) {
            return response()->json(['message' => 'Saldo tidak mencukupi.'], 422);
        }
        // generate kode_transaksi
        $id_transaksi = 'TRX-' . date('Ymd') . '-' . rand(1000, 9999);

        // 5. Simpan ke Database (Status: Pending)
        $transaksi = Transaksi::create([
            'kode_transaksi' => $id_transaksi,
            'no_rekening' => $nasabah->no_rekening,
            'kode_petugas' => null,
            'kode_jenis' => $request->kode_jenis,
            'tgl_transaksi' => now(),
            'jumlah' => $request->jumlah,
            'status' => 'pending',
            'keterangan_nasabah' => $request->keterangan_nasabah
        ]);
        return response()->json([
            'message' => 'Permintaan berhasil dikirim. Menunggu konfirmasi Petugas.',
            'data' => $transaksi
        ], 201);
    }

    // public function requestTarik(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'jumlah' => 'required|integer|min:1000',
    //         'keterangan_nasabah' => 'required|string|min:5',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }
    //     $nasabah = $request->user();

    //     //cek saldo asli
    //     if ($nasabah->saldo < $request->jumlah) {
    //         return response()->json(['message' => 'Saldo Anda tidak mencukupi untuk melakukan penarikan ini.'], 422);
    //     }

    //     $kelas = $nasabah->kelas;
    //     $jurusan = $kelas ? $kelas->jurusan : null;
    //     $jenisTarik = JenisTransaksi::where('nama_jenis', 'Tarik Tunai')->firstOrFail();

    //     $transaksi = Transaksi::create([
    //         'no_rekening' => $nasabah->no_rekening,
    //         'jenis_transaksi_id' => $jenisTarik->id,
    //         'jumlah' => $request->jumlah,
    //         'status' => 'pending',
    //         'keterangan_nasabah' => $request->keterangan_nasabah,
    //         'nama_saat_transaksi' => $nasabah->nama,
    //         'kelas_saat_transaksi' => $kelas ? $kelas->nama_kelas : null,
    //         'jurusan_saat_transaksi' => $jurusan ? $jurusan->nama_jurusan : null,
    //         'saldo_sebelum' => 0,
    //         'saldo_setelah' => 0,
    //     ]);

    //     return response()->json([
    //         'message' => 'Permintaan tarik tunai berhasil dibuat dan menunggu persetujuan.',
    //         'data' => $transaksi
    //     ], 201);
    // }

    //method petugas di transaksi
    public function approve(Request $request, $kode_transaksi)
    {
        //Cari Transaksi
        $transaksi = Transaksi::find($kode_transaksi);

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        // Cek status
        if ($transaksi->status != 'pending') {
            return response()->json(['message' => 'Transaksi ini sudah diproses.'], 422);
        }

        //PROSES PERUBAHAN SALDO (Atomic Transaction)
        try {
            DB::beginTransaction();

            // A. Update Status Transaksi
            $transaksi->update([
                'status' => 'success', // 
                'kode_petugas' => $request->user()->kode_petugas 
            ]);

            
            $nasabah = Nasabah::where('no_rekening', $transaksi->no_rekening)
                ->lockForUpdate()
                ->first();

            // Cek Jenis: SETOR atau TARIK?
            if ($transaksi->kode_jenis == 'SETOR') {
                $nasabah->saldo = $nasabah->saldo + $transaksi->jumlah;
            } else {
                // TARIK (Cek saldo lagi untuk keamanan ganda)
                if ($nasabah->saldo < $transaksi->jumlah) {
                    throw new \Exception("Saldo nasabah tidak cukup saat diproses.");
                }
                $nasabah->saldo = $nasabah->saldo - $transaksi->jumlah;
            }

            $nasabah->save(); // Simpan saldo baru

            DB::commit(); // Simpan permanen

            return response()->json(['message' => 'Transaksi Disetujui. Saldo Nasabah Berhasil Diupdate.']);
        } catch (\Exception $e) {
            DB::rollback(); // Batalkan jika error
            return response()->json(['message' => 'Gagal memproses: ' . $e->getMessage()], 500);
        }
    }

    public function getPending()
    {
        // Pastikan relasi di Model Transaksi namanya 'nasabah' dan 'jenisTransaksi'
        // Jika nama relasi beda, sesuaikan di sini (misal: 'jenis_transaksi')
        $data = Transaksi::with(['nasabah', 'jenisTransaksi'])
            ->where('status', 'pending')
            ->orderBy('tgl_transaksi', 'asc')
            ->get();

        return response()->json($data);
    }


    public function reject(Request $request, $kode_transaksi)
    {
        $transaksi = Transaksi::find($kode_transaksi);

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        if ($transaksi->status != 'pending') {
            return response()->json(['message' => 'Transaksi ini sudah diproses.'], 422);
        }

        // Update status jadi rejected/gagal
        $transaksi->update([
            'status' => 'rejected', // atau 'gagal'
            'kode_petugas' => $request->user()->kode_petugas,
            'tgl_transaksi' => now() // Update waktu penolakan
        ]);

        return response()->json(['message' => 'Transaksi berhasil ditolak.']);
    }

    //Riwayat Transaksi Nasabah 
    public function getHistoryNasabah(Request $request)
    {
        $nasabah = $request->user(); // Ambil user login

        $history = Transaksi::with('jenisTransaksi') // Join ke jenis
            ->where('no_rekening', $nasabah->no_rekening) // Filter punya dia
            ->where('status', '!=', 'pending') // Hanya yang sudah selesai (berhasil/gagal)
            ->orderBy('tgl_transaksi', 'desc') // Yang terbaru di atas
            ->get();

        return response()->json($history);
    }

    //Riwayat Semua Transaksi (Laporan Harian/Bulanan)
    public function getHistoryAdmin()
    {
        $history = Transaksi::with(['nasabah', 'jenisTransaksi', 'petugas'])
            ->where('status', '!=', 'pending') // Jangan tampilkan yang pending
            ->orderBy('tgl_transaksi', 'desc')
            ->get();

        return response()->json($history);
    }
}
