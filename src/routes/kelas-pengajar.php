<?php

use App\Http\Controllers\KelasPengajarController;
use Illuminate\Support\Facades\Route;

// UC-07 Mengelola Kelas (Pengajar). Pembatas peran memakai middleware 'peran' bawaan proyek;
// kepemilikan kelas tetap diperiksa di KelasPengajarController.
Route::middleware(['auth', 'peran:pengajar'])
    ->prefix('pengajar/kelas')
    ->name('pengajar.kelas.')
    ->controller(KelasPengajarController::class)
    ->where(['idKelas' => '[0-9]+', 'idPelajar' => '[0-9]+'])
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'buat')->name('buat');
        Route::post('/{idKelas}/ganti-kode', 'gantiKode')->name('ganti-kode');
        Route::get('/{idKelas}/anggota', 'anggota')->name('anggota');
        Route::post('/{idKelas}/anggota/{idPelajar}/keluarkan', 'keluarkan')->name('keluarkan');
        Route::post('/{idKelas}/scoreboard', 'scoreboard')->name('scoreboard');
        Route::get('/{idKelas}/tugas', 'formTugas')->name('tugas');
        Route::post('/{idKelas}/tugas', 'terbitkan')->name('terbitkan');
    });
