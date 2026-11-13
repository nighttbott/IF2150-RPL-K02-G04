<?php

use App\Http\Controllers\LatihanController;
use App\Http\Controllers\LatihanNonKanvasController;
use Illuminate\Support\Facades\Route;

// UC-03 Latihan (Naufal)
Route::middleware(['auth', 'peran:pelajar'])->prefix('latihan')->name('latihan.')->controller(LatihanController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('jawaban', 'nilaiJawaban')->name('nilai');
    Route::post('selesai', 'simpanHasil')->name('selesai');
    Route::get('hasil', 'hasil')->name('hasil');
    Route::get('{materi}', 'pilihMode')->whereNumber('materi')->name('mode');
    Route::get('{materi}/{mode}', 'soal')->whereNumber('materi')->name('soal');
    Route::post('{materi}/{mode}/lanjut', 'lanjut')->whereNumber('materi')->name('lanjut');
    Route::post('{materi}/{mode}/ulangi', 'ulangi')->whereNumber('materi')->name('ulangi');
});

// UC-03 Latihan: mode non-kanvas (Marvel) - bunyi, merangkai, transliterasi.
Route::middleware(['auth', 'peran:pelajar'])->prefix('latihan/soal')->group(function () {
    Route::get('/hasil', [LatihanNonKanvasController::class, 'hasil'])->name('latihan.soal.hasil');
    Route::get('/{mode}/{idMateri}', [LatihanNonKanvasController::class, 'mulai'])
        ->whereIn('mode', ['bunyi', 'merangkai', 'transliterasi'])->whereNumber('idMateri')->name('latihan.soal.mulai');
    Route::post('/{mode}', [LatihanNonKanvasController::class, 'kirim'])
        ->whereIn('mode', ['bunyi', 'merangkai', 'transliterasi'])->name('latihan.soal.kirim');
});