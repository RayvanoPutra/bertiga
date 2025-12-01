<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\Transaksi;
use App\Models\Kelas;
use App\Models\JenisTransaksi;
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
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

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

        if (!$nasabah) {
            return response()->json(['message' => 'Nasabah tidak ditemukan'], 404);
        }
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
            'status_baru' => 'required|in:aktif,nonaktif,alumni',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

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
    }
}
