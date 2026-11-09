<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obat extends Model {
    use HasFactory;

    protected $table = 'obats';
    protected $primaryKey = 'id_obat';
    protected $fillable = ['nama_obat', 'jenis', 'stok', 'harga'];

    public function kandungans() {
        return $this->hasMany(KandunganObat::class, 'id_obat', 'id_obat');
    }
}