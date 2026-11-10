<?php

use App\Http\Controllers\LatihanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'peran:pelajar'])->prefix('latihan')->name('latihan.')->controller(LatihanController::class)->group(function () {
    Route::post('jawaban', 'nilaiJawaban')->name('nilai');
    Route::post('selesai', 'simpanHasil')->name('selesai');
    Route::get('hasil', 'hasil')->name('hasil');
    Route::get('{materi}', 'pilihMode')->whereNumber('materi')->name('mode');
    Route::get('{materi}/{mode}', 'soal')->whereNumber('materi')->name('soal');
    Route::post('{materi}/{mode}/lanjut', 'lanjut')->whereNumber('materi')->name('lanjut');
    Route::post('{materi}/{mode}/ulangi', 'ulangi')->whereNumber('materi')->name('ulangi');
});
