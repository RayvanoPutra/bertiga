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
        $jurusan = Jurusan::orderBy('kode_jurusan', 'asc')->get();
        return response()->json($jurusan);
    }

    /**
     *simpan data jurusan baru
     */
    public function storeJurusan(Request $request)
{
    // Hapus validasi kode_jurusan
    $validator = Validator::make($request->all(), [
        'nama_jurusan' => 'required|string|unique:jurusan,nama_jurusan',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // 1. Generate kode_jurusan dari nama_jurusan (contoh)
    $kode_jurusan = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $request->nama_jurusan), 0, 5));
    // Pastikan kode unik
    $counter = 0;
    $final_kode = $kode_jurusan;
    while (Jurusan::where('kode_jurusan', $final_kode)->exists()) {
        $counter++;
        $final_kode = $kode_jurusan . $counter;
    }

    // 2. Buat data Jurusan
    $jurusan = Jurusan::create([
        'kode_jurusan' => $final_kode,
        'nama_jurusan' => $request->nama_jurusan
    ]);
    
    return response()->json($jurusan, 201);
}

    public function showJurusan($kode_jurusan)
    {
        $jurusan = Jurusan::find($kode_jurusan);
        if (!$jurusan) {
            return response()->json(['message' => 'Jurusan tidak ditemukan'], 404);
        }
        return response()->json($jurusan);
    }

    public function updateJurusan(Request $request, $kode_jurusan)
    {
        $jurusan = Jurusan::find($kode_jurusan);
        if (!$jurusan) {
            return response()->json(['message' => 'Jurusan tidak ditemukan'], 404);
        }
        $validator = Validator::make($request->all(), [
            'nama_jurusan' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $jurusan->update($request->only('nama_jurusan'));
        return response()->json($jurusan);
    }

    public function deleteJurusan($kode_jurusan)
    {
        $jurusan = Jurusan::find($kode_jurusan);
        if (!$jurusan) {
            return response()->json(['message' => 'Jurusan tidak ditemukan'], 404);
        }
        if ($jurusan->kelas()->count() > 0) {
            return response()->json([
                'message' => 'Hapus Gagal: Jurusan ini masih digunakan oleh ' . $jurusan->kelas()->count() . ' kelas.'
            ], 409);
        }
        $jurusan->delete();
        return response()->json(['message' => 'Jurusan berhasil dihapus'], 200);
    }

    /**
     *ambil semua data tahun ajaran
     */
    public function getTahunAjaran()
    {
        $tahunAjaran = TahunAjaran::orderBy('tahun_ajaran', 'desc')->get();
        return response()->json($tahunAjaran);
    }

    /**
     *simpen data tahun ajaran baru
     */
    public function storeTahunAjaran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required|string|unique:tahun_ajaran,tahun_ajaran',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $tahunAjaran = TahunAjaran::create($request->all());
        return response()->json($tahunAjaran, 201);
    }

    public function updateTahunAjaran(Request $request, $id)
    {
        $tahunAjaran = TahunAjaran::find($id);
        if (!$tahunAjaran) {
            return response()->json(['message' => 'Tahun Ajaran tidak ditemukan'], 404);
        }
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required|string|unique:tahun_ajaran,tahun_ajaran,' . $id,
            'status' => 'required|in:aktif,nonaktif',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $tahunAjaran->update($request->all());
        return response()->json($tahunAjaran);
    }

    public function deleteTahunAjaran($id)
    {
        $tahunAjaran = TahunAjaran::find($id);
        if (!$tahunAjaran) {
            return response()->json(['message' => 'Tahun Ajaran tidak ditemukan'], 404);
        }
        if ($tahunAjaran->kelas()->count() > 0) {
            return response()->json([
                'message' => 'Hapus Gagal: Tahun Ajaran ini masih digunakan oleh ' . $tahunAjaran->kelas()->count() . ' kelas.'
            ], 409);
        }
        $tahunAjaran->delete();
        return response()->json(['message' => 'Tahun Ajaran berhasil dihapus'], 200);
    }
    /**
     *ambil semua data kelas
     */
    // public function getKelas()
    // {
    //     // 'with()' menggunakan untuk eager loading dan mendapatkan relasi dari jurusan dan ta 
    //     $kelas = Kelas::with(['jurusan', 'tahunAjaran'])
    //         ->orderBy('nama_kelas', 'asc')
    //         ->get();

    //     return response()->json($kelas);
    // }

    public function getKelas()
    {
        // 'with()' menggunakan untuk eager loading dan mendapatkan relasi dari jurusan dan ta 
        // Pastikan nama relasi di Model Kelas.php adalah 'jurusan' dan 'tahunAjaran'
        $kelas = Kelas::with(['jurusan', 'tahunAjaran']) 
            ->orderBy('nama_kelas', 'asc')
            ->get();

        return response()->json($kelas);
    }

    /**
     *simpan data kelas baru
     */
    // public function storeKelas(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
    //         'kode_jurusan' => 'required|exists:jurusan,kode_jurusan',
    //         //aturan nama kelas bisa sama jika tahun ajarannya tidak sama
    //         'nama_kelas' => [
    //             'required',
    //             'string',
    //             Rule::unique('kelas')->where(function ($query) use ($request) {
    //                 return $query
    //                     ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
    //                     ->where('kode_jurusan', $request->kode_jurusan);
    //             }),
    //         ],
    //     ], [
    //         //error
    //         'nama_kelas.unique' => 'Kombinasi Kelas, Jurusan, dan Tahun Ajaran ini sudah ada.'
    //     ]);
    //     // --- AKHIR ATURAN BARU ---

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $kelas = Kelas::create($request->all());
    //     // Load relasi agar respon JSON-nya lengkap
    //     $kelas->load(['jurusan', 'tahunAjaran']);
    //     return response()->json($kelas, 201);
    // }


    public function storeKelas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_tahun_ajaran' => 'required|exists:tahun_ajaran,kode_tahun_ajaran', // Diubah
            'kode_jurusan' => 'required|exists:jurusan,kode_jurusan',
            'nama_kelas' => [
                'required',
                'string',
                Rule::unique('kelas')->where(function ($query) use ($request) {
                    return $query
                        // Diubah: Gunakan kode_tahun_ajaran
                        ->where('kode_tahun_ajaran', $request->kode_tahun_ajaran) 
                        ->where('kode_jurusan', $request->kode_jurusan);
                }),
            ],
            // Pastikan ada field 'kode_kelas' jika ingin di-set, atau buat kode_kelas otomatis di Model/Controller
            'kode_kelas' => 'required|string|unique:kelas,kode_kelas', 
        ], [
            'nama_kelas.unique' => 'Kombinasi Kelas, Jurusan, dan Tahun Ajaran ini sudah ada.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kelas = Kelas::create($request->all());
        $kelas->load(['jurusan', 'tahunAjaran']);
        return response()->json($kelas, 201);
    }
    
    public function updateKelas(Request $request, $id)
    {
        $kelas = Kelas::find($id);
        if (!$kelas) {
            return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'kode_jurusan' => 'required|exists:jurusan,kode_jurusan',
            'nama_kelas' => [
                'required',
                'string',
                Rule::unique('kelas')->where(function ($query) use ($request) {
                    return $query
                        ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
                        ->where('kode_jurusan', $request->kode_jurusan);
                })->ignore($id), // 'ignore($id)' berarti aturan unik ini tidak berlaku untuk dirinya sendiri
            ],
        ], [
            'nama_kelas.unique' => 'Kombinasi Kelas, Jurusan, dan Tahun Ajaran ini sudah ada.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kelas->update($request->all());
        $kelas->load(['jurusan', 'tahunAjaran']);
        return response()->json($kelas);
    }

    public function deleteKelas($id)
    {
        $kelas = Kelas::find($id);
        if (!$kelas) {
            return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
        }
        if ($kelas->nasabah()->count() > 0) {
            return response()->json([
                'message' => 'Hapus Gagal: Kelas ini masih memiliki ' . $kelas->nasabah()->count() . ' nasabah terdaftar.'
            ], 409);
        }
        $kelas->delete();
        return response()->json(['message' => 'Kelas berhasil dihapus'], 200);
    }
}