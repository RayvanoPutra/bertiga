<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Nasabah extends Authenticatable implements JWTSubject
{
    use HasFactory, HasApiTokens;

    protected $table = 'nasabah';
    protected $primaryKey = 'no_rekening'; //primarykey
    public $incrementing = false;        //tidak increment
    protected $keyType = 'string';
    protected $guarded = [];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'no_rekening',
        'no_induk',
        'nama',
        'email',
        'password',
        'no_telp',
        'jenis_rekening',
        'kode_kelas',
        'saldo',
        'status',
    ];
    
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kode_kelas', 'kode_kelas');
    }
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Mengambil no_rekening sebagai ID utama di dalam token
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => 'nasabah', // Kita tanamkan label 'nasabah' di dalam token
            'no_rekening' => $this->no_rekening
        ];
    }
}
