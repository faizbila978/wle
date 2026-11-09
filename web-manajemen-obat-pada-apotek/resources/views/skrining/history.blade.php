@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#0d2e26]">Riwayat Pasien & Alergi</h1>
            <p class="text-xs text-gray-500 mt-1">Daftar seluruh pasien dan riwayat alergi obat yang pernah di-skrining.</p>
        </div>
        <a href="{{ route('skrining.index') }}" class="px-4 py-2 bg-[#0d2e26] text-white rounded-xl text-sm font-medium hover:bg-[#133d33] transition shadow">
            ← Kembali ke Skrining
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-700 text-xs uppercase tracking-wider">
                    <th class="p-4 font-semibold">Nama Pasien / Pelanggan</th>
                    <th class="p-4 font-semibold">Riwayat Alergi Tercatat</th>
                    <th class="p-4 font-semibold">Waktu Input</th>
                    <th class="p-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm">
                @forelse($pelanggans as $p)
                <tr class="hover:bg-gray-50">
                    <td class="p-4 font-bold text-gray-800">{{ $p->nama_pelanggan }}</td>
                    <td class="p-4">
                        <div class="flex flex-wrap gap-1">
                            @forelse($p->alergis as $alg)
                                <span class="px-2.5 py-1 text-xs font-semibold bg-red-50 text-red-700 border border-red-200 rounded-lg">
                                    {{ $alg->nama_alergi }}
                                </span>
                            @empty
                                <span class="text-gray-400 italic">Tidak ada alergi</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="p-4 text-gray-500 text-xs">
                        {{ $p->created_at->format('d M Y, H:i') }}
                    </td>
                    <td class="p-4">
                        <a href="{{ route('skrining.index', ['nama_pelanggan' => $p->nama_pelanggan]) }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-medium hover:bg-emerald-700 transition">
                            Pilih & Skrining Ulang
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-6 text-center text-gray-500">Belum ada riwayat pasien tersimpan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection