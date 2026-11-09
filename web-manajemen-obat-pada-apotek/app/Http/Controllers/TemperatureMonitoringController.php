<?php

namespace App\Http\Controllers;

use App\Models\Perangkat;
use App\Models\DataSensor;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TemperatureMonitoringController extends Controller {
    public function index() {
        // Ambil data perangkat beserta sensor terakhir dan batasannya
        $perangkats = Perangkat::with(['dataSensors' => function($q) {
            $q->orderBy('waktu_catat', 'desc');
        }, 'batasan'])->get();

        return view('monitoring.index', compact('perangkats'));
    }

    // Fungsi simulasi untuk mengirim data dummy suhu/kelembapan baru (bisa dipanggil via tombol simulasikan)
    public function simulateSensor(Request $request) {
        $perangkats = Perangkat::all();

        if ($perangkats->isEmpty()) {
            // Buat perangkat default jika belum ada
            $perangkat = Perangkat::create([
                'nama_perangkat' => 'Sensor ESP32 - Gudang Utama',
                'lokasi' => 'Ruang Penyimpanan Obat A',
                'status' => 'Aktif'
            ]);

            \App\Models\PengaturanBatasan::create([
                'id_perangkat' => $perangkat->id_perangkat,
                'suhu_minimal' => 15.0,
                'suhu_maksimal' => 25.0,
                'lembap_minimal' => 40.0,
                'lembap_maksimal' => 60.0,
            ]);
            
            $perangkats = [$perangkat];
        }

        foreach ($perangkats as $p) {
            // Generate nilai acak simulasi suhu (18°C - 28°C) dan kelembapan (45% - 65%)
            DataSensor::create([
                'id_perangkat' => $p->id_perangkat,
                'suhu' => rand(180, 280) / 10,
                'kelembapan' => rand(450, 650) / 10,
                'waktu_catat' => Carbon::now(),
            ]);
        }

        return redirect()->route('monitoring.index')->with('success', 'Simulasi data sensor IoT berhasil diperbarui!');
    }
}