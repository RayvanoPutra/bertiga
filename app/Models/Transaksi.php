<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    
    // Primary Key String
    protected $primaryKey = 'kode_transaksi';
    public $incrementing = false;
    protected $keyType = 'string';

    // Menggunakan $fillable agar lebih jelas dan aman
    // Sesuai permintaan dosen: TIDAK ADA kolom snapshot/historis di sini.
    protected $fillable = [
        'kode_transaksi',
        'no_rekening',
        'kode_petugas',
        'kode_jenis',
        'tgl_transaksi',
        'jumlah',
        'status',
        'keterangan_nasabah',
    ];

    // Relasi ke Nasabah
    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class, 'no_rekening', 'no_rekening');
    }

    // Relasi ke Petugas
    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'kode_petugas', 'kode_petugas');
    }

    // Relasi ke Jenis Transaksi
    public function jenisTransaksi()
    {
        return $this->belongsTo(JenisTransaksi::class, 'kode_jenis', 'kode_jenis');
    }
}