<?php

use App\Http\Controllers\OtentikasiController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [OtentikasiController::class, 'tampilMasuk'])->name('masuk');
    Route::post('/masuk', [OtentikasiController::class, 'prosesMasuk'])->name('masuk.proses');
    Route::get('/daftar', [OtentikasiController::class, 'tampilDaftar'])->name('daftar');
    Route::post('/daftar', [OtentikasiController::class, 'prosesDaftar'])->name('daftar.proses');
});

Route::post('/keluar', [OtentikasiController::class, 'keluar'])
    ->middleware('auth')
    ->name('keluar');