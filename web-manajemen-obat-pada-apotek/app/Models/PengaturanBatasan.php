<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PengaturanBatasan extends Model {
    protected $table = 'pengaturan_batasans';
    protected $primaryKey = 'id_batasan';
    protected $fillable = ['id_perangkat', 'suhu_minimal', 'suhu_maksimal', 'lembap_minimal', 'lembap_maksimal'];

    public function perangkat() {
        return $this->belongsTo(Perangkat::class, 'id_perangkat', 'id_perangkat');
    }
}