<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningAlergi extends Model {
    protected $primaryKey = 'id_screening';
    protected $fillable = ['id_pelanggan', 'id_obat', 'id_alergi', 'id_pengelola', 'tgl_screening', 'hasil'];

    public function pelanggan() {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function obat() {
        return $this->belongsTo(Obat::class, 'id_obat', 'id_obat');
    }

    public function alergi() {
        return $this->belongsTo(AlergiPelanggan::class, 'id_alergi', 'id_alergi');
    }
}