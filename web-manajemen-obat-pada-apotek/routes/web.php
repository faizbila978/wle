<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\AllergyScreeningController;
use App\Http\Controllers\TemperatureMonitoringController;
use App\Http\Controllers\AuthController;

// Auth (hanya untuk tamu / belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Semua halaman yang wajib login
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Stok & Mutasi (form pakai modal, jadi show/create/edit dikecualikan)
    Route::resource('stok', MedicineController::class)
        ->except(['show', 'create', 'edit'])
        ->parameters(['stok' => 'id_obat']);
    Route::post('/stok/reduce', [MedicineController::class, 'reduceStock'])->name('stok.reduce');

    // Skrining Alergi
    Route::get('/skrining', [AllergyScreeningController::class, 'index'])->name('skrining.index');
    Route::get('/skrining/history', [AllergyScreeningController::class, 'history'])->name('skrining.history');

    // Monitoring Suhu
    Route::get('/monitoring', [TemperatureMonitoringController::class, 'index'])->name('monitoring.index');
    Route::post('/monitoring/simulate', [TemperatureMonitoringController::class, 'simulateSensor'])->name('monitoring.simulate');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});