<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';
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
