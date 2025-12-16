<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajaran';
    protected $primaryKey = 'kode_tahun_ajaran';
    public $incrementing = false; 
    protected $keyType = 'string';
    protected $guarded = [];
    
    public function kelas(): HasMany
    {
        // PK tabel ini 'id', FK di 'kelas' adalah 'tahun_ajaran_id' (standar Laravel)
        
        return $this->hasMany(Kelas::class, 'kode_tahun_ajaran');
    }
}