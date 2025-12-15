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

class NasabahController extends Controller
{
    public function index()
    {
        $data = Nasabah::with('kelas')->get();
        return response()->json($data);
    }

    /**
     * Register Nasabah (Siswa/Guru) + Otomatis Potong Admin + Catat Transaksi
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
         
         if ($filterJurusan) {
             $query->whereHas('kelas', function ($q) use ($filterJurusan) {
                 $q->where('kode_jurusan', $filterJurusan);
             });
         }
         
         if ($filterTahunAjaran) {
              $query->whereHas('kelas', function ($q) use ($filterTahunAjaran) {
                  $q->where('id_tahun_ajaran', $filterTahunAjaran);
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

    public function store(Request $request)
    {
        // 1. Validasi Input
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

        // 2. Ambil Pengaturan Biaya Admin (Default 12.000 jika tidak disetting)
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

        // 5. Eksekusi Database Transaction (Agar Data Konsisten)
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
                'kode_kelas' => $request->kode_kelas, // Gunakan id_kelas
                'saldo' => $saldoBersih, // Simpan saldo yang sudah dipotong
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            // B. Catat Transaksi 1: Setoran Awal (Uang Masuk)
            // Kita pakai ID Jenis 'SETOR' (sesuai seeder)
            $id_trx_1 = 'TRX-' . time() . '-' . rand(100, 999);
            Transaksi::create([
                'kode_transaksi' => $id_trx_1,
                'no_rekening' => $no_rekening_baru,
                'kode_petugas' => null, // null karena sistem otomatis
                'kode_jenis' => 'SETOR', // Pastikan ID ini ada di tabel jenis_transaksi
                'tgl_transaksi' => now(),
                'jumlah' => $request->saldo_awal,
                'status' => 'success'
            ]);

            // C. Catat Transaksi 2: Potongan Admin (Uang Keluar)
            // Kita pakai ID Jenis 'TARIK' (sebagai representasi uang keluar/biaya)
            // Atau jika Anda sudah buat jenis 'BIAYA', pakai itu.
            if ($biayaAdmin > 0) {
                $id_trx_2 = 'TRX-' . (time() + 1) . '-' . rand(100, 999);
                Transaksi::create([
                    'kode_transaksi' => $id_trx_2,
                    'no_rekening' => $no_rekening_baru,
                    'kode_petugas' => null,
                    'kode_jenis' => 'AWAL', // Anggap biaya admin sebagai penarikan sistem
                    'tgl_transaksi' => now(),
                    'jumlah' => $biayaAdmin,
                    'status' => 'success'
                ]);
            }

            DB::commit(); // Simpan permanen

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
            DB::rollback(); // Batalkan semua jika error
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }
}


/**
 * Helper function untuk membuat No Rekening unik.
 * Format: BSM- [6 digit angka random]
 */
    // private function generateNoRekening()
    // {
    //     do {
    //         $noRek = 'BSM-' . mt_rand(100000, 999999);
    //         // Cek ke DB apakah noRek sudah ada. Jika sudah, ulangi (loop).
    //         $cek = Nasabah::where('no_rekening', $noRek)->first();
    //     } while ($cek);

    //     return $noRek;
    // }

    // method utk mengambil data nasabah (web)
    // public function getNasabah(Request $request)
    // {
    //     $nasabah = Nasabah::with(['kelas', 'kelas.jurusan'])
    //         ->where('status', 'aktif')
    //         ->orderBy('nama', 'asc')
    //         ->get();

    //     return response()->json($nasabah);
    // }

    // method utk mengambil detail nasabah by no_rekening
    // public function showNasabah($no_rekening)
    // {
    //     $nasabah = Nasabah::with(['kelas', 'kelas.jurusan'])
    //         ->find($no_rekening);

    //     if (!$nasabah) {
    //         return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
    //     }
    //     return response()->json($nasabah);
    // }

