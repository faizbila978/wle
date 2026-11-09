<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DataSensor extends Model {
    protected $table = 'data_sensors';
    protected $primaryKey = 'id_sensor';
    protected $fillable = ['id_perangkat', 'suhu', 'kelembapan', 'waktu_catat'];

    public function perangkat() {
        return $this->belongsTo(Perangkat::class, 'id_perangkat', 'id_perangkat');
    }
}