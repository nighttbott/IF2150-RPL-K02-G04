<?php

use App\Http\Controllers\OtentikasiController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [OtentikasiController::class, 'tampilMasuk'])->name('masuk');
    Route::post('/masuk', [OtentikasiController::class, 'prosesMasuk'])->name('masuk.proses');
});

Route::post('/keluar', [OtentikasiController::class, 'keluar'])
    ->middleware('auth')
    ->name('keluar');