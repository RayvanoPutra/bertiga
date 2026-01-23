<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\Transaksi;
use App\Models\Kelas;
use App\Models\JenisTransaksi;
<<<<<<< HEAD
use App\Models\Pengaturan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class NasabahController extends Controller
{
    public function index(Request $request) // Tambahkan Request $request di sini
    {
        // 1. Inisialisasi query dengan relasi
        $query = Nasabah::with(['kelas.jurusan', 'kelas.tahunAjaran']);

        // 2. Filter Pencarian (Nama, No Rekening, atau NIS)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('no_rekening', 'like', '%' . $search . '%')
                    ->orWhere('no_induk', 'like', '%' . $search . '%');
            });
        }

        // 3. Filter Jurusan (Melalui relasi Kelas)
        if ($request->has('jurusan') && $request->jurusan != '') {
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('kode_jurusan', $request->jurusan);
            });
        }

        // 4. Filter Tahun Ajaran (Melalui relasi Kelas)
        if ($request->has('tahun_ajaran') && $request->tahun_ajaran != '') {
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('kode_tahun_ajaran', $request->tahun_ajaran);
            });
        }

        // 5. Filter Kelas Spesifik
        if ($request->has('kelas') && $request->kelas != '') {
            $query->where('kode_kelas', $request->kelas);
        }

        // 6. Ambil data dengan urutan terbaru
        $data = $query->orderBy('created_at', 'desc')->get();

        return response()->json($data);
    }

    /**
     * Register Nasabah (Siswa/Guru) + Otomatis Potong Admin + Catat Transaksi
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        // 1. Validasi Input (DENGAN PESAN CUSTOM BAHASA INDONESIA)
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'no_induk' => 'required|string|unique:nasabah,no_induk', // Unik
            'jenis_rekening' => 'required|in:siswa,guru',
            'password' => 'required|string|min:6',

            // Kolom opsional
            'alamat' => 'nullable|string',
            'no_telp' => 'nullable|string',

            'email' => 'required|email|unique:nasabah,email', // Unik
            'kode_kelas' => 'nullable|required_if:jenis_rekening,siswa|exists:kelas,kode_kelas',
            'saldo_awal' => 'required|integer|min:20000',
        ], [
            // --- PESAN ERROR CUSTOM AGAR JELAS DI FRONTEND ---
            'no_induk.unique' => 'Nomor Induk (NIS/NIP) ini sudah terdaftar.',
            'email.unique' => 'Alamat Email ini sudah digunakan nasabah lain.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'kode_kelas.required_if' => 'Kelas harus dipilih untuk nasabah siswa.',
            'saldo_awal.min' => 'Setoran awal minimal adalah Rp 20.000.',
        ]);

        if ($validator->fails()) {
            // Mengirimkan detail 'errors' agar bisa dibaca JavaScript
            return response()->json([
                'message' => 'Validasi Gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Ambil Pengaturan Biaya Admin Terbaru
        // Sesuaikan key 'jumlah_potongan' dengan yang dikirim dari form pengaturan
        $settingAdmin = Pengaturan::where('nama_pengaturan', 'jumlah_potongan')->first();

        // Jika di database tidak ada, kita set default 0 agar tidak membingungkan
        $biayaAdmin = $settingAdmin ? (int)$settingAdmin->nilai : 0;

        // 3. Hitung Saldo Bersih
        if ($request->saldo_awal < $biayaAdmin) {
            return response()->json(['message' => 'Saldo awal kurang. Minimal Rp ' . number_format($biayaAdmin)], 422);
        }
        $saldoBersih = $request->saldo_awal - $biayaAdmin;

        // 4. GENERATE NO REKENING UNIK (Format: BB-TT-KK-XXXXX)
        // ---------------------------------------------------------
        $now = Carbon::now();
        $bulan = $now->format('m'); // Contoh: 08 (Bulan Saat Ini)
        $tahun = $now->format('y'); // Contoh: 25 (Tahun Saat Ini - 2 Digit)

        $kodeTingkat = '00'; // Default jika tidak terdeteksi (atau untuk Guru)

        if ($request->jenis_rekening == 'siswa' && $request->kode_kelas) {
            $kelas = Kelas::where('kode_kelas', $request->kode_kelas)->first();
            if ($kelas) {
                // Deteksi Angka Kelas dari Nama Kelas
                // Contoh: "X RPL 1" -> 10, "XI TKJ 2" -> 11
                $namaKelas = strtoupper($kelas->nama_kelas);

                if (str_contains($namaKelas, 'X ') || str_contains($namaKelas, '10 ')) {
                    $kodeTingkat = '10';
                } elseif (str_contains($namaKelas, 'XI ') || str_contains($namaKelas, '11 ')) {
                    $kodeTingkat = '11';
                } elseif (str_contains($namaKelas, 'XII ') || str_contains($namaKelas, '12 ')) {
                    $kodeTingkat = '12';
                }
            }
        } elseif ($request->jenis_rekening == 'guru') {
            $kodeTingkat = '99'; // Kode khusus Guru
        }

        // Prefix: 082510 (Bulan-Tahun-TingkatKelas)
        $prefix = $bulan . $tahun . $kodeTingkat;

        // Cari nomor urut terakhir dengan prefix yang sama
        $lastNasabah = Nasabah::where('no_rekening', 'like', $prefix . '%')
            ->orderBy('no_rekening', 'desc')
            ->first();

        if ($lastNasabah) {
            // Ambil 5 digit terakhir lalu tambah 1
            $lastUrut = (int)substr($lastNasabah->no_rekening, -5);
            $nextUrut = $lastUrut + 1;
        } else {
            $nextUrut = 1;
        }

        // Format akhir: 08251000010
        $no_rekening_baru = $prefix . sprintf('%05d', $nextUrut);
        // ---------------------------------------------------------

        // 5. Eksekusi Database Transaction
        try {

            DB::beginTransaction();

            // A. Simpan Data   Nasabah
            $nasabah = Nasabah::create([
                'no_rekening' => $no_rekening_baru,
                'no_induk' => $request->no_induk,
                'nama' => $request->nama,
                'email' => $request->email,

                'no_telp' => $request->no_telp ?? null,

                'jenis_rekening' => $request->jenis_rekening,
                'kode_kelas' => $request->kode_kelas,
                'saldo' => $saldoBersih,

                // HAPUS BARIS INI (KARENA KOLOM USERNAME DIHAPUS)
                // 'username' => $no_rekening_baru, 

                'password' => Hash::make($request->password),
                'status' => 'aktif',
            ]);

            // B. Catat Transaksi 1: Setoran Awal
            $jenisSetor = JenisTransaksi::where('nama_jenis', 'Setor Tunai')->first();
            $kodeJenisSetor = $jenisSetor ? $jenisSetor->kode_jenis : 'SETOR';

            Transaksi::create([
                'kode_transaksi' => 'TRX-' . time() . '-' . rand(100, 999),
                'no_rekening' => $no_rekening_baru,
                'kode_petugas' => $request->user()->kode_petugas ?? null,
                'kode_jenis' => $kodeJenisSetor,
                'tgl_transaksi' => now(),
                'jumlah' => $request->saldo_awal,
                'status' => 'success',
                'keterangan_nasabah' => 'Setoran Awal Pendaftaran',
                // Data Historis - (Dihapus sesuai request sebelumnya)
            ]);

            // C. Catat Transaksi 2: Potongan Admin
            if ($biayaAdmin > 0) {
                $jenisAdmin = JenisTransaksi::where('nama_jenis', 'Biaya Admin')->first();
                $kodeJenisAdmin = $jenisAdmin ? $jenisAdmin->kode_jenis : 'AWAL';

                Transaksi::create([
                    'kode_transaksi' => 'ADM-' . (time() + 1) . '-' . rand(100, 999),
                    'no_rekening' => $no_rekening_baru,
                    'kode_petugas' => $request->user()->kode_petugas ?? null,
                    'kode_jenis' => $kodeJenisAdmin,
                    'tgl_transaksi' => now(),
                    'jumlah' => $biayaAdmin,
                    'status' => 'success',
                    'keterangan_nasabah' => 'Potongan Biaya Admin Pendaftaran',
                    // Data Historis - (Dihapus sesuai request sebelumnya)
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Nasabah Berhasil Didaftarkan. No Rek: ' . $no_rekening_baru,
                'detail' => [
                    'uang_diterima' => $request->saldo_awal,
                    'potongan_admin' => $biayaAdmin,
                    'saldo_akhir' => $saldoBersih
                ],
                'data' => $nasabah
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update Profil Mandiri
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'password' => 'nullable|string|min:6',
            'email'    => 'nullable|email',
            'no_telp'  => 'nullable|string',
=======
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NasabahController extends Controller
{
    /**
     *add nasabah
     */
    public function storeNasabah(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:nasabah,username',
            'password' => 'required|string|min:6',
            'nama' => 'required|string',
            'no_induk' => 'required|string|unique:nasabah,no_induk',
            'jenis_rekening' => 'required|in:siswa,guru',
            'email' => 'nullable|email',
            'no_telp' => 'nullable|string',
            'alamat' => 'nullable|string',
            'kelas_id' => 'nullable|required_if:jenis_rekening,siswa|exists:kelas,id',
            'saldo_awal' => 'required|integer|min:0',
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

<<<<<<< HEAD
        $updateData = [
            'email' => $request->email,
            'no_telp' => $request->no_telp,
            // Username tidak bisa diupdate
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => $user
        ]);
    }

    /**
     * Hapus Nasabah (Hanya jika saldo 0)
     */
    public function deleteNasabah($no_rekening)
    {
        $nasabah = Nasabah::where('no_rekening', $no_rekening)->first();

        if (!$nasabah) {
            return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
        }

        if ($nasabah->saldo > 0) {
            return response()->json(['message' => 'Gagal: Nasabah masih memiliki saldo.'], 422);
        }

        $nasabah->delete();
        return response()->json(['message' => 'Nasabah berhasil dihapus']);
    }

    public function updateNasabah(Request $request, $no_rekening)
    {
        $nasabah = Nasabah::where('no_rekening', $no_rekening)->first();
=======
        //menyiapkan data historis sebelum transaksi database dimulai
        $kelas = null;
        $jurusan = null;
        if ($request->jenis_rekening == 'siswa') {
            //mengambil data kelas dan jurusan dr relasi utk data historis
            $kelas = Kelas::with('jurusan')->find($request->kelas_id);
            //make sure kelas ada di jurusan
            if ($kelas) {
                $jurusan = $kelas->jurusan;
            }
        }

        //mengambil id jenis utk saldo awal
        $jenisSaldoAwal = JenisTransaksi::where('nama_jenis', 'Saldo Awal')->first();
        if (!$jenisSaldoAwal) {
            return response()->json(['message' => 'Error: Jenis Transaksi "Saldo Awal" tidak ditemukan. Harap seed database.'], 500);
        }
        // DB::transaction() memastikan jika salah satu query gagal,
        //semua query akan di-ROLLBACK (dibatalkan).
        try {
            DB::transaction(function () use ($request, $kelas, $jurusan, $jenisSaldoAwal) {

                //buat nasabah
                $nasabah = Nasabah::create([
                    //login
                    'username' => $request->username,
                    'password' => Hash::make($request->password), //enkrip password
                    'no_rekening' => $this->generateNoRekening(), //panggil fungsi helper
                    'nama' => $request->nama,
                    'no_induk' => $request->no_induk,
                    'jenis_rekening' => $request->jenis_rekening,
                    'email' => $request->email,
                    'no_telp' => $request->no_telp,
                    'alamat' => $request->alamat,
                    'kelas_id' => $request->kelas_id, //null kl guru
                    'saldo' => $request->saldo_awal, //saldo 'live' diisi
                ]);
                // buat transaksi saldo awal
                Transaksi::create([
                    // Data Hubungan
                    'no_rekening' => $nasabah->no_rekening,
                    'petugas_id' => $request->user()->id, //id petugas yg sedang login
                    'jenis_transaksi_id' => $jenisSaldoAwal->id,

                    // Data Transaksi
                    'tgl_transaksi' => now(), // Diisi sekarang
                    'jumlah' => $request->saldo_awal,

                    // Data Workflow
                    'status' => 'approved', // Transaksi Saldo Awal auto-approved
                    'keterangan_nasabah' => 'Pendaftaran nasabah baru',

                    // === DATA SNAPSHOT (INTI LAPORAN) ===
                    'nama_saat_transaksi' => $nasabah->nama,
                    'kelas_saat_transaksi' => $kelas ? $kelas->nama_kelas : null, //ambil nama kelas
                    'jurusan_saat_transaksi' => $jurusan ? $jurusan->nama_jurusan : null,
                    'saldo_sebelum' => 0, //set saldo awal 0
                    'saldo_setelah' => $request->saldo_awal,
                ]);
            });
        } catch (\Exception $e) {
            //error, kirim respon
            return response()->json(['message' => 'Gagal mendaftarkan nasabah.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Nasabah baru berhasil didaftarkan.'], 201);
    }

    /**
     * Helper function untuk membuat No Rekening unik.
     * Format: BSM- [6 digit angka random]
     */
    private function generateNoRekening()
    {
        do {
            $noRek = 'BSM-' . mt_rand(100000, 999999);
            // Cek ke DB apakah noRek sudah ada. Jika sudah, ulangi (loop).
            $cek = Nasabah::where('no_rekening', $noRek)->first();
        } while ($cek);

        return $noRek;
    }

    // method utk mengambil data nasabah (web)
    public function getNasabah(Request $request)
    {
        $nasabah = Nasabah::with(['kelas', 'kelas.jurusan'])
            ->where('status', 'aktif')
            ->orderBy('nama', 'asc')
            ->get();

        return response()->json($nasabah);
    }

    // method utk mengambil detail nasabah by no_rekening
    public function showNasabah($no_rekening)
    {
        $nasabah = Nasabah::with(['kelas', 'kelas.jurusan'])
            ->find($no_rekening);
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740

        if (!$nasabah) {
            return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
        }
<<<<<<< HEAD

        // PERBAIKAN DI SINI:
        // Kita beri tahu Laravel: "Cek email unik di tabel nasabah, tapi abaikan baris yang no_rekening-nya sama dengan nasabah ini"
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'email' => 'required|email|unique:nasabah,email,' . $nasabah->no_rekening . ',no_rekening',
            'no_telp' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $nasabah->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'no_telp' => $request->no_telp,
        ]);

        return response()->json(['message' => 'Berhasil memperbarui data']);
    }
    /**
     * Bulk Update Status (Dari kodingan teman Anda)
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_rekening_list' => 'required|array|min:1',
            'no_rekening_list.*' => 'string|exists:nasabah,no_rekening',
=======
        return response()->json($nasabah);
    }

    // method utk update nasabah
    public function updateNasabah(Request $request, $no_rekening)
    {
        $nasabah = Nasabah::find($no_rekening);
        if (!$nasabah) {
            return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
        }

        // validasi input
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:nasabah,username,' . $no_rekening . ',no_rekening',
            'nama' => 'required|string',
            'no_induk' => 'required|string|unique:nasabah,no_induk,' . $no_rekening . ',no_rekening',
            'jenis_rekening' => 'required|in:siswa,guru',
            'email' => 'nullable|email',
            'kelas_id' => 'nullable|required_if:jenis_rekening,siswa|exists:kelas,id',
            // tidak izinkan update password atau saldo di sini
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $nasabah->update($request->except(['password', 'saldo'])); //kecuali
        return response()->json($nasabah);
    }
    // method utk delete nasabah
    public function deleteNasabah(Request $request, $no_rekening)
    {
        $nasabah = Nasabah::find($no_rekening);
        if (!$nasabah) {
            return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
        }

        //cek saldo
        if ($nasabah->saldo > 0) {
            return response()->json(['message' => 'Hapus Gagal: Nasabah masih memiliki sisa saldo. Harap tarik tunai terlebih dahulu.'], 409);
        }

        //cek transaksi pending
        if ($nasabah->transaksi()->where('status', 'pending')->count() > 0) {
            return response()->json(['message' => 'Hapus Gagal: Nasabah masih memiliki transaksi pending.'], 409);
        }

        $nasabah->status = 'nonaktif';
        $nasabah->save();

        return response()->json(['message' => 'Nasabah berhasil dinonaktifkan.']);
    }

    public function bulkUpdateStatus(Request $request)
    {
        // validasi input
        $validator = Validator::make($request->all(), [
            'no_rekening_list' => 'required|array|min:1',
            'no_rekening_list.*' => 'string|exists:nasabah,no_rekening', //cek array per item
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
            'status_baru' => 'required|in:aktif,nonaktif,alumni',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

<<<<<<< HEAD
        // Cek saldo jika mau dinonaktifkan (Opsional)
        // ... (Logika cek saldo bisa ditambah di sini)

        try {
            Nasabah::whereIn('no_rekening', $request->no_rekening_list)
                ->update(['status' => $request->status_baru]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal update status.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Berhasil update status.']);
    }

    //Statistik Dashboard
    // app/Http/Controllers/Api/NasabahController.php

    public function getDashboardStats()
    {
        try {
            $totalSaldo = \App\Models\Nasabah::sum('saldo'); // Total uang nasabah
            $totalNasabah = \App\Models\Nasabah::count(); // Jumlah nasabah terdaftar

            // Menghitung setoran masuk hari ini (opsional jika tabel transaksi sudah ada)
            $setoranHariIni = \App\Models\Transaksi::where('jenis_transaksi', 'setor')
                ->whereDate('created_at', today())
                ->sum('nominal');

            return response()->json([
                'total_saldo' => $totalSaldo,
                'total_nasabah' => $totalNasabah,
                'setoran_hari_ini' => $setoranHariIni,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memuat statistik'], 500);
        }
=======
        $listNoRekening = $request->no_rekening_list;
        $statusBaru = $request->status_baru;

        // filter nasabah yg ada saldonya
        $masihPunyaSaldo = Nasabah::whereIn('no_rekening', $listNoRekening)
            ->where('saldo', '>', 0)
            ->pluck('nama', 'no_rekening');

        if ($masihPunyaSaldo->isNotEmpty()) {
            return response()->json([
                'message' => 'Gagal: Beberapa nasabah masih memiliki sisa saldo.',
                'gagal_karena_saldo' => $masihPunyaSaldo
            ], 409); // 409 Conflict
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

        // Jika semua aman (saldo 0 dan tidak ada pending), jalankan 1 query UPDATE massal.
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
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
    }
}
