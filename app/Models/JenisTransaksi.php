<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JenisTransaksi extends Model
{
    use HasFactory;

    protected $table = 'jenis_transaksi';
    protected $primaryKey = 'kode_jenis';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
