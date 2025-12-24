<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';
    protected $primaryKey = 'kode_kelas'; 
    public $incrementing = false;
    protected $keyType = 'string';
    
    // Gunakan fillable atau guarded untuk keamanan mass assignment
    protected $guarded = [];

    // RELASI 1: Ke tabel Jurusan
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'kode_jurusan', 'kode_jurusan');
    }

    // RELASI 2: Ke tabel Tahun Ajaran (HAPUS SALAH SATU JIKA ADA DUA)
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'kode_tahun_ajaran', 'kode_tahun_ajaran');
    }
}