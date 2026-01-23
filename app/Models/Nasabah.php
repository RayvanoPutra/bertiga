<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Tambahkan ini untuk relasi transaksi
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Nasabah extends Authenticatable implements JWTSubject
{
    use HasFactory, HasApiTokens;

    protected $table = 'nasabah';
    protected $primaryKey = 'no_rekening'; 
    public $incrementing = false; 
    protected $keyType = 'string';
    
    protected $guarded = [];

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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * RELASI: Ke tabel Transaksi
     * Diperlukan agar sistem bisa mengecek riwayat potongan admin
     */
    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'no_rekening', 'no_rekening');
    }

    /**
     * RELASI: Ke tabel Kelas
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kode_kelas', 'kode_kelas');
    }

    // --- JWT METHODS ---

    public function getJWTIdentifier()
    {
        return $this->getKey(); 
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => 'nasabah', 
            'no_rekening' => $this->no_rekening
        ];
    }
}