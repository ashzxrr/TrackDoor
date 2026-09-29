<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminKaryawanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'login'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'authenticate'])->name('authenticate');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('auth.admin')->name('logout');

    Route::middleware('auth.admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/export', [AdminDashboardController::class, 'export'])->name('dashboard.export');
        Route::resource('karyawan', AdminKaryawanController::class)->except('show');
        Route::post('/karyawan/{karyawan}/photo', [AdminKaryawanController::class, 'uploadPhoto'])->name('karyawan.photo');
    });
});

Route::middleware('auth.security')->group(function () {
    Route::get('/scan', [ScanController::class, 'index'])->name('scan');
    Route::post('/scan/lookup', [ScanController::class, 'lookup'])->name('scan.lookup');
    Route::post('/scan/store', [ScanController::class, 'store'])->name('scan.store');
});
