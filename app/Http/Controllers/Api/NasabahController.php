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
use Illuminate\Validation\Rule; // Tambahkan ini untuk validasi unique saat update

class NasabahController extends Controller
{
    public function index()
    {
        $data = Nasabah::with('kelas')->get();
        return response()->json($data);
    }

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
            Log::error("Error saving Nasabah: " . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan nasabah: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ----------------------------------------------------
     * FUNGSI EDIT & DELETE (Menggunakan no_rekening sebagai PK)
     * ----------------------------------------------------
     */

    // method utk mengambil detail nasabah by no_rekening
    public function showNasabah($no_rekening)
    {
        // Mencari berdasarkan PK (no_rekening) karena Model sudah disetel
        $nasabah = Nasabah::with(['kelas', 'kelas.jurusan'])
            ->find($no_rekening);

        if (!$nasabah) {
            return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
        }
        return response()->json($nasabah);
    }

    // method utk update nasabah
    public function updateNasabah(Request $request, $no_rekening)
    {
        // Mencari nasabah berdasarkan no_rekening
        $nasabah = Nasabah::find($no_rekening);
        if (!$nasabah) {
            return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
        }

        // validasi input
        $validator = Validator::make($request->all(), [
            // 💡 Koreksi: Pastikan `unique` mengabaikan data nasabah yang sedang diedit
            'username' => [
                'required',
                'string',
                Rule::unique('nasabah', 'username')->ignore($no_rekening, 'no_rekening'),
            ],
            'nama' => 'required|string',
            'no_induk' => [
                'required',
                'string',
                Rule::unique('nasabah', 'no_induk')->ignore($no_rekening, 'no_rekening'),
            ],
            'jenis_rekening' => 'required|in:siswa,guru',
            'email' => 'nullable|email',
            'alamat' => 'nullable|string',
            'no_telp' => 'nullable|string',
            'kode_kelas' => 'nullable|required_if:jenis_rekening,siswa|exists:kelas,kode_kelas',
            // tidak izinkan update password atau saldo di sini
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Melakukan update, mengabaikan password dan saldo
        $nasabah->update($request->except(['password', 'saldo'])); 
        return response()->json($nasabah);
    }

    // method utk delete nasabah (Soft Delete custom: mengubah status)
    public function deleteNasabah(Request $request, $no_rekening)
    {
        // Mencari nasabah berdasarkan no_rekening
        $nasabah = Nasabah::find($no_rekening);
        if (!$nasabah) {
            return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
        }

        // cek saldo
        if ($nasabah->saldo > 0) {
            return response()->json(['message' => 'Hapus Gagal: Nasabah masih memiliki sisa saldo. Harap tarik tunai terlebih dahulu.'], 409);
        }

        // cek transaksi pending
        // Asumsi relasi transaksi() sudah ada di Model Nasabah
        if ($nasabah->transaksi()->where('status', 'pending')->count() > 0) {
            return response()->json(['message' => 'Hapus Gagal: Nasabah masih memiliki transaksi pending.'], 409);
        }

        // Ubah status menjadi nonaktif
        $nasabah->status = 'nonaktif';
        $nasabah->save();

        return response()->json(['message' => 'Nasabah berhasil dinonaktifkan.']);
    }
    
    // Fungsi Bulk Update Status
    // (Tidak saya ubah dari kode Anda, hanya membersihkan komentar)
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_rekening_list' => 'required|array|min:1',
            'no_rekening_list.*' => 'string|exists:nasabah,no_rekening',
            'status_baru' => 'required|in:aktif,nonaktif,alumni',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $listNoRekening = $request->no_rekening_list;
        $statusBaru = $request->status_baru;

        $masihPunyaSaldo = Nasabah::whereIn('no_rekening', $listNoRekening)
            ->where('saldo', '>', 0)
            ->pluck('nama', 'no_rekening');

        if ($masihPunyaSaldo->isNotEmpty()) {
            return response()->json([
                'message' => 'Gagal: Beberapa nasabah masih memiliki sisa saldo.',
                'gagal_karena_saldo' => $masihPunyaSaldo
            ], 409);
        }

        $masihPending = Nasabah::whereIn('no_rekening', $listNoRekening)
            ->whereHas('transaksi', function ($query) {
                $query->where('status', 'pending');
            })
            ->pluck('nama', 'no_rekening');

        if ($masihPending->isNotEmpty()) {
            return response()->json([
                'message' => 'Gagal: Beberapa nasabah masih memiliki transaksi pending.',
                'gagal_karena_pending' => $masihPending
            ], 409);
        }

        try {
            DB::table('nasabah')
                ->whereIn('no_rekening', $listNoRekening)
                ->update(['status' => $statusBaru]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal update status nasabah.', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Berhasil memperbarui status untuk ' . count($listNoRekening) . ' nasabah.'
        ]);
    }
}