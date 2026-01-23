<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Nasabah;
use App\Models\JenisTransaksi;
use App\Models\Pengaturan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class TransaksiController extends Controller
{
    // --- REQUEST SETOR (Android) ---
    public function requestSetor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|integer|min:1000',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $nasabah = $request->user();
        $jenisSetor = JenisTransaksi::where('nama_jenis', 'Setor Tunai')->firstOrFail();

        $kelas = $nasabah->kelas;
        $jurusan = $kelas ? $kelas->jurusan : null;

        $transaksi = Transaksi::create([
            'kode_transaksi' => 'TRX-' . time() . '-' . rand(100, 999),
            'no_rekening' => $nasabah->no_rekening,
            'kode_jenis' => $jenisSetor->kode_jenis,
            'kode_petugas' => null,
            'tgl_transaksi' => null,
            'jumlah' => $request->jumlah,
            'status' => 'pending',
            'keterangan_nasabah' => 'Request Setor Tunai',
            'nama_saat_transaksi' => $nasabah->nama,
            'kelas_saat_transaksi' => $kelas ? $kelas->nama_kelas : null,
            'jurusan_saat_transaksi' => $jurusan ? $jurusan->nama_jurusan : null,
            'saldo_sebelum' => $nasabah->saldo,
            'saldo_setelah' => 0,
        ]);

        return response()->json(['message' => 'Berhasil dibuat', 'data' => $transaksi], 201);
    }

    // --- REQUEST TARIK (Android) ---
    public function requestTarik(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|integer|min:1000',
            'keterangan_nasabah' => 'required|string|min:5',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $nasabah = $request->user();
        if ($nasabah->saldo < $request->jumlah) {
            return response()->json(['message' => 'Saldo tidak mencukupi.'], 422);
        }

        $jenisTarik = JenisTransaksi::where('nama_jenis', 'Tarik Tunai')->firstOrFail();
        $kelas = $nasabah->kelas;
        $jurusan = $kelas ? $kelas->jurusan : null;

        $transaksi = Transaksi::create([
            'kode_transaksi' => 'TRX-' . time() . '-' . rand(100, 999),
            'no_rekening' => $nasabah->no_rekening,
            'kode_jenis' => $jenisTarik->kode_jenis,
            'kode_petugas' => null,
            'jumlah' => $request->jumlah,
            'status' => 'pending',
            'keterangan_nasabah' => $request->keterangan_nasabah,
            'tgl_transaksi' => null,
            'nama_saat_transaksi' => $nasabah->nama,
            'kelas_saat_transaksi' => $kelas ? $kelas->nama_kelas : null,
            'jurusan_saat_transaksi' => $jurusan ? $jurusan->nama_jurusan : null,
            'saldo_sebelum' => $nasabah->saldo,
            'saldo_setelah' => 0,
        ]);

        return response()->json(['message' => 'Berhasil dibuat', 'data' => $transaksi], 201);
    }

    public function approve(Request $request, $kode_transaksi)
{
    try {
        return DB::transaction(function () use ($request, $kode_transaksi) {
            // 1. Cari transaksi pending
            $transaksi = Transaksi::with('jenisTransaksi')
                ->where('kode_transaksi', $kode_transaksi)
                ->firstOrFail();

            if ($transaksi->status != 'pending') {
                throw ValidationException::withMessages(['status' => 'Transaksi sudah diproses.']);
            }

            // 2. Ambil data nasabah
            $nasabah = Nasabah::where('no_rekening', $transaksi->no_rekening)->lockForUpdate()->firstOrFail();
            $saldo_awal_nasabah = (int)$nasabah->saldo;
            $jumlah_trx = (int)$transaksi->jumlah;
            $jenisNama = $transaksi->jenisTransaksi->nama_jenis;

            // 3. Hitung Saldo (Setor/Tarik)
            if ($jenisNama == 'Setor Tunai') {
                $saldo_skrg = $saldo_awal_nasabah + $jumlah_trx;
            } else {
                if ($saldo_awal_nasabah < $jumlah_trx) {
                    throw ValidationException::withMessages(['saldo' => 'Saldo tidak mencukupi.']);
                }
                $saldo_skrg = $saldo_awal_nasabah - $jumlah_trx;
            }

            // 4. LOGIKA POTONGAN ADMIN BERKALA
            $keterangan = $transaksi->keterangan_nasabah;
            // 4. LOGIKA POTONGAN ADMIN TAHUNAN (FLEXIBLE ANNIVERSARY)
if ($jenisNama == 'Setor Tunai' && $nasabah->jenis_rekening == 'siswa') {
    $settingBiaya = Pengaturan::where('nama_pengaturan', 'jumlah_potongan')->first();
    $nominalBiaya = $settingBiaya ? (int)$settingBiaya->nilai : 11000;
    
    $tglDaftar = $nasabah->created_at; 
    $sekarang = now();

    // 1. Cek apakah sudah beda tahun kalender
    $isBedaTahun = ($sekarang->year > $tglDaftar->year);

    // 2. Cek apakah tanggal sekarang sudah mencapai atau melewati tanggal daftar (M-D)
    // Contoh: '01-22' >= '02-23' -> False (Belum waktunya bayar)
    // Contoh: '01-22' >= '01-10' -> True (Sudah waktunya/lewat tanggal)
    $sudahMasukBulanTanggal = ($sekarang->format('m-d') >= $tglDaftar->format('m-d'));

    if ($isBedaTahun && $sudahMasukBulanTanggal) {
        // 3. Pastikan belum ada potongan ADM di tahun berjalan (2026)
        $sudahBayarTahunIni = Transaksi::where('no_rekening', $nasabah->no_rekening)
            ->where('kode_jenis', 'ADM')
            ->where('status', 'success')
            ->whereYear('tgl_transaksi', $sekarang->year)
            ->exists();

        if (!$sudahBayarTahunIni && $saldo_skrg >= $nominalBiaya) {
            $saldo_skrg -= $nominalBiaya;

            Transaksi::create([
                'kode_transaksi' => 'ADM-' . time() . '-' . $nasabah->no_rekening,
                'no_rekening' => $nasabah->no_rekening,
                'kode_petugas' => $request->user()->kode_petugas,
                'kode_jenis' => 'ADM', 
                'tgl_transaksi' => $sekarang,
                'jumlah' => $nominalBiaya,
                'status' => 'success',
                'keterangan_nasabah' => 'Biaya Admin Tahunan ' . $sekarang->year,
            ]);

            $keterangan .= " (Auto-Debit Admin)";
        }
    }
}

            // 5. Update Nasabah & Transaksi (HAPUS SALDO_SEBELUM & SESUDAH)
            $nasabah->update(['saldo' => $saldo_skrg]);
            $transaksi->update([
                'status' => 'success',
                'kode_petugas' => $request->user()->kode_petugas,
                'tgl_transaksi' => now(),
                'keterangan_nasabah' => $keterangan
            ]);

            return response()->json(['message' => 'Transaksi berhasil disetujui.']);
        });
    } catch (\Exception $e) {
        // Mengembalikan pesan error asli agar Anda bisa melihat jika ada kolom yang kurang
        return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
    }
}


    // --- REJECT ---
    public function reject(Request $request, $kode_transaksi)
    {
        $transaksi = Transaksi::where('kode_transaksi', $kode_transaksi)
            ->where('status', 'pending')
            ->firstOrFail();

        $transaksi->update([
            'status' => 'rejected',
            'kode_petugas' => $request->user()->kode_petugas,
            'tgl_transaksi' => now(),
        ]);

        return response()->json(['message' => 'Transaksi ditolak.']);
    }

    // --- GET PENDING ---
    public function getPending(Request $request)
    {
        $pending = Transaksi::with(['nasabah', 'jenisTransaksi'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
        return response()->json($pending);
    }

    // --- HISTORY ADMIN ---
    public function getHistoryAdmin(Request $request)
{
    // Eager loading untuk relasi yang dibutuhkan
    $query = Transaksi::with(['nasabah.kelas.jurusan', 'nasabah.tahunAjaran', 'petugas', 'jenisTransaksi']);

    // BAGIAN YANG DIHAPUS: $query->where('kode_transaksi', 'not like', 'ADM-%');
    // Sekarang transaksi dengan kode ADM- (Potongan Awal) akan ikut tampil.

    // Filter Search
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('no_rekening', 'like', "%$search%")
                ->orWhereHas('nasabah', function ($n) use ($search) {
                    $n->where('nama', 'like', "%$search%");
                });
        });
    }

    // Filter Jenis
    if ($request->filled('jenis')) {
        $query->where('kode_jenis', $request->jenis);
    }

    // Filter Bulan
    if ($request->filled('bulan')) {
        $date = explode('-', $request->bulan);
        if (count($date) == 2) {
            $query->whereYear('tgl_transaksi', $date[0])
                ->whereMonth('tgl_transaksi', $date[1]);
        }
    }

    // Filter Jurusan
    if ($request->filled('jurusan')) {
        $query->whereHas('nasabah.kelas', function ($k) use ($request) {
            $k->where('kode_jurusan', $request->jurusan);
        });
    }

    // Filter Kelas
    if ($request->filled('kelas')) {
        $query->whereHas('nasabah', function ($n) use ($request) {
            $n->where('kode_kelas', $request->kelas);
        });
    }

    // Filter Tahun Ajaran
    if ($request->filled('tahun') && $request->tahun !== 'undefined') {
        $query->whereHas('nasabah.kelas', function ($q) use ($request) {
            $q->where('kode_tahun_ajaran', $request->tahun);
        });
    }

    // Mengambil data yang statusnya bukan pending (success/rejected)
    $history = $query->where('status', '!=', 'pending')
        ->orderBy('tgl_transaksi', 'desc')
        ->get();

    return response()->json($history);
}

    public function getHistoryNasabah(Request $request)
    {
        $nasabah = $request->user();
        $history = Transaksi::with('jenisTransaksi')
            ->where('no_rekening', $nasabah->no_rekening)
            ->where('status', '!=', 'pending')
            ->orderBy('tgl_transaksi', 'desc')
            ->get();
        return response()->json($history);
    }

    // --- CETAK PDF ---
    public function cetakPdf(Request $request)
{
    // 1. Inisialisasi Query dengan Eager Loading
    $query = Transaksi::with(['nasabah.kelas.jurusan', 'jenisTransaksi', 'petugas'])
        ->where('status', '!=', 'pending');

    // 2. Filter Search (Rekening atau Nama)
    if ($request->filled('search') && $request->search !== 'undefined') {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('no_rekening', 'like', "%$search%")
                ->orWhereHas('nasabah', function ($n) use ($search) {
                    $n->where('nama', 'like', "%$search%");
                });
        });
    }

    // 3. Filter Jenis (SETOR/TARIK/AWAL)
    if ($request->filled('jenis') && $request->jenis !== 'undefined') {
        $query->where('kode_jenis', $request->jenis);
    }

    // 4. Filter Bulan
    if ($request->filled('bulan') && $request->bulan !== 'undefined') {
        $date = explode('-', $request->bulan);
        if (count($date) == 2) {
            $query->whereYear('tgl_transaksi', $date[0])
                ->whereMonth('tgl_transaksi', $date[1]);
        }
    }

    // 5. Filter Jurusan (INI YANG SERING TERLEWAT)
    if ($request->filled('jurusan') && $request->jurusan !== 'undefined') {
        $query->whereHas('nasabah.kelas', function ($q) use ($request) {
            $q->where('kode_jurusan', $request->jurusan);
        });
    }

    // 6. Filter Kelas
    if ($request->filled('kelas') && $request->kelas !== 'undefined') {
        $query->whereHas('nasabah', function ($q) use ($request) {
            $q->where('kode_kelas', $request->kelas);
        });
    }

    // 7. Filter Tahun Ajaran
    if ($request->filled('tahun') && $request->tahun !== 'undefined') {
        $query->whereHas('nasabah.kelas', function ($q) use ($request) {
            $q->where('kode_tahun_ajaran', $request->tahun);
        });
    }

    // 8. Ambil Data setelah di Filter
    $transaksi = $query->orderBy('tgl_transaksi', 'desc')->get();

    // Logika petugas login dan settings tetap sama seperti sebelumnya...
    $user = auth()->user();
    $petugasLogin = (object) [
        'nama' => $user ? ($user->nama_petugas ?? $user->nama) : ($request->nama_petugas ?? 'Administrator'),
        'kode' => $user ? ($user->kode_petugas ?? $user->username) : ($request->kode_petugas ?? '-')
    ];

    $infoFilter = [
        'kode_tahun_ajaran' => $request->tahun ?: 'Semua Tahun',
        'kelas'             => $request->kelas ?: 'Semua Kelas',
        'bulan'             => $request->bulan ?: 'Semua Bulan'
    ];

    $settings = \App\Models\Pengaturan::pluck('nilai', 'nama_pengaturan')->toArray();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.transaksi_rekap', compact('transaksi', 'infoFilter', 'petugasLogin', 'settings'))
          ->setPaper('a4', 'landscape');

    return $pdf->stream('Laporan_Transaksi.pdf');
}
}
