<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Siswa extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;
    protected $table = 'siswa';
    protected $fillable = [
        'kelas_id',
        'nis',
        'no_rekening',
        'nama',
        'saldo',
        'password'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
