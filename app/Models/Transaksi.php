<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    use HasFactory;
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
}
