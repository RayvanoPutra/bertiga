<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
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
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
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
}
