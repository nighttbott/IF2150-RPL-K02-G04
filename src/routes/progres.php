<?php

use App\Http\Controllers\PemantauanKelasController;
use App\Http\Controllers\ProgresController;
use Illuminate\Support\Facades\Route;

/*
| Route modul progres dan pemantauan (pemilik: Natan)
|   UC-04  Meninjau Progres dan Motivasi        -> I-11
|   UC-08  Memantau dan Menindaklanjuti Progres -> I-17
*/

Route::middleware(['auth', 'peran:pelajar'])->group(function () {
    // I-11 Progres Pelajar. Parameter opsional: ?periode=7|30|90|semua, ?materi=ID, ?kelas=ID
    Route::get('/progres', [ProgresController::class, 'tampilkan'])->name('progres.index');
});

Route::middleware(['auth', 'peran:pengajar'])->group(function () {
    // Daftar kelas milik Pengajar untuk dipilih sebelum memantau
    Route::get('/pemantauan', [PemantauanKelasController::class, 'daftarKelas'])->name('pemantauan.kelas');
    // I-17 Pantau Progres Anggota. Parameter opsional: ?periode=..., ?pelajar=ID
    Route::get('/pemantauan/{idKelas}', [PemantauanKelasController::class, 'tampilkan'])
        ->whereNumber('idKelas')->name('pemantauan.index');
    Route::post('/pemantauan/{idKelas}/umpan-balik', [PemantauanKelasController::class, 'kirimUmpanBalik'])
        ->whereNumber('idKelas')->name('pemantauan.umpan-balik');
});
