// Models/Transaksi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    use HasFactory;
    
    protected $table = 'transaksi';
    protected $primaryKey = 'kode_transaksi';
    public $incrementing = false;
    protected $keyType = 'string';
    
    // 🏆 SOLUSI UTAMA: Menonaktifkan timestamps 
    // Karena tabel 'transaksi' di migrasi Anda tidak memiliki kolom created_at dan updated_at.
    public $timestamps = false; 
    
    protected $guarded = [];

    //relasi ke nasabah 
    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class, 'no_rekening', 'no_rekening');
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'kode_petugas', 'kode_petugas');
    }

    public function jenisTransaksi()
    {
        // Parameter ke-2: Foreign Key di tabel 'transaksi' (kode_jenis)
        // Parameter ke-3: Primary Key di tabel 'jenis_transaksi' (kode_jenis)
        return $this->belongsTo(JenisTransaksi::class, 'kode_jenis', 'kode_jenis');
    }
}