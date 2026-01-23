<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';
<<<<<<< HEAD
    protected $primaryKey = 'kode_kelas'; 
    public $incrementing = false;
    protected $keyType = 'string';
    
    // Gunakan fillable atau guarded untuk keamanan mass assignment
    protected $guarded = [];

    // RELASI 1: Ke tabel Jurusan
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'kode_jurusan', 'kode_jurusan');
    }

    // RELASI 2: Ke tabel Tahun Ajaran (HAPUS SALAH SATU JIKA ADA DUA)
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'kode_tahun_ajaran', 'kode_tahun_ajaran');
    }
}
=======
    protected $fillable = ['tahun_ajaran_id', 'kode_jurusan', 'nama_kelas'];

    //Relasi ke tahun ajaran 1 to many
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function jurusan(): BelongsTo
    {
        // Non-Standar: Kita harus beri tahu nama FK dan PK-nya
        // Parameter ke-2: Foreign Key di tabel 'kelas' (tabel ini)
        // Parameter ke-3: Primary Key di tabel 'jurusan' (tabel tujuan)
        return $this->belongsTo(Jurusan::class, 'kode_jurusan', 'kode_jurusan');
    }

    public function nasabah(): HasMany
    {
        return $this->hasMany(Nasabah::class);
    }

}
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
