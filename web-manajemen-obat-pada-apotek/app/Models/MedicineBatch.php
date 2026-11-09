<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineBatch extends Model {
    use HasFactory;

    protected $table = 'medicine_batches'; // Sesuaikan jika nama tabel di database berbeda (misal: 'batches' atau 'mutasi_stok')
    protected $primaryKey = 'id'; // Sesuaikan primary key jika ada

    protected $fillable = [
        'id_obat', 'batch_number', 'initial_stock', 'current_stock', 'expired_date', 'type', 'description'
    ];

    public function medicine() {
        return $this->belongsTo(Medicine::class, 'id_obat', 'id_obat');
    }
}