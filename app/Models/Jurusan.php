<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jurusan extends Model
{
    use HasFactory;

    protected $table = 'jurusan';
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