    // method utk update nasabah
    // public function updateNasabah(Request $request, $no_rekening)
    // {
    //     $nasabah = Nasabah::find($no_rekening);
    //     if (!$nasabah) {
    //         return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
    //     }

    //     // validasi input
    //     $validator = Validator::make($request->all(), [
    //         'username' => 'required|string|unique:nasabah,username,' . $no_rekening . ',no_rekening',
    //         'nama' => 'required|string',
    //         'no_induk' => 'required|string|unique:nasabah,no_induk,' . $no_rekening . ',no_rekening',
    //         'jenis_rekening' => 'required|in:siswa,guru',
    //         'email' => 'nullable|email',
    //         'kelas_id' => 'nullable|required_if:jenis_rekening,siswa|exists:kelas,id',
    //         // tidak izinkan update password atau saldo di sini
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $nasabah->update($request->except(['password', 'saldo'])); //kecuali
    //     return response()->json($nasabah);
    // }
    // // method utk delete nasabah
    // public function deleteNasabah(Request $request, $no_rekening)
    // {
    //     $nasabah = Nasabah::find($no_rekening);
    //     if (!$nasabah) {
    //         return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
    //     }

    //     //cek saldo
    //     if ($nasabah->saldo > 0) {
    //         return response()->json(['message' => 'Hapus Gagal: Nasabah masih memiliki sisa saldo. Harap tarik tunai terlebih dahulu.'], 409);
    //     }

    //     //cek transaksi pending
    //     if ($nasabah->transaksi()->where('status', 'pending')->count() > 0) {
    //         return response()->json(['message' => 'Hapus Gagal: Nasabah masih memiliki transaksi pending.'], 409);
    //     }

    //     $nasabah->status = 'nonaktif';
    //     $nasabah->save();

    //     return response()->json(['message' => 'Nasabah berhasil dinonaktifkan.']);
    // }

    // public function bulkUpdateStatus(Request $request)
    // {
    //     // validasi input
    //     $validator = Validator::make($request->all(), [
    //         'no_rekening_list' => 'required|array|min:1',
    //         'no_rekening_list.*' => 'string|exists:nasabah,no_rekening', //cek array per item
    //         'status_baru' => 'required|in:aktif,nonaktif,alumni',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $listNoRekening = $request->no_rekening_list;
    //     $statusBaru = $request->status_baru;

    //     // filter nasabah yg ada saldonya
    //     $masihPunyaSaldo = Nasabah::whereIn('no_rekening', $listNoRekening)
    //         ->where('saldo', '>', 0)
    //         ->pluck('nama', 'no_rekening');

    //     if ($masihPunyaSaldo->isNotEmpty()) {
    //         return response()->json([
    //             'message' => 'Gagal: Beberapa nasabah masih memiliki sisa saldo.',
    //             'gagal_karena_saldo' => $masihPunyaSaldo
    //         ], 409); // 409 Conflict
    //     }

    //     $masihPending = Nasabah::whereIn('no_rekening', $listNoRekening)
    //         ->whereHas('transaksi', function ($query) {
    //             $query->where('status', 'pending');
    //         })
    //         ->pluck('nama', 'no_rekening');

    //     if ($masihPending->isNotEmpty()) {
    //         return response()->json([
    //             'message' => 'Gagal: Beberapa nasabah masih memiliki transaksi pending.',
    //             'gagal_karena_pending' => $masihPending
    //         ], 409);
    //     }

    //     // Jika semua aman (saldo 0 dan tidak ada pending), jalankan 1 query UPDATE massal.
    //     try {
    //         DB::table('nasabah')
    //             ->whereIn('no_rekening', $listNoRekening)
    //             ->update(['status' => $statusBaru]);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Gagal update status nasabah.', 'error' => $e->getMessage()], 500);
    //     }

    //     return response()->json([
    //         'message' => 'Berhasil memperbarui status untuk ' . count($listNoRekening) . ' nasabah.'
    //     ]);
    // }