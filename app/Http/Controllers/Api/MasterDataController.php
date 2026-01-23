<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jurusan;
use App\Models\TahunAjaran;
use App\Models\Kelas;
<<<<<<< HEAD
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
=======
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
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
    }

    public function deleteJurusan($kode_jurusan)
    {
<<<<<<< HEAD
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
=======
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
    public function getKelas()
    {
        // 'with()' menggunakan untuk eager loading dan mendapatkan relasi dari jurusan dan ta 
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
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'kode_jurusan' => 'required|exists:jurusan,kode_jurusan',
            //aturan nama kelas bisa sama jika tahun ajarannya tidak sama
            'nama_kelas' => [
                'required',
                'string',
                Rule::unique('kelas')->where(function ($query) use ($request) {
                    return $query
                        ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
                        ->where('kode_jurusan', $request->kode_jurusan);
                }),
            ],
        ], [
            //error
            'nama_kelas.unique' => 'Kombinasi Kelas, Jurusan, dan Tahun Ajaran ini sudah ada.'
        ]);
        // --- AKHIR ATURAN BARU ---

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kelas = Kelas::create($request->all());
        // Load relasi agar respon JSON-nya lengkap
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
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
