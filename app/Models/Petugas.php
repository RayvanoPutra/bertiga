<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Petugas extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;
    protected $table = 'petugas';
    protected $fillable = [
        'nama',
        'username',
        'password',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
