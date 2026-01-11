<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Petugas extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory;
    protected $table = 'petugas';
    protected $primaryKey = 'kode_petugas';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    // kolom yang disembunyikan
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role, // <--- PENTING: Pembeda saat Server membaca Token
            'username' => $this->username, // Opsional: Simpan username di token
        ];
    }
}
