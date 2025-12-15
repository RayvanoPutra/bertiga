<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\Transaksi;
use App\Models\Kelas;
use App\Models\JenisTransaksi;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; 
use Exception;

class NasabahController extends Controller
{
    /**
     * add nasabah (Store)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_rekening' => 'required|unique:nasabah',
            'kode_kelas' => 'nullable|exists:kelas,kode_kelas',
            'no_induk' => 'required|unique:nasabah',
            'nama' => 'required|string|max:255',
            'jenis_rekening' => 'required|in:siswa,guru',
            'username' => 'required|unique:nasabah|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $nasabah = Nasabah::create([
            'no_rekening' => $request->no_rekening,
            'kode_kelas' => $request->kode_kelas,
            'no_induk' => $request->no_induk,
            'nama' => $request->nama,
            'email' => $request->email,
            'no_telp' => $request->no_telp,
            'jenis_rekening' => $request->jenis_rekening,
            'saldo' => 0, // Saldo awal
            'username' => $request->username,
            'password' => Hash::make($request->password), // Enkripsi password
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Nasabah berhasil ditambahkan',
            'data' => $nasabah
        ], 201);
    }
    
    // ... (Fungsi generateNoRekening dan getNasabah) ...
    private function generateNoRekening()
    {
        do {
            $noRek = 'BSM-' . mt_rand(100000, 999999);
            $cek = Nasabah::where('no_rekening', $noRek)->first();
        } while ($cek);

        return $noRek;
    }

    public function getNasabah(Request $request)
    {
        // ... (Logika getNasabah) ...
        $filterKelas = $request->input('kelas_id'); 
        $filterJurusan = $request->input('jurusan_id'); 
        $filterTahunAjaran = $request->input('tahun_ajaran_id');
        $search = $request->input('cari');
        $perPage = $request->input('per_page', 10); 

        $query = Nasabah::with(['kelas', 'kelas.jurusan'])
            ->where('status', 'aktif')
            ->orderBy('nama', 'asc');

        if ($filterKelas) {
            $query->where('kode_kelas', $filterKelas);
        }
        // ... (lanjutan filter Jurusan dan Tahun Ajaran) ...
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_rekening', 'like', '%' . $search . '%');
            });
        }

        try {
            $nasabah = $query->paginate($perPage);
            return response()->json($nasabah);
        } catch (Exception $e) {
            Log::error("Error loading Nasabah data: " . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memuat data nasabah karena kesalahan server.',
                'error_detail' => $e->getMessage() 
            ], 500); 
        }
    }
}