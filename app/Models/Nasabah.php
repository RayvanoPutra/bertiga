<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nasabah extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'nasabah';
    protected $primaryKey = 'no_rekening';
    public $incrementing = false; 
    protected $keyType = 'string';

    protected $fillable = [
        'username',
        'password',
        'no_rekening', // Wajib di fillable karena kita set saat create
        'nama',
        'no_induk',
        'jenis_rekening',
        'email',
        'no_telp',
        'alamat',
        'kode_kelas', // Pastikan ini ada
        'saldo',
        'status', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    // Relasi ke Model Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kode_kelas', 'kode_kelas'); 
    }
}