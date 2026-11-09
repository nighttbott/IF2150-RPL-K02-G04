<?php

use App\Http\Controllers\MateriController;
use Illuminate\Support\Facades\Route;

// Pelajar: I-04 Daftar Materi, I-05 Detail Materi
Route::middleware('auth')->group(function () {
    Route::get('/materi', [MateriController::class, 'index'])->name('materi.index');
    Route::get('/materi/{materi}', [MateriController::class, 'show'])->whereNumber('materi')->name('materi.show');
    Route::get('/materi/{materi}/audio', [MateriController::class, 'audio'])->whereNumber('materi')->name('materi.audio');
});

// Tim Materi: I-18 Kelola Materi, I-19 Unggah Materi (hak akses diperiksa di MateriController)
Route::middleware('auth')->prefix('kelola/materi')->group(function () {
    Route::get('/', [MateriController::class, 'kelola'])->name('materi.kelola');
    Route::get('/baru', [MateriController::class, 'buat'])->name('materi.buat');
    Route::post('/', [MateriController::class, 'simpan'])->name('materi.simpan');
    Route::get('/{materi}/sunting', [MateriController::class, 'sunting'])->whereNumber('materi')->name('materi.sunting');
    Route::put('/{materi}', [MateriController::class, 'perbarui'])->whereNumber('materi')->name('materi.perbarui');
    Route::post('/{materi}/terbitkan', [MateriController::class, 'terbitkan'])->whereNumber('materi')->name('materi.terbitkan');
    Route::delete('/{materi}', [MateriController::class, 'arsipkan'])->whereNumber('materi')->name('materi.arsipkan');
});
