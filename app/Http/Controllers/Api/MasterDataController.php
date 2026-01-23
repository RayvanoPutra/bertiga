<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jurusan;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Models\Petugas;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class MasterDataController extends Controller
{
    // ==========================================
    // JURUSAN
    // ==========================================

    public function getJurusan()
{
    // Cukup panggil Jurusan::all() atau Jurusan::get()
    return response()->json(Jurusan::all());
}

    // app/Http/Controllers/Api/MasterDataController.php

public function storeJurusan(Request $request)
{
    // HAPUS 'kode_tahun_ajaran' dari sini
    $validator = Validator::make($request->all(), [
        'kode_jurusan' => 'required|string|unique:jurusan,kode_jurusan',
        'nama_jurusan' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Validasi Gagal', 'errors' => $validator->errors()], 422);
    }

    try {
        $jurusan = Jurusan::create([
            'kode_jurusan' => strtoupper($request->kode_jurusan),
            'nama_jurusan' => $request->nama_jurusan
            // JANGAN masukkan kode_tahun_ajaran di sini
        ]);

        return response()->json(['message' => 'Jurusan berhasil disimpan', 'data' => $jurusan], 201);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
    }
}

    public function updateJurusan(Request $request, $kode_jurusan)
    {
        $jurusan = Jurusan::where('kode_jurusan', $kode_jurusan)->first();
        if (!$jurusan) return response()->json(['message' => 'Jurusan tidak ditemukan'], 404);

        $validator = Validator::make($request->all(), [
            'nama_jurusan' => 'required|string',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $jurusan->update($request->only('nama_jurusan'));
        return response()->json(['message' => 'Jurusan Berhasil Diupdate', 'data' => $jurusan]);
    }

    public function deleteJurusan($kode_jurusan)
    {
        $jurusan = Jurusan::where('kode_jurusan', $kode_jurusan)->first();
        if (!$jurusan) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        if ($jurusan->kelas()->count() > 0) {
            return response()->json(['message' => 'Hapus Gagal: Jurusan masih digunakan di ' . $jurusan->kelas()->count() . ' kelas.'], 409);
        }

        $jurusan->delete();
        return response()->json(['message' => 'Jurusan berhasil dihapus']);
    }

    // ==========================================
    // TAHUN AJARAN
    // ==========================================

    public function getTahunAjaran()
    {
        return response()->json(TahunAjaran::orderBy('tahun_ajaran', 'desc')->get());
    }

    public function storeTahunAjaran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_tahun_ajaran' => 'required|string|unique:tahun_ajaran,kode_tahun_ajaran',
            'tahun_ajaran' => 'required|string|unique:tahun_ajaran,tahun_ajaran',
            'status' => 'required|in:aktif,nonaktif',
        ], [
            'tahun_ajaran.unique' => 'TAHUN AJARAN TIDAK BOLEH SAMA' // Pesan Wireframe Turn 15
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $ta = TahunAjaran::create($request->all());
        return response()->json(['message' => 'Tahun Ajaran Berhasil Ditambah', 'data' => $ta]);
    }

    public function updateTahunAjaran(Request $request, $kode_tahun_ajaran)
    {
        $ta = TahunAjaran::where('kode_tahun_ajaran', $kode_tahun_ajaran)->first();
        if (!$ta) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required|string|unique:tahun_ajaran,tahun_ajaran,' . $kode_tahun_ajaran . ',kode_tahun_ajaran',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $ta->update($request->all());
        return response()->json(['message' => 'Berhasil Update', 'data' => $ta]);
    }

    public function deleteTahunAjaran($id)
{
    try {
        // 1. Cari data berdasarkan ID atau Kode
        $ta = TahunAjaran::where('kode_tahun_ajaran', $id)->first();

        if (!$ta) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // 2. Eksekusi Hapus
        // Catatan: Karena di migrasi Kelas Anda menggunakan onDelete('cascade'), 
        // maka menghapus TA ini juga akan menghapus Kelas yang terkait.
        $ta->delete();

        return response()->json(['message' => 'Tahun Ajaran berhasil dihapus']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
    }
}

    // ==========================================
    // KELAS
    // ==========================================

    public function getKelas() {
    $data = Kelas::with(['jurusan', 'tahunAjaran'])->get();
    return response()->json($data); // Mengirim data murni
    }

    public function updateKelas(Request $request, $kode_kelas) {
    // Cari kelas berdasarkan kode yang dikirim dari URL
    $kelas = Kelas::where('kode_kelas', $kode_kelas)->first();
    
    if (!$kelas) return response()->json(['message' => 'Kelas tidak ditemukan'], 404);

    $kelas->update([
        'nama_kelas' => $request->nama_kelas,
        'kode_jurusan' => $request->kode_jurusan,
        'kode_tahun_ajaran' => $request->kode_tahun_ajaran,
    ]);

    return response()->json(['message' => 'Berhasil diperbarui']);
}


    public function storeKelas(Request $request)
{
    // 1. Validasi Input
    $validator = Validator::make($request->all(), [
        'nama_kelas' => 'required|string',
        'kode_jurusan' => 'required|exists:jurusan,kode_jurusan',
        'kode_tahun_ajaran' => 'required|exists:tahun_ajaran,kode_tahun_ajaran',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
    }

    // 2. Cek Duplikat (Agar tidak ada data yang sama persis)
    $exists = Kelas::where('nama_kelas', $request->nama_kelas)
                    ->where('kode_jurusan', $request->kode_jurusan)
                    ->where('kode_tahun_ajaran', $request->kode_tahun_ajaran)
                    ->exists();

    if ($exists) {
        return response()->json(['message' => 'Kelas ini sudah terdaftar untuk jurusan dan tahun ajaran tersebut.'], 422);
    }

    // 3. GENERATE KODE KELAS (Format: X-AK-1-TA2425)
    // Mengubah spasi menjadi tanda hubung, misal: "X AK 1" -> "X-AK-1"
    $namaKelasBersih = str_replace(' ', '-', $request->nama_kelas);

    // Membersihkan kode TA jika mengandung "TA-" agar tidak duplikat
    $taBersih = str_replace('TA-', '', $request->kode_tahun_ajaran);

    // Hasil akhir gabungan
    $kodeBaru = $namaKelasBersih . '-TA' . $taBersih;

    $kelas = Kelas::create([
        'kode_kelas' => $kodeBaru, 
        'nama_kelas' => $request->nama_kelas,
        'kode_jurusan' => $request->kode_jurusan,
        'kode_tahun_ajaran' => $request->kode_tahun_ajaran,
    ]);

    return response()->json(['message' => 'Kelas berhasil ditambahkan', 'data' => $kelas]);
}

public function deleteKelas($kode_kelas)
{
    try {
        // 1. Cari data kelas berdasarkan Kode Kelas
        $kelas = Kelas::where('kode_kelas', $kode_kelas)->first();

        if (!$kelas) {
            return response()->json(['message' => 'Data kelas tidak ditemukan'], 404);
        }

        // 2. Keamanan Tambahan: Cek apakah benar-benar tidak ada nasabah
        // Meskipun Anda yakin kosong, pengecekan ini mencegah error database (Foreign Key)
        $hasNasabah = \App\Models\Nasabah::where('kode_kelas', $kode_kelas)->exists();
        if ($hasNasabah) {
            return response()->json(['message' => 'Gagal: Masih ada nasabah yang terdaftar di kelas ini.'], 422);
        }

        // 3. Eksekusi Hapus
        $kelas->delete();

        return response()->json(['message' => 'Kelas berhasil dihapus']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
    }
}

    // ==========================================
    // PETUGAS
    // ==========================================

public function getPetugas() {
    return response()->json(\App\Models\Petugas::all());
}

// app/Http/Controllers/Api/MasterDataController.php

public function storePetugas(Request $request) {
    // 1. Validasi dengan pesan kustom Bahasa Indonesia
    $validator = Validator::make($request->all(), [
        'nama_petugas' => 'required|string',
        'username'     => 'required|unique:petugas,username',
        'password'     => 'required|min:6',
        'role'         => 'required|in:superadmin,admin'
    ], [
        'nama_petugas.required' => 'Nama petugas wajib diisi.',
        'username.required'     => 'Username wajib diisi.',
        'username.unique'       => 'Username sudah digunakan, silakan pilih yang lain.',
        'password.required'     => 'Password wajib diisi.',
        'password.min'          => 'Password minimal harus 6 karakter.',
        'role.required'         => 'Role wajib dipilih.',
    ]);

    // Jika validasi gagal, kirimkan objek errors
    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validasi gagal',
            'errors' => $validator->errors()
        ], 422);
    }

    // 2. Logika Generate Kode Otomatis
    $role = $request->role;
    $prefix = ($role === 'superadmin') ? 'SADM' : 'ADM';

    $lastPetugas = \App\Models\Petugas::where('kode_petugas', 'like', $prefix . '%')
        ->orderBy('kode_petugas', 'desc')
        ->first();

    if ($lastPetugas) {
        $lastNumber = (int) substr($lastPetugas->kode_petugas, strlen($prefix));
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }

    $kodeOtomatis = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

    // 3. Simpan ke Database
    try {
        $petugas = \App\Models\Petugas::create([
            'kode_petugas' => $kodeOtomatis,
            'nama_petugas' => $request->nama_petugas,
            'username'     => $request->username,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
        ]);

        return response()->json([
            'message' => 'Petugas berhasil ditambahkan',
            'data' => $petugas
        ], 201);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Terjadi kesalahan database'], 500);
    }
}

public function deletePetugas($kode) {
    $petugas = \App\Models\Petugas::where('kode_petugas', $kode)->first();
    if (!$petugas) return response()->json(['message' => 'Tidak ditemukan'], 404);
    
    // Cegah menghapus diri sendiri (opsional)
    $petugas->delete();
    return response()->json(['message' => 'Petugas dihapus']);
}
    // ==========================================
    // PENGATURAN
    // ==========================================
    // Tambahkan fungsi untuk mengambil dan mengupdate pengaturan
// app/Http/Controllers/Api/MasterDataController.php

public function getPengaturan() {
    try {
        // Mengambil semua data dan mengubahnya jadi format Key => Value
        $data = \App\Models\Pengaturan::all();
        
        // Tambahkan URL lengkap untuk logo agar bisa tampil di frontend
        if (isset($data['logo_website']) && $data['logo_website']) {
            $data['logo_website'] = url($data['logo_website']);
        }

        return response()->json($data);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}

// app/Http/Controllers/Api/MasterDataController.php

public function updatePengaturan(Request $request)
{
    try {
        $inputs = $request->all();

        // 1. Logika Unggah Logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            // Simpan di folder: storage/app/public/logo
            $path = $file->store('public/logo'); 
            // Ubah path menjadi: storage/logo/namafile.png agar bisa diakses publik
            $inputs['logo_website'] = str_replace('public/', 'storage/', $path);
        }

        // 2. Simpan/Update ke Database
        foreach ($inputs as $key => $value) {
            if ($key !== 'logo') { // Jangan simpan file mentah ke tabel
                \App\Models\Pengaturan::updateOrCreate(
                    ['nama_pengaturan' => $key],
                    ['nilai' => $value]
                );
            }
        }

        return response()->json(['message' => 'Berhasil diperbarui']);
    } catch (\Exception $e) {
        // Gunakan -> untuk memanggil method di dalam objek
        return response()->json(['message' => $e->getMessage()], 500);
    }
}
}