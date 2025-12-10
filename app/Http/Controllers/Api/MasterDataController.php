<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jurusan;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{

    /**
     *ambil semua data jurusan.
     */
    public function getJurusan()
    {
        //ambil data jurusan 
        $data = Jurusan::orderBy('kode_jurusan', 'asc')->get();
        return response()->json($data);
    }

    /**
     *simpan data jurusan baru
     */
    public function storeJurusan(Request $request)
    {
        //validasi input
        $validator = Validator::make($request->all(), [
            'kode_jurusan' => 'required|string|unique:jurusan,kode_jurusan',
            'nama_jurusan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //buat data
        $jurusan = Jurusan::create([
            'kode_jurusan' => $request->kode_jurusan,
            'nama_jurusan' => $request->nama_jurusan
        ]);
        return response()->json(['message' => 'Jurusan Berhasil Ditambah', 'data' => $jurusan]);
    }

    // public function showJurusan($kode_jurusan)
    // {
    //     $jurusan = Jurusan::find($kode_jurusan);
    //     if (!$jurusan) {
    //         return response()->json(['message' => 'Jurusan tidak ditemukan'], 404);
    //     }
    //     return response()->json($jurusan);
    // }

    // public function updateJurusan(Request $request, $kode_jurusan)
    // {
    //     $jurusan = Jurusan::find($kode_jurusan);
    //     if (!$jurusan) {
    //         return response()->json(['message' => 'Jurusan tidak ditemukan'], 404);
    //     }
    //     $validator = Validator::make($request->all(), [
    //         'nama_jurusan' => 'required|string',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }
    //     $jurusan->update($request->only('nama_jurusan'));
    //     return response()->json($jurusan);
    // }

    // public function deleteJurusan($kode_jurusan)
    // {
    //     $jurusan = Jurusan::find($kode_jurusan);
    //     if (!$jurusan) {
    //         return response()->json(['message' => 'Jurusan tidak ditemukan'], 404);
    //     }
    //     if ($jurusan->kelas()->count() > 0) {
    //         return response()->json([
    //             'message' => 'Hapus Gagal: Jurusan ini masih digunakan oleh ' . $jurusan->kelas()->count() . ' kelas.'
    //         ], 409);
    //     }
    //     $jurusan->delete();
    //     return response()->json(['message' => 'Jurusan berhasil dihapus'], 200);
    // }

    /**
     *ambil semua data tahun ajaran
     */
    public function getTahunAjaran()
    {
        $tahunAjaran = TahunAjaran::orderBy('status', 'asc')->get();
        return response()->json($tahunAjaran);
    }

    /**
     *simpen data tahun ajaran baru
     */
    public function storeTahunAjaran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_tahun_ajaran' => 'required|string|unique:tahun_ajaran,kode_tahun_ajaran', // Manual: TA-2025
            'tahun_ajaran' => 'required|string', // Manual: 2024/2025
            'status' => 'required|in:aktif,nonaktif',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $ta = TahunAjaran::create([
            'kode_tahun_ajaran' => $request->kode_tahun_ajaran,
            'tahun_ajaran' => $request->tahun_ajaran,
            'status' => $request->status
        ]);
        return response()->json(['message' => 'Tahun Ajaran Berhasil Ditambah', 'data' => $ta]);
    }

    // public function updateTahunAjaran(Request $request, $id)
    // {
    //     $tahunAjaran = TahunAjaran::find($id);
    //     if (!$tahunAjaran) {
    //         return response()->json(['message' => 'Tahun Ajaran tidak ditemukan'], 404);
    //     }
    //     $validator = Validator::make($request->all(), [
    //         'tahun_ajaran' => 'required|string|unique:tahun_ajaran,tahun_ajaran,' . $id,
    //         'status' => 'required|in:aktif,nonaktif',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }
    //     $tahunAjaran->update($request->all());
    //     return response()->json($tahunAjaran);
    // }

    // public function deleteTahunAjaran($id)
    // {
    //     $tahunAjaran = TahunAjaran::find($id);
    //     if (!$tahunAjaran) {
    //         return response()->json(['message' => 'Tahun Ajaran tidak ditemukan'], 404);
    //     }
    //     if ($tahunAjaran->kelas()->count() > 0) {
    //         return response()->json([
    //             'message' => 'Hapus Gagal: Tahun Ajaran ini masih digunakan oleh ' . $tahunAjaran->kelas()->count() . ' kelas.'
    //         ], 409);
    //     }
    //     $tahunAjaran->delete();
    //     return response()->json(['message' => 'Tahun Ajaran berhasil dihapus'], 200);
    // }
    /**
     *ambil semua data kelas
     */
    public function getKelas()
    {
        // 'with()' menggunakan untuk eager loading dan mendapatkan relasi dari jurusan dan ta 
        $data = Kelas::with(['jurusan', 'tahunAjaran'])->get();
        return response()->json($data);
    }

    /**
     *simpan data kelas baru
     */
    public function storeKelas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kelas' => 'required|string', // Contoh: X RPL 1
            'kode_jurusan' => 'required|exists:jurusan,kode_jurusan',
            'kode_tahun_ajaran' => 'required|exists:tahun_ajaran,kode_tahun_ajaran',
        ]);
        // --- AKHIR ATURAN BARU ---

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Buat ID Kelas Unik
        $id_clean = str_replace(' ', '-', strtoupper($request->nama_kelas));
        $kode_kelas_unik = $id_clean . '-' . $request->kode_tahun_ajaran;

        if (Kelas::find($kode_kelas_unik)) {
            return response()->json(['message' => 'Kelas ini sudah ada di Tahun Ajaran tersebut!'], 422);
        }

        $kelas = Kelas::create([
            'kode_kelas' => $kode_kelas_unik, // ID Otomatis
            'nama_kelas' => $request->nama_kelas,
            'kode_jurusan' => $request->kode_jurusan,
            'kode_tahun_ajaran' => $request->kode_tahun_ajaran,
        ]);

        return response()->json(['message' => 'Kelas Berhasil Ditambah', 'data' => $kelas]);
    }

    // public function updateKelas(Request $request, $id)
    // {
    //     $kelas = Kelas::find($id);
    //     if (!$kelas) {
    //         return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
    //         'kode_jurusan' => 'required|exists:jurusan,kode_jurusan',
    //         'nama_kelas' => [
    //             'required',
    //             'string',
    //             Rule::unique('kelas')->where(function ($query) use ($request) {
    //                 return $query
    //                     ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
    //                     ->where('kode_jurusan', $request->kode_jurusan);
    //             })->ignore($id), // 'ignore($id)' berarti aturan unik ini tidak berlaku untuk dirinya sendiri
    //         ],
    //     ], [
    //         'nama_kelas.unique' => 'Kombinasi Kelas, Jurusan, dan Tahun Ajaran ini sudah ada.'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $kelas->update($request->all());
    //     $kelas->load(['jurusan', 'tahunAjaran']);
    //     return response()->json($kelas);
    // }

    // public function deleteKelas($id)
    // {
    //     $kelas = Kelas::find($id);
    //     if (!$kelas) {
    //         return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
    //     }
    //     if ($kelas->nasabah()->count() > 0) {
    //         return response()->json([
    //             'message' => 'Hapus Gagal: Kelas ini masih memiliki ' . $kelas->nasabah()->count() . ' nasabah terdaftar.'
    //         ], 409);
    //     }
    //     $kelas->delete();
    //     return response()->json(['message' => 'Kelas berhasil dihapus'], 200);
    // }
}
