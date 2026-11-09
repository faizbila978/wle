<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlergiPelanggan extends Model {
    protected $primaryKey = 'id_alergi';
    protected $fillable = ['id_pelanggan', 'nama_alergi'];

    public function pelanggan() {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
}