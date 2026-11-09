<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model {
    protected $primaryKey = 'id_pelanggan';
    protected $fillable = ['nama_pelanggan'];

    public function alergis() {
        return $this->hasMany(AlergiPelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
}