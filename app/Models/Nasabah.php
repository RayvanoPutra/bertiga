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
}
