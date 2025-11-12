<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jurusan;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use Illuminate\Support\Facades\Validator;

class MasterDataController extends Controller
{
    // --- JURUSAN ---

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
        //validasi input
        $validator = Validator::make($request->all(), [
            'kode_jurusan' => 'required|string|unique:jurusan,kode_jurusan',
            'nama_jurusan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //buat data
        $jurusan = Jurusan::create($request->all());
        return response()->json($jurusan, 201);
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
    /**
     *ambil semua data kelas
     */
    public function getKelas()
    {
        // 'with()' adalah Eager Loading, sangat efisien
        // Ini akan mengambil data kelas BESERTA data jurusan dan tahun ajaran terkait
        $kelas = Kelas::with(['jurusan', 'tahunAjaran'])
            ->orderBy('nama_kelas', 'asc')
            ->get();

        return response()->json($kelas);
    }

    /**
     *simpan data kelas baru
     */
    public function storeKelas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kelas' => 'required|string',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'kode_jurusan' => 'required|exists:jurusan,kode_jurusan',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kelas = Kelas::create($request->all());
        return response()->json($kelas, 201);
    }
}
