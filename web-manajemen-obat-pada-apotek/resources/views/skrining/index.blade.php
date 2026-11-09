@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#0d2e26]">Skrining Alergi Pasien</h1>
            <p class="text-xs text-gray-500 mt-1">Cukup ketik atau pilih nama pasien dan riwayat alergi untuk menyaring keamanan obat dari database.</p>
        </div>
        <a href="{{ route('skrining.history') }}" class="px-4 py-2 bg-amber-600 text-white rounded-xl text-sm font-medium hover:bg-amber-700 transition shadow">
            📋 Lihat Semua Riwayat Pasien
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Form Input Instan & Riwayat Cepat -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-fit space-y-6">
            <h3 class="text-base font-bold text-[#0d2e26]">Input Data Skrining</h3>
            
            <form action="{{ route('skrining.index') }}" method="GET">
    <div class="space-y-4">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Nama Pelanggan / Pasien</label>
            <input type="text" name="nama_pelanggan" value="{{ $namaPelanggan ?? '' }}" required placeholder="Contoh: Budi Santoso" class="w-full px-3 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-[#0d2e26]">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Riwayat Alergi (Pisahkan koma)</label>
            <input type="text" name="nama_alergi" value="{{ $namaAlergiInput ?? '' }}" placeholder="Contoh: Paracetamol" class="w-full px-3 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-[#0d2e26]">
        </div>

        <!-- Tambahan Input Keluhan / Gejala / Indikasi -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Cari Keluhan / Indikasi Obat</label>
            <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Contoh: Demam, Batuk, Flu" class="w-full px-3 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-[#0d2e26]">
            <span class="text-[10px] text-gray-400 mt-1 block">Kosongkan jika ingin melihat semua obat.</span>
        </div>

        <button type="submit" class="w-full py-2.5 bg-[#0d2e26] text-white rounded-xl text-sm font-medium hover:bg-[#133d33] transition shadow">
            🔍 Cari Obat Alternatif Aman
        </button>
    </div>
</form>

            <!-- Bagian Daftar Pasien Tersimpan (Riwayat Cepat) -->
            <div class="border-t pt-4">
                <h4 class="text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Pasien Yang Sering Di-skrining:</h4>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    @php
                        $listPelanggan = \App\Models\Pelanggan::with('alergis')->latest()->take(5)->get();
                    @endphp
                    @forelse($listPelanggan as $p)
                        <div class="p-2 bg-gray-50 hover:bg-emerald-50 border border-gray-100 rounded-xl flex justify-between items-center text-xs">
                            <div>
                                <span class="font-bold text-gray-800 block">{{ $p->nama_pelanggan }}</span>
                                <span class="text-[10px] text-gray-500">
                                    Alergi: {{ $p->alergis->pluck('nama_alergi')->implode(', ') ?: 'Tidak ada' }}
                                </span>
                            </div>
                            <a href="{{ route('skrining.index', ['nama_pelanggan' => $p->nama_pelanggan, 'nama_alergi' => $p->alergis->pluck('nama_alergi')->implode(', ')]) }}" 
                               class="px-2.5 py-1 bg-[#0d2e26] text-white rounded-lg font-medium hover:bg-[#133d33] transition">
                                Pilih
                            </a>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">Belum ada data pasien tersimpan.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Hasil Pencocokan Keamanan Obat -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-[#0d2e26] mb-4">Hasil Pencocokan Keamanan Obat</h3>

            @if(isset($namaPelanggan) && !empty($namaPelanggan))
                <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                    <p class="text-sm font-semibold text-emerald-900">Pelanggan: {{ $namaPelanggan }}</p>
                    <p class="text-xs text-emerald-700 mt-1">
                        Alergi Terinput: 
                        <span class="font-bold text-red-600">
                            {{ !empty($allergyList) ? implode(', ', $allergyList) : 'Tidak ada riwayat alergi' }}
                        </span>
                    </p>
                </div>

                <div class="overflow-hidden border border-gray-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 text-xs uppercase tracking-wider">
                                <th class="p-3 font-semibold">Nama Obat</th>
                                <th class="p-3 font-semibold">Kandungan Zat Aktif</th>
                                <th class="p-3 font-semibold">Stok & Harga</th>
                                <th class="p-3 font-semibold">Status Skrining</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm">
                            @forelse($screeningResults as $res)
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-bold text-gray-800">{{ $res['obat']->nama_obat }} <br><span class="text-xs text-gray-400 font-normal">({{ $res['obat']->jenis }})</span></td>
                                <td class="p-3 text-gray-600">
                                    {{ $res['obat']->kandungans->pluck('nama_kandungan')->implode(', ') ?: '-' }}
                                </td>
                                <td class="p-3 text-gray-700">
                                    Stok: <b>{{ $res['obat']->stok }}</b><br>
                                    <span class="text-xs text-gray-500">Rp {{ number_format($res['obat']->harga, 0, ',', '.') }}</span>
                                </td>
                                <td class="p-3">
                                    @if($res['is_safe'])
                                        <span class="px-2.5 py-1 text-xs font-bold bg-emerald-100 text-emerald-800 rounded-lg">
                                            ✓ AMAN
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-bold bg-red-100 text-red-800 rounded-lg">
                                            ✕ TIDAK AMAN (ALERGI)
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-gray-500 text-sm">Belum ada data obat di dalam sistem.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center p-12 text-center border-2 border-dashed border-gray-200 rounded-2xl">
                    <p class="text-sm text-gray-400">Silakan ketik nama pelanggan & riwayat alerginya, atau pilih dari daftar pasien di sebelah kiri untuk melihat hasil skrining obat.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection