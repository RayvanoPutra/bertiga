<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
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
    // kolom yang disembunyikan
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
