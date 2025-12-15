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
use Illuminate\Support\Facades\Log; // Tambahkan ini untuk logging error

class NasabahController extends Controller
{
    /**
     * Mengambil daftar Nasabah (Pagination & Filter)
     * Method ini harus menggantikan public function index() Anda yang lama.
     * Karena route: Route::get('/nasabah', [NasabahController::class, 'getNasabah']);
     */
    public function getNasabah(Request $request)
    {
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
        
        // Logika filter Jurusan (Membutuhkan relasi kelas.jurusan di filter)
        if ($filterJurusan) {
            $query->whereHas('kelas', function ($q) use ($filterJurusan) {
                $q->where('kode_jurusan', $filterJurusan);
            });
        }
        
        // Asumsi relasi Tahun Ajaran ada di Model Kelas, sesuaikan jika beda
        if ($filterTahunAjaran) {
             $query->whereHas('kelas', function ($q) use ($filterTahunAjaran) {
                $q->where('id_tahun_ajaran', $filterTahunAjaran); // Sesuaikan kolom di tabel 'kelas'
            });
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_rekening', 'like', '%' . $search . '%')
                  ->orWhere('no_induk', 'like', '%' . $search . '%');
            });
        }

        try {
            $nasabah = $query->paginate($perPage);
            return response()->json($nasabah);
        } catch (\Exception $e) {
            Log::error("Error loading Nasabah data: " . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memuat data nasabah karena kesalahan server.',
                'error_detail' => $e->getMessage() 
            ], 500); 
        }
    }


    /**
     * Register Nasabah (Siswa/Guru) + Otomatis Potong Admin + Catat Transaksi
     * (Logika store Anda sudah bagus dan saya pertahankan, hanya menambahkan Log::error)
     */
    public function store(Request $request)
    {
        // ... (Validasi tetap sama) ...
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'no_induk' => 'required|string|unique:nasabah,no_induk',
            'jenis_rekening' => 'required|in:siswa,guru',
            'username' => 'required|string|unique:nasabah,username',
            'password' => 'required|string',
            'alamat' => 'nullable|string',
            'no_telp' => 'nullable|string',
            'kode_kelas' => 'nullable|exists:kelas,kode_kelas',
            'saldo_awal' => 'required|integer|min:20000',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Ambil Pengaturan Biaya Admin
        $settingAdmin = Pengaturan::where('nama_pengaturan', 'biaya_admin_daftar')->first();
        $biayaAdmin = $settingAdmin ? (int)$settingAdmin->nilai : 12000;

        // 3. Hitung Saldo Bersih
        if ($request->saldo_awal < $biayaAdmin) {
            return response()->json(['message' => 'Saldo awal kurang. Minimal Rp ' . number_format($biayaAdmin)], 422);
        }
        $saldoBersih = $request->saldo_awal - $biayaAdmin;

        // 4. Generate No Rekening Unik
        do {
            $acak = rand(10000, 99999);
            $no_rekening_baru = '100' . $acak;
            $cek = Nasabah::where('no_rekening', $no_rekening_baru)->first();
        } while ($cek);

        // 5. Eksekusi Database Transaction
        try {
            DB::beginTransaction();

            // A. Simpan Data Nasabah
            $nasabah = Nasabah::create([
                'no_rekening' => $no_rekening_baru,
                'no_induk' => $request->no_induk,
                'nama' => $request->nama,
                'email' => $request->email,
                'no_telp' => $request->no_telp,
                'jenis_rekening' => $request->jenis_rekening,
                'kode_kelas' => $request->kode_kelas, 
                'saldo' => $saldoBersih, 
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            // B. Catat Transaksi 1: Setoran Awal (Uang Masuk)
            // KODE_JENIS: 'SETOR' harus ada di tabel jenis_transaksi
            $id_trx_1 = 'TRX-' . time() . '-' . rand(100, 999);
            Transaksi::create([
                'kode_transaksi' => $id_trx_1,
                'no_rekening' => $no_rekening_baru,
                'kode_petugas' => null, 
                'kode_jenis' => 'SETOR', 
                'tgl_transaksi' => now(),
                'jumlah' => $request->saldo_awal,
                'status' => 'success'
            ]);

            // C. Catat Transaksi 2: Potongan Admin (Uang Keluar)
            // KODE_JENIS: 'AWAL' harus ada di tabel jenis_transaksi
            if ($biayaAdmin > 0) {
                $id_trx_2 = 'TRX-' . (time() + 1) . '-' . rand(100, 999);
                Transaksi::create([
                    'kode_transaksi' => $id_trx_2,
                    'no_rekening' => $no_rekening_baru,
                    'kode_petugas' => null,
                    'kode_jenis' => 'AWAL', 
                    'tgl_transaksi' => now(),
                    'jumlah' => $biayaAdmin,
                    'status' => 'success'
                ]);
            }

            DB::commit(); 

            return response()->json([
                'message' => 'Nasabah Berhasil Didaftarkan',
                'detail' => [
                    'uang_diterima' => $request->saldo_awal,
                    'potongan_admin' => $biayaAdmin,
                    'saldo_akhir' => $saldoBersih
                ],
                'data' => $nasabah
            ]);
        } catch (\Exception $e) {
            DB::rollback(); 
            Log::error("Error Store Nasabah: " . $e->getMessage());
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }
    
    // --- Method CRUD Lain yang Diperlukan Route ---
    
    // Asumsi: Method-method ini diambil dari kode yang Anda komentari sebelumnya
    // Anda harus memastikan kode untuk showNasabah, updateNasabah, deleteNasabah, dan bulkUpdateStatus 
    // tersedia di Controller jika Anda memanggilnya di api.php.
    
    // Contoh method deleteNasabah (mengacu pada kode Anda yang dikomentari)
    public function deleteNasabah(Request $request, $no_rekening)
    {
        $nasabah = Nasabah::find($no_rekening);
        if (!$nasabah) {
            return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
        }

        if ($nasabah->saldo > 0) {
            return response()->json(['message' => 'Hapus Gagal: Nasabah masih memiliki sisa saldo. Harap tarik tunai terlebih dahulu.'], 409);
        }

        // Asumsi: Relasi 'transaksi' ada di Model Nasabah.
        if ($nasabah->transaksi()->where('status', 'pending')->count() > 0) {
            return response()->json(['message' => 'Hapus Gagal: Nasabah masih memiliki transaksi pending.'], 409);
        }

        $nasabah->status = 'nonaktif';
        $nasabah->save();

        return response()->json(['message' => 'Nasabah berhasil dinonaktifkan.']);
    }
    
    // ... (Tambahkan kembali updateNasabah dan bulkUpdateStatus jika route di api.php memanggilnya) ...
}