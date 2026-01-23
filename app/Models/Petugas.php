<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
<<<<<<< HEAD
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Petugas extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory;
    protected $table = 'petugas';
    protected $primaryKey = 'kode_petugas';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
=======
use Illuminate\Notifications\Notifiable;

class Petugas extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'petugas';
    // kolom yang diisi
    protected $fillable = [
        'nama_petugas',
        'username',
        'password',
        'role',
    ];
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
    // kolom yang disembunyikan
    protected $hidden = [
        'password',
        'remember_token',
    ];
<<<<<<< HEAD

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
=======
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
}
