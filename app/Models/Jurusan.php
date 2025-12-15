<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurusan extends Model
{
    use HasFactory;

    protected $table = 'jurusan';
    protected $primaryKey = 'kode_jurusan';
    //pk tidak increment 
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'kode_jurusan', 'kode_jurusan');
    }
}