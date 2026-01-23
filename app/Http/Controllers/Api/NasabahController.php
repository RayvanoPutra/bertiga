<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\Transaksi;
use App\Models\Kelas;
use App\Models\JenisTransaksi;
use App\Models\Pengaturan;
use Barryvdh\DomPDF\Facade\Pdf;
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

            // A. Simpan Data Nasabah
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
            // if ($biayaAdmin > 0) {
            //     $jenisAdmin = JenisTransaksi::where('nama_jenis', 'Biaya Admin')->first();
            //     $kodeJenisAdmin = $jenisAdmin ? $jenisAdmin->kode_jenis : 'AWAL';

            if ($biayaAdmin > 0) {
                $jenisAdmin = JenisTransaksi::where('nama_jenis', 'Biaya Admin')->first();
                $kodeJenisAdmin = $jenisAdmin ? $jenisAdmin->kode_jenis : 'ADM';

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
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

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

        if (!$nasabah) {
            return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
        }

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
            'status_baru' => 'required|in:aktif,nonaktif,alumni',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

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

    // public function getDashboardStats()
    // {
    //     try {
    //         $totalSaldo = \App\Models\Nasabah::sum('saldo'); // Total uang nasabah
    //         $totalNasabah = \App\Models\Nasabah::count(); // Jumlah nasabah terdaftar

    //         // Menghitung setoran masuk hari ini (opsional jika tabel transaksi sudah ada)
    //         $setoranHariIni = \App\Models\Transaksi::where('jenis_transaksi', 'setor')
    //             ->whereDate('created_at', today())
    //             ->sum('nominal');

    //         return response()->json([
    //             'total_saldo' => $totalSaldo,
    //             'total_nasabah' => $totalNasabah,
    //             'setoran_hari_ini' => $setoranHariIni,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Gagal memuat statistik'], 500);
    //     }
    // }

    public function getDashboardStats()
{
    try {
        $totalSaldo = \App\Models\Nasabah::sum('saldo');
        $totalNasabah = \App\Models\Nasabah::count();

        // SALAH: sum('nominal') -> BENAR: sum('jumlah')
        // SALAH: where('jenis_transaksi', 'setor') -> BENAR: where('kode_jenis', 'SETOR')
        $setoranHariIni = \App\Models\Transaksi::where('kode_jenis', 'SETOR')
            ->where('status', 'success') // Pastikan hanya yang sukses
            ->whereDate('tgl_transaksi', today())
            ->sum('jumlah');

        return response()->json([
            'total_saldo' => $totalSaldo,
            'total_nasabah' => $totalNasabah,
            'setoran_hari_ini' => $setoranHariIni,
        ]);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Gagal memuat statistik: ' . $e->getMessage()], 500);
    }
}

    public function cetakLaporan(Request $request)
    {
        $query = Nasabah::with(['kelas.jurusan', 'kelas.tahunAjaran']);

        // Filter Logic (Tetap sama seperti kode Anda)
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                    ->orWhere('no_rekening', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->jurusan) {
            $query->whereHas('kelas', fn ($q) => $q->where('kode_jurusan', $request->jurusan));
        }
        if ($request->kelas) {
            $query->where('kode_kelas', $request->kelas);
        }
        if ($request->tahun_ajaran) {
            $query->whereHas('kelas', fn ($q) => $q->where('kode_tahun_ajaran', $request->tahun_ajaran));
        }

        $data = $query->orderBy('nama', 'asc')->get();
        $settings = Pengaturan::pluck('nilai', 'nama_pengaturan')->toArray();

        // TAMBAHAN: Ambil info detail filter untuk ditampilkan di PDF
        $filterInfo = [
            'jurusan' => $request->jurusan ? \App\Models\Jurusan::where('kode_jurusan', $request->jurusan)->first()?->nama_jurusan : 'Semua Jurusan',
            'kelas' => $request->kelas ? \App\Models\Kelas::where('kode_kelas', $request->kelas)->first()?->nama_kelas : 'Semua Kelas',
            'ta' => $request->tahun_ajaran ? \App\Models\TahunAjaran::where('kode_tahun_ajaran', $request->tahun_ajaran)->first()?->tahun_ajaran : 'Semua Tahun Ajaran',
        ];

        $pdf = Pdf::loadView('pdf.laporan_nasabah', [
            'nasabah' => $data,
            'settings' => $settings,
            'filter' => $filterInfo, // Kirim variabel filter ke view
            'tgl_cetak' => now()->translatedFormat('d F Y')
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan_Keuangan_Nasabah.pdf');
    }
}
