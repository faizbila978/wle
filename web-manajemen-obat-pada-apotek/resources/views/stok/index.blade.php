@extends('layouts.app')

@section('content')
<!-- Background dengan gradasi halus (Bentodi gradient background) -->
<div class="p-8 bg-gradient-to-br from-[#f4f7f6] via-[#eaf0ed] to-[#f4f7f6] min-h-screen">

    <!-- Header & Tombol Aksi -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
        <div class="flex items-center gap-3 w-full md:w-auto">
            <!-- Accent pill dengan gradasi warna utama -->
            <h1 class="text-2xl font-bold bg-gradient-to-r from-[#0F766E] to-[#115E59] bg-clip-text text-transparent tracking-tight">
                Manajemen Stok & Mutasi Obat
            </h1>
        </div>
        
        <!-- Form Pencarian & Tombol Aksi -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <form action="{{ route('stok.index') }}" method="GET" class="flex-1 md:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama obat..." 
                       class="w-full px-4 py-2.5 bg-white/80 backdrop-blur-xs border border-gray-200/80 rounded-2xl text-sm text-gray-700 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#0F766E] shadow-xs transition">
            </form>

            <!-- Tombol Barang Keluar (Oranye lebih gelap agar kontras teks putih optimal) -->
            <button onclick="openModalKeluar()" class="bg-gradient-to-r from-amber-600 to-amber-700 text-white px-5 py-2.5 rounded-2xl shadow-md shadow-amber-700/20 hover:from-amber-700 hover:to-amber-800 hover:shadow-lg transition-all duration-200 font-medium text-sm whitespace-nowrap">
                - Barang Keluar
            </button>

            <!-- Tombol Tambah Obat Baru dengan Gradasi Teal Fresh (#0F766E -> #115E59) -->
            <button onclick="openModalTambah()" class="bg-gradient-to-br from-[#0F766E] to-[#115E59] text-white px-5 py-2.5 rounded-2xl shadow-md shadow-[#0F766E]/20 hover:from-[#0d665f] hover:to-[#0e4f4c] hover:shadow-lg transition-all duration-200 font-medium text-sm whitespace-nowrap">
                + Tambah Obat Baru
            </button>
        </div>
    </div>

    <!-- Alert Notifikasi Sukses -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/80 text-emerald-800 rounded-2xl text-sm shadow-xs flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    
    <!-- Alert Notifikasi Peringatan Expired / Error -->
    @if(session('error'))
        <div class="mb-6 p-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-300/80 text-amber-900 rounded-2xl text-sm font-medium shadow-xs flex items-center gap-3">
            <span class="text-lg">⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Tabel Daftar Obat & Batch -->
    <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden border border-gray-100/80">
        <table class="w-full text-left border-collapse">
            <thead>
                <!-- Header tabel dengan gradasi halus -->
                <tr class="bg-gradient-to-r from-gray-50 via-gray-100/50 to-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="p-5 font-semibold">Nama Obat</th>
                    <th class="p-5 font-semibold">Total Stok</th>
                    <th class="p-5 font-semibold">Detail Batch & Expired</th>

                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-base">
                @forelse($medicines as $med)
                <tr class="hover:bg-gradient-to-r hover:from-teal-50/20 hover:to-transparent transition-colors duration-150">
                    <!-- Kolom 1: Nama Obat & Jenis -->
                    <td class="p-5 font-bold text-gray-800">
                        {{ $med->nama_obat }} <br>
                        <span class="text-xs text-gray-500 font-normal">Jenis: {{ $med->jenis }}</span>
                    </td>
                    
                    <!-- Kolom 2: Total Stok -->
                    <td class="p-5 font-bold text-[#0F766E]">
                        <span class="bg-gradient-to-r from-[#0F766E] to-[#115E59] bg-clip-text text-transparent">
                            {{ $med->stok }}
                        </span>
                    </td>
                    
                    <!-- Kolom 3: Detail Batch & Expired -->
                    <td class="p-5">
                        <div class="flex flex-wrap gap-2">
                            @foreach($med->batches->where('current_stock', '>', 0) as $batch)
                                @php
                                    $isExpired = \Carbon\Carbon::parse($batch->expired_date)->isPast();
                                    $isNearExpired = \Carbon\Carbon::parse($batch->expired_date)->lte(now()->addDays(60));
                                @endphp
                                <div class="inline-flex items-center gap-2 text-xs px-3 py-1.5 rounded-xl border shadow-2xs
                                    {{ $isExpired ? 'border-red-200 bg-gradient-to-r from-red-50 to-rose-50 text-red-700' : ($isNearExpired ? 'border-amber-200 bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-800' : 'border-gray-200/80 bg-gradient-to-r from-gray-50 to-slate-50 text-gray-700') }}">
                                    <span>Batch: <b class="font-semibold">{{ $batch->batch_number }}</b></span>
                                    <span class="text-gray-400">|</span>
                                    <span>Sisa: <b>{{ $batch->current_stock }}</b></span>
                                    <span class="text-gray-400">|</span>
                                    <span>Exp: <b>{{ $batch->expired_date }}</b></span>
                                    
                                    @if($isExpired) 
                                        <span class="font-bold text-red-600 tracking-wider">[KADALUARSA]</span>
                                    @elseif($isNearExpired) 
                                        <span class="font-bold text-amber-600 tracking-wider">[Hampir Exp]</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </td>

                </tr>
                @empty
                <tr>

                    <td colspan="4" class="p-8 text-center text-gray-500">Belum ada data obat atau obat tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form Tambah Obat Baru -->
<div id="modalTambahObat" class="fixed inset-0 bg-black/40 backdrop-blur-xs hidden items-center justify-center z-50 p-4">
    <div class="bg-gradient-to-b from-white to-gray-50/50 rounded-3xl w-full max-w-md p-8 shadow-2xl relative border border-gray-100">
        <h3 class="text-xl font-bold bg-gradient-to-r from-[#0F766E] to-[#115E59] bg-clip-text text-transparent mb-6">Tambah Obat Baru</h3>
        
        <form action="{{ route('stok.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Nama Obat</label>
                    <input type="text" name="nama_obat" required placeholder="Contoh: Paracetamol" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-[#0F766E] outline-none transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Jenis / Kategori</label>
                    <input type="text" name="jenis" required placeholder="Contoh: Tablet / Botol" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-[#0F766E] outline-none transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Kandungan Zat Aktif</label>
                    <input type="text" name="kandungan" required placeholder="Contoh: Paracetamol" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-[#0F766E] outline-none transition text-sm">
                    <span class="text-[11px] text-gray-500 mt-1 block">Digunakan untuk pencocokan skrining alergi pasien.</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Jumlah Stok Awal</label>
                    <input type="number" name="initial_stock" min="1" required placeholder="Contoh: 50" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-[#0F766E] outline-none transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Tanggal Kadaluarsa (Expired)</label>
                    <input type="date" name="expired_date" required class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-gray-800 focus:ring-2 focus:ring-[#0F766E] outline-none transition text-sm text-gray-700">
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="closeModalTambah()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-2xl hover:bg-gray-200 transition font-medium text-sm">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-br from-[#0F766E] to-[#115E59] text-white rounded-2xl hover:from-[#0d665f] hover:to-[#0e4f4c] transition font-medium text-sm shadow-md shadow-[#0F766E]/10">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Form Barang Keluar -->
<div id="modalBarangKeluar" class="fixed inset-0 bg-black/40 backdrop-blur-xs hidden items-center justify-center z-50 p-4">
    <div class="bg-gradient-to-b from-white to-amber-50/20 rounded-3xl w-full max-w-md p-8 shadow-2xl relative border border-gray-100">
        <h3 class="text-xl font-bold bg-gradient-to-r from-amber-700 to-amber-800 bg-clip-text text-transparent mb-2">Form Barang Keluar</h3>
        <p class="text-xs text-gray-600 mb-6 leading-relaxed">Sistem akan otomatis mengeluarkan stok menggunakan metode FIFO berdasarkan tanggal expired terdekat.</p>
        
        <form action="{{ route('stok.reduce') }}" method="POST" onsubmit="return konfirmasiKeluar()">
    @csrf
    <div class="space-y-4">
        <!-- Pilih Obat -->
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Pilih Obat</label>
            <select name="id_obat" id="selectObatKeluar" required onchange="updateModeKeluar()" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-amber-600 outline-none transition text-sm text-gray-800">
                <option value="" data-stok="0">-- Pilih Obat --</option>
                @foreach($medicines as $m)
                    <option value="{{ $m->id_obat }}" data-stok="{{ $m->stok }}">{{ $m->nama_obat }} (Total Stok: {{ $m->stok }})</option>
                @endforeach
            </select>
        </div>

        <!-- Pilih Mode -->
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Mode Pengeluaran</label>
            <div class="grid grid-cols-2 gap-2">
                <label class="cursor-pointer">
                    <input type="radio" name="mode" value="manual" class="peer hidden" checked onchange="updateModeKeluar()">
                    <div class="text-center px-3 py-2.5 rounded-2xl border border-gray-200 bg-white text-sm text-gray-700 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 peer-checked:font-semibold transition">
                        Ketik Manual
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="mode" value="semua" class="peer hidden" onchange="updateModeKeluar()">
                    <div class="text-center px-3 py-2.5 rounded-2xl border border-gray-200 bg-white text-sm text-gray-700 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 peer-checked:font-semibold transition">
                        Hapus Semua Stok
                    </div>
                </label>
            </div>
        </div>

        <!-- Jumlah Keluar -->
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-1.5">Jumlah Keluar / Qty</label>
            <input type="number" name="quantity" id="inputQtyKeluar" min="1" required placeholder="Contoh: 5" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-amber-600 outline-none transition text-sm read-only:bg-gray-100 read-only:text-gray-500">
            <span id="infoHapusSemua" class="hidden text-[11px] text-red-600 mt-1 block">Seluruh stok obat ini akan dikeluarkan (FIFO).</span>
        </div>
    </div>

    <div class="flex justify-end gap-3 mt-8">
        <button type="button" onclick="closeModalKeluar()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-2xl hover:bg-gray-200 transition font-medium text-sm">Batal</button>
        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-amber-600 to-amber-700 text-white rounded-2xl hover:from-amber-700 hover:to-amber-800 transition font-medium text-sm shadow-md shadow-amber-700/10">Proses Pengurangan</button>
    </div>
</form><!-- Script Modal & Interaksi -->

<script>
    function openModalTambah() {
        document.getElementById('modalTambahObat').classList.remove('hidden');
        document.getElementById('modalTambahObat').classList.add('flex');
    }
    function closeModalTambah() {
        document.getElementById('modalTambahObat').classList.remove('flex');
        document.getElementById('modalTambahObat').classList.add('hidden');
    }

    function openModalKeluar() {
        document.getElementById('modalBarangKeluar').classList.remove('hidden');
        document.getElementById('modalBarangKeluar').classList.add('flex');
    }
    function closeModalKeluar() {
        document.getElementById('modalBarangKeluar').classList.remove('flex');
        document.getElementById('modalBarangKeluar').classList.add('hidden');
    }
</script>
@endsection