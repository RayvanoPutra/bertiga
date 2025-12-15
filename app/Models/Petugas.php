<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Petugas extends Authenticatable
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
}
