<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'pengaturan'; 

    // Kolom-kolom yang boleh diisi (mass assignable)
    protected $fillable = [
        'nama_pengaturan',
        'nilai',
    ];
}