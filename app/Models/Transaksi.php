<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    use HasFactory;
<<<<<<< HEAD

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

    // public function nasabah()
    // {
    //     return $this->belongsTo(Nasabah::class, 'no_rekening', 'no_rekening');
    // }
=======
    protected $table = 'transaksi';
    protected $guarded = [];

    //relasi ke nasabah 
    public function nasabah(): BelongsTo
    {
        // Non-Standar: Kita harus beri tahu nama FK dan PK-nya
        // Parameter ke-2: Foreign Key di tabel 'transaksi' (tabel ini)
        // Parameter ke-3: Primary Key di tabel 'nasabah' (tabel tujuan)
        return $this->belongsTo(Nasabah::class, 'no_rekening', 'no_rekening');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Petugas::class);
    }

    public function jenisTransaksi(): BelongsTo
    {
        return $this->belongsTo(JenisTransaksi::class);
    }
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
}
