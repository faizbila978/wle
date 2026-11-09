<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // --- Bagian 1: Tabel Apotek (Pelanggan, Alergi, Obat, dll) ---
        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id('id_pelanggan');
            $table->string('nama_pelanggan');
            $table->timestamps();
        });

        Schema::create('alergi_pelanggans', function (Blueprint $table) {
            $table->id('id_alergi');
            $table->foreignId('id_pelanggan')->constrained('pelanggans', 'id_pelanggan')->onDelete('cascade');
            $table->string('nama_alergi'); 
            $table->timestamps();
        });

        Schema::create('obats', function (Blueprint $table) {
            $table->id('id_obat');
            $table->string('nama_obat');
            $table->string('jenis'); 
            $table->integer('stok')->default(0);
            $table->decimal('harga', 12, 2)->default(0);
            $table->timestamps();
        });


        Schema::create('kandungan_obats', function (Blueprint $table) {
            $table->id('id_kandungan');
            $table->foreignId('id_obat')->constrained('obats', 'id_obat')->onDelete('cascade');
            $table->string('nama_kandungan'); 
            $table->timestamps();
        });

        Schema::create('screening_alergis', function (Blueprint $table) {
            $table->id('id_screening');
            $table->foreignId('id_pelanggan')->constrained('pelanggans', 'id_pelanggan')->onDelete('cascade');
            $table->foreignId('id_obat')->constrained('obats', 'id_obat')->onDelete('cascade');
            $table->foreignId('id_alergi')->nullable()->constrained('alergi_pelanggans', 'id_alergi')->onDelete('set null');
            $table->unsignedBigInteger('id_pengelola')->nullable(); 
            $table->dateTime('tgl_screening');
            $table->text('hasil'); 
            $table->timestamps();
        });

        // --- Bagian 2: Tabel Perangkat IoT ---
        Schema::create('perangkats', function (Blueprint $table) {
            $table->id('id_perangkat');
            $table->string('nama_perangkat');
            $table->string('lokasi'); 
            $table->string('status')->default('Aktif');
            $table->timestamps();
        });

        Schema::create('data_sensors', function (Blueprint $table) {
            $table->id('id_sensor');
            $table->foreignId('id_perangkat')->constrained('perangkats', 'id_perangkat')->onDelete('cascade');
            $table->float('suhu'); 
            $table->float('kelembapan'); 
            $table->dateTime('waktu_catat');
            $table->timestamps();
        });

        Schema::create('pengaturan_batasans', function (Blueprint $table) {
            $table->id('id_batasan');
            $table->foreignId('id_perangkat')->constrained('perangkats', 'id_perangkat')->onDelete('cascade');
            $table->float('suhu_minimal')->default(15.0);
            $table->float('suhu_maksimal')->default(25.0);
            $table->float('lembap_minimal')->default(40.0);
            $table->float('lembap_maksimal')->default(60.0);
            $table->timestamps();
        });
        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id(); // Primary key auto increment
            $table->foreignId('id_obat')->constrained('obats', 'id_obat')->onDelete('cascade');
            $table->string('batch_number');
            $table->integer('initial_stock');
            $table->integer('current_stock');
            $table->date('expired_date');
            $table->string('type');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        // Hapus tabel secara terbalik dari urutan pembuatan untuk menghindari foreign key constraint error
        Schema::dropIfExists('pengaturan_batasans');
        Schema::dropIfExists('data_sensors');
        Schema::dropIfExists('perangkats');
        Schema::dropIfExists('screening_alergis');
        Schema::dropIfExists('kandungan_obats');
        Schema::dropIfExists('obats');
        Schema::dropIfExists('alergi_pelanggans');
        Schema::dropIfExists('pelanggans');
        Schema::dropIfExists('medicine_batches');
    }

};