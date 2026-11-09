@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#f4f7f6] min-h-screen">
    <!-- Bentodi Style Card Wrapper -->
    <div class="bg-white p-8 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 max-w-3xl transition-all duration-300">
        <!-- Header Section -->
        <div class="flex items-center gap-3 mb-4">
            <div class="w-3 h-8 bg-[#0d2e26] rounded-full"></div>
            <h1 class="text-2xl font-bold text-[#0d2e26] tracking-tight">Selamat Datang di Sistem Apotek Farmasi 57</h1>
        </div>

        <!-- Description -->
        <p class="text-gray-500 leading-relaxed mb-8 text-base">
            Panel utama untuk mengelola inventaris obat, memantau tanggal kadaluarsa (<span class="font-medium text-gray-700">expired date</span>), 
            melakukan skrining alergi pasien, serta memantau suhu ruangan gudang secara real-time.
        </p>

        <!-- Action Button -->
        <div class="flex gap-4 items-center">
            <a href="{{ route('stok.index') }}" class="inline-flex items-center gap-2 bg-[#0d2e26] text-white px-6 py-3.5 rounded-2xl font-medium hover:bg-[#133d33] shadow-md shadow-[#0d2e26]/10 hover:shadow-lg hover:shadow-[#0d2e26]/20 transition-all duration-200">
                <span>Kelola Stok & Mutasi Obat</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="d-round" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</div>
@endsection