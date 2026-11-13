<?php

use App\Http\Controllers\PemantauanKelasController;
use App\Http\Controllers\ProgresController;
use Illuminate\Support\Facades\Route;

// Route modul progres (UC-04, I-11) dan pemantauan (UC-08, I-17), pemilik: Natan.

Route::middleware('peran:pelajar')->group(function () {
    // I-11 Progres Pelajar. Parameter opsional: ?periode=7|30|90|semua, ?materi=ID, ?kelas=ID
    Route::get('/progres', [ProgresController::class, 'tampilkan'])->name('progres.index');
});

Route::middleware('peran:pengajar')->group(function () {
    // Daftar kelas milik Pengajar untuk dipilih sebelum memantau
    Route::get('/pemantauan', [PemantauanKelasController::class, 'daftarKelas'])->name('pemantauan.kelas');
    // I-17 Pantau Progres Anggota. Parameter opsional: ?periode=..., ?pelajar=ID
    Route::get('/pemantauan/{idKelas}', [PemantauanKelasController::class, 'tampilkan'])
        ->whereNumber('idKelas')->name('pemantauan.index');
    Route::post('/pemantauan/{idKelas}/umpan-balik', [PemantauanKelasController::class, 'kirimUmpanBalik'])
        ->whereNumber('idKelas')->name('pemantauan.umpan-balik');
});
