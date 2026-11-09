<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KandunganObat extends Model {
    use HasFactory;

    protected $table = 'kandungan_obats';
    protected $primaryKey = 'id_kandungan';
    protected $fillable = ['id_obat', 'nama_kandungan'];

    public function obat() {
        return $this->belongsTo(Obat::class, 'id_obat', 'id_obat');
    }
}