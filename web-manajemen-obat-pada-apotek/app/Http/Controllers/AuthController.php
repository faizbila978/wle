<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Tampilkan halaman Login
    public function showLogin() {
        return view('auth.login');
    }

    // Proses Login (Verifikasi username/password & masuk ke dashboard)
    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Password wajib diisi.'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard')->with('success', 'Berhasil masuk ke dashboard!');
        }

        // Extend: Tampilkan pesan kesalahan jika verifikasi gagal
        return back()->withErrors([
            'email' => 'Username/Email atau password salah.',
        ])->onlyInput('email');
    }

    // Tampilkan halaman Daftar Akun
    public function showRegister() {
        return view('auth.register');
    }

    // Proses Daftar Akun (Isi data registrasi & validasi)
    public function register(Request $request) {
        // Validasi data registrasi
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed',
        ], [
            'name.required' => 'Nama pengelola wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Simpan akun baru
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')->with('success', 'Akun berhasil didaftarkan! Silakan login.');
    }

    // Proses Logout
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}