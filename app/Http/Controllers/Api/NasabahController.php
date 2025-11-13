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
            //informasi login
            'username' => 'required|string|unique:nasabah,username',
            'password' => 'required|string|min:6',
            //informasi diri
            'nama' => 'required|string',
            'no_induk' => 'required|string|unique:nasabah,no_induk',
            'jenis_rekening' => 'required|in:siswa,guru',
            'email' => 'nullable|email',
            'no_telp' => 'nullable|string',
            'alamat' => 'nullable|string',
            //info kelas (jika siswa)
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
            $jurusan = $kelas->jurusan;
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
                    'kelas_id' => $request->kelas_id, //teteo NULL jika guru
                    'saldo' => $request->saldo_awal, //saldo 'live' diisi
                ]);

                // --- AKSI 2: Buat Transaksi "Saldo Awal" (Snapshot) ---
                Transaksi::create([
                    // Data Hubungan
                    'no_rekening' => $nasabah->no_rekening,
                    'petugas_id' => $request->user()->id, // Diisi ID petugas yg sedang login
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
}
