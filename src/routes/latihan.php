<?php

use App\Http\Controllers\LatihanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'peran:pelajar'])->prefix('latihan')->name('latihan.')->controller(LatihanController::class)->group(function () {
    Route::post('jawaban', 'nilaiJawaban')->name('nilai');
    Route::get('{materi}', 'pilihMode')->whereNumber('materi')->name('mode');
    Route::get('{materi}/{mode}', 'soal')->whereNumber('materi')->name('soal');
});
