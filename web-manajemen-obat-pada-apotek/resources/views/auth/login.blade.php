@extends('layouts.auth') <!-- Menggunakan layout auth tanpa sidebar -->

@section('content')
<div class="flex items-center justify-center min-h-screen bg-[#0d2e26] p-6"> <!-- Menggunakan bg hijau tema apotek -->
    <div class="bg-white p-8 rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md">
        <!-- Isi form tetap sama -->
        <h2 class="text-2xl font-bold text-[#0d2e26] mb-2">Login Pengelola</h2>
        <p class="text-xs text-gray-500 mb-6">Silakan masuk menggunakan akun terdaftar.</p>

        @if(session('success'))
        <div class="mb-4 p-3 bg-emerald-50 text-emerald-800 text-xs rounded-xl border border-emerald-200">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Email / Username</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-3 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-[#0d2e26]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-[#0d2e26]">
            </div>
            <button type="submit" class="w-full py-2.5 bg-[#0d2e26] text-white rounded-xl text-sm font-medium hover:bg-[#133d33] transition shadow">
                Masuk ke Dashboard
            </button>
        </form>

        <p class="text-xs text-center text-gray-500 mt-6">
            Belum punya akun? <a href="{{ route('register') }}" class="text-emerald-700 font-bold hover:underline">Daftar Akun</a>
        </p>
    </div>
</div>
@endsection