@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header & Tombol Simulasi -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#0d2e26]">Monitoring Suhu & Kelembapan Gudang</h1>
            <p class="text-xs text-gray-500 mt-1">Pemantauan real-time kondisi lingkungan penyimpanan obat berdasarkan sensor IoT.</p>
        </div>
        
        
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Grid Perangkat & Sensor -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($perangkats as $p)
            @php
                $latestData = $p->dataSensors->first();
                $batangSuhuMin = $p->batasan->suhu_minimal ?? 15;
                $batangSuhuMax = $p->batasan->suhu_maksimal ?? 25;
                
                $isSuhuAman = $latestData ? ($latestData->suhu >= $batangSuhuMin && $latestData->suhu <= $batangSuhuMax) : true;
            @endphp

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <!-- Info Perangkat -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-gray-800 text-base">{{ $p->nama_perangkat }}</h3>
                            <p class="text-xs text-gray-400">Lokasi: {{ $p->lokasi }}</p>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-bold bg-emerald-100 text-emerald-800 rounded-lg">
                            {{ $p->status }}
                        </span>
                    </div>

                    @if($latestData)
                        <!-- Card Indikator Suhu & Kelembapan -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <!-- Suhu -->
                            <div class="p-4 rounded-xl border {{ $isSuhuAman ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200' }}">
                                <span class="text-xs text-gray-500 block mb-1">Suhu Saat Ini</span>
                                <span class="text-2xl font-bold {{ $isSuhuAman ? 'text-emerald-700' : 'text-red-700' }}">
                                    {{ $latestData->suhu }}°C
                                </span>
                                <span class="text-[10px] block mt-1 text-gray-500">Ideal: {{ $batangSuhuMin }}°C - {{ $batangSuhuMax }}°C</span>
                            </div>

                            <!-- Kelembapan -->
                            <div class="p-4 rounded-xl border bg-blue-50 border-blue-200">
                                <span class="text-xs text-gray-500 block mb-1">Kelembapan</span>
                                <span class="text-2xl font-bold text-blue-700">
                                    {{ $latestData->kelembapan }}%
                                </span>
                                <span class="text-[10px] block mt-1 text-gray-500">Ideal: 40% - 60%</span>
                            </div>
                        </div>

                        <!-- Peringatan Jika Suhu Tidak Normal -->
                        @if(!$isSuhuAman)
                            <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded-xl text-xs font-medium">
                                ⚠️ Peringatan: Suhu keluar dari batas aman penyimpanan obat! Segera cek AC/Pendingin ruangan.
                            </div>
                        @endif

                        <p class="text-[11px] text-gray-400 text-right">Terakhir diperbarui: {{ $latestData->waktu_catat }}</p>
                    @else
                        <div class="py-8 text-center text-gray-400 text-sm">
                            Belum ada data sensor tercatat. Klik tombol simulasi di atas.
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-3 p-12 text-center bg-white rounded-2xl border border-gray-100 text-gray-400 text-sm">
                Belum ada perangkat IoT yang terdaftar di sistem.
            </div>
        @endforelse
    </div>
</div>
@endsection