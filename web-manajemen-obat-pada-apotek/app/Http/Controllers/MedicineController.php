<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MedicineController extends Controller {
    // Menampilkan daftar stok obat dan status batch/expired-nya
    public function index() {
        $medicines = Medicine::with(['batches' => function($query) {
            $query->orderBy('expired_date', 'asc');
        }])->get();

        return view('stok.index', compact('medicines'));
    }

// Menyimpan obat baru beserta kandungan obatnya
    public function store(Request $request) {
        $request->validate([
            'nama_obat' => 'required|string|max:255',
            'jenis' => 'required|string|max:255',
            'kandungan' => 'required|string|max:255', // Validasi input kandungan
            'initial_stock' => 'required|integer|min:1',
            'expired_date' => 'required|date',
        ]);

        $batchNumber = 'BATCH-' . strtoupper(Str::random(6));

        DB::transaction(function () use ($request, $batchNumber) {
            $medicine = Medicine::create([
                'nama_obat' => $request->nama_obat,
                'jenis' => $request->jenis,
                'harga' => 0,
                'stok' => $request->initial_stock,
            ]);

            // Simpan kandungan obat agar terbaca di skrining alergi
            \App\Models\KandunganObat::create([
                'id_obat' => $medicine->id_obat,
                'nama_kandungan' => $request->kandungan,
            ]);

            MedicineBatch::create([
                'id_obat' => $medicine->id_obat,
                'batch_number' => $batchNumber,
                'initial_stock' => $request->initial_stock,
                'current_stock' => $request->initial_stock,
                'expired_date' => $request->expired_date,
                'type' => 'in',
                'description' => 'Stok Awal',
            ]);
        });

        return redirect()->route('stok.index')->with('success', 'Obat baru beserta kandungan berhasil ditambahkan!');
    }

    // Mengurangi stok (Barang Keluar / Penjualan / Rusak dengan metode FIFO)
    // Mengurangi stok (Barang Keluar / Penjualan / Rusak dengan metode FIFO)
    // Mengurangi stok (Barang Keluar / Penjualan / Rusak dengan metode FIFO)
    public function reduceStock(Request $request) {
        $request->validate([
            'id_obat' => 'required|exists:obats,id_obat', // Sesuaikan name dari form modal (id_obat)
            'quantity' => 'required|integer|min:1',
        ]);

        $qtyToReduce = $request->quantity;
        $warningMessage = null;
        $isDeleted = false; // Flag untuk pesan notifikasi

        DB::transaction(function () use ($request, $qtyToReduce, &$warningMessage, &$isDeleted) {
            $medicine = Medicine::find($request->id_obat);

            // Cek ketersediaan stok menggunakan kolom 'stok'
            if ($medicine->stok < $qtyToReduce) {
                throw new \Exception('Stok tidak mencukupi!');
            }

            // Ambil batch berdasarkan sistem FIFO menggunakan foreign key 'id_obat'
            $batches = MedicineBatch::where('id_obat', $request->id_obat)
                                    ->where('current_stock', '>', 0)
                                    ->orderBy('expired_date', 'asc')
                                    ->get();

            foreach ($batches as $batch) {
                if ($qtyToReduce <= 0) break;

                // Cek apakah batch terdepan ini sudah expired atau hampir expired (< 60 hari)
                $isExpired = \Carbon\Carbon::parse($batch->expired_date)->isPast();
                $isNearExpired = \Carbon\Carbon::parse($batch->expired_date)->lte(now()->addDays(60));

                if ($isExpired) {
                    $warningMessage = "Perhatian: Obat yang dikeluarkan mencakup batch {$batch->batch_number} yang TELAH KADALUARSA!";
                } elseif ($isNearExpired && !$warningMessage) {
                    $warningMessage = "Perhatian: Obat yang dikeluarkan mencakup batch {$batch->batch_number} yang hampir kadaluarsa (Exp: {$batch->expired_date})!";
                }

                if ($batch->current_stock >= $qtyToReduce) {
                    $batch->current_stock -= $qtyToReduce;
                    $batch->save();
                    $qtyToReduce = 0;
                } else {
                    $qtyToReduce -= $batch->current_stock;
                    $batch->current_stock = 0;
                    $batch->save();
                }
            }

            // Kurangi total stok utama pada kolom 'stok'
            $medicine->stok -= $request->quantity;
            $medicine->save();

            // === LOGIKA OTOMATIS HAPUS OBAT JIKA STOK HABIS (0) ===
            if ($medicine->stok <= 0) {
                // Hapus relasi terkait terlebih dahulu (batch dan kandungan)
                $medicine->batches()->delete();
                if (method_exists($medicine, 'kandungans')) {
                    $medicine->kandungans()->delete();
                }
                
                // Hapus data obat induknya
                $medicine->delete();
                $isDeleted = true;
            }
        });

        // Pesan sukses disesuaikan jika obat otomatis terhapus
        $successMsg = $isDeleted 
            ? 'Barang keluar diproses, stok habis dan obat berhasil dihapus dari sistem secara otomatis!' 
            : 'Barang keluar berhasil diproses!';

        if ($warningMessage) {
            return redirect()->route('stok.index')->with('success', $successMsg)->with('error', $warningMessage);
        }

        return redirect()->route('stok.index')->with('success', $successMsg);
    }

}