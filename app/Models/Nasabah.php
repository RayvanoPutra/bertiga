<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
<<<<<<< HEAD
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

=======
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Nasabah extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'nasabah';
    protected $primaryKey = 'no_rekening'; //primarykey
    public $incrementing = false;        //tidak increment
    protected $keyType = 'string';
    protected $guarded = [];
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
    protected $hidden = [
        'password',
        'remember_token',
    ];
<<<<<<< HEAD

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
=======
    
    public function kelas(): BelongsTo
    {
        // Standar: 'kelas_id'
        return $this->belongsTo(Kelas::class);
    }

    // fungsi utk memberi tahukan pk nasabah itu string relasi ke transaksi (polimorfik)
    public function tokens(): MorphMany
    {
        return $this->morphMany(
            \Laravel\Sanctum\PersonalAccessToken::class,
            'tokenable',      //nama relasi (prefix)
            'tokenable_type', //kolom tipe di DB (e.g., 'App\Models\Nasabah')
            'tokenable_id',   //kolom ID di DB (yang sudah kita ubah jadi string)
            'no_rekening'     //kolom PK di tabel nasabah
        );
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'no_rekening', 'no_rekening');
    }
}
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
