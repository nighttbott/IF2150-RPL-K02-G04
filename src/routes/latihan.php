<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LatihanNonKanvasController;

/*
| UC-03 Latihan: mode non-kanvas (Marvel) - bunyi, merangkai, transliterasi.
| Mode menulis (kanvas) ditambahkan Naufal pada berkas ini juga.
*/
Route::middleware(['auth', 'peran:pelajar'])->prefix('latihan/soal')->group(function () {
    Route::get('/hasil', [LatihanNonKanvasController::class, 'hasil'])->name('latihan.soal.hasil');
    Route::get('/{mode}/{idMateri}', [LatihanNonKanvasController::class, 'mulai'])
        ->whereIn('mode', ['bunyi', 'merangkai', 'transliterasi'])->whereNumber('idMateri')->name('latihan.soal.mulai');
    Route::post('/{mode}', [LatihanNonKanvasController::class, 'kirim'])
        ->whereIn('mode', ['bunyi', 'merangkai', 'transliterasi'])->name('latihan.soal.kirim');
});
