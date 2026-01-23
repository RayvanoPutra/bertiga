<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\Relations\HasMany;
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740

class Jurusan extends Model
{
    use HasFactory;

    protected $table = 'jurusan';
<<<<<<< HEAD
    protected $primaryKey = 'kode_jurusan'; 
    public $incrementing = false; 
    protected $keyType = 'string';

    // HANYA dua kolom ini yang boleh diisi
    protected $fillable = [
        'kode_jurusan', 
        'nama_jurusan'
    ];

    /**
     * Relasi ke Kelas
     * (Jurusan tidak punya Tahun Ajaran, tapi Jurusan punya banyak Kelas)
     */
    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'kode_jurusan', 'kode_jurusan');
    }
}
=======

    //primary key tabel jurusan
    protected $primaryKey = 'kode_jurusan';
    //pk tidak increment 
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['kode_jurusan', 'nama_jurusan'];

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'kode_jurusan', 'kode_jurusan');
    }
}
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
