<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
<<<<<<< HEAD
    protected $table = 'tahun_ajaran';
    protected $primaryKey = 'kode_tahun_ajaran'; // Nama kolom PK Anda
    
    // WAJIB ADA: Beritahu Laravel PK Anda bukan angka auto-increment
    public $incrementing = false; 
    protected $keyType = 'string';

    // WAJIB ADA: Agar kolom bisa diisi (Mass Assignment)
    protected $fillable = [
        'kode_tahun_ajaran', 
        'tahun_ajaran', 
        'status'
    ];
=======
    use HasFactory;

    protected $table = 'tahun_ajaran';
    protected $fillable = ['tahun_ajaran', 'status'];
    public function kelas(): HasMany
    {
        // PK tabel ini 'id', FK di 'kelas' adalah 'tahun_ajaran_id' (standar Laravel)
        return $this->hasMany(Kelas::class);
    }
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
}
