<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Perangkat extends Model {
    protected $table = 'perangkats';
    protected $primaryKey = 'id_perangkat';
    protected $fillable = ['nama_perangkat', 'lokasi', 'status'];

    public function dataSensors() {
        return $this->hasMany(DataSensor::class, 'id_perangkat', 'id_perangkat');
    }

    public function batasan() {
        return $this->hasOne(PengaturanBatasan::class, 'id_perangkat', 'id_perangkat');
    }
}