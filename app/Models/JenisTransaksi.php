<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JenisTransaksi extends Model
{
    use HasFactory;

    protected $table = 'jenis_transaksi';
<<<<<<< HEAD
    protected $primaryKey = 'kode_jenis';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
=======
    protected $fillable = ['nama_jenis'];
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
