<?php

use App\Http\Controllers\KelasPengajarController;
use Illuminate\Support\Facades\Route;

/*
| UC-07 Mengelola Kelas (pemilik: Marvel)
|   I-14 Kelola Kelas, I-15 Terbitkan Tugas, I-16 Kelola Anggota
*/
Route::middleware(['auth', 'peran:pengajar'])->prefix('kelola/kelas')->group(function () {
    Route::get('/', [KelasPengajarController::class, 'index'])->name('kelas.pengajar.index');
    Route::post('/', [KelasPengajarController::class, 'simpan'])->name('kelas.pengajar.simpan');

    Route::prefix('{idKelas}')->where(['idKelas' => '[0-9]+', 'idPelajar' => '[0-9]+', 'idTugas' => '[0-9]+'])->group(function () {
        Route::get('/', [KelasPengajarController::class, 'tampilkan'])->name('kelas.pengajar.tampil');
        Route::post('/ganti-kode', [KelasPengajarController::class, 'gantiKode'])->name('kelas.pengajar.ganti-kode');
        Route::post('/scoreboard', [KelasPengajarController::class, 'ubahScoreboard'])->name('kelas.pengajar.scoreboard');

        Route::get('/anggota', [KelasPengajarController::class, 'anggota'])->name('kelas.pengajar.anggota');
        Route::delete('/anggota/{idPelajar}', [KelasPengajarController::class, 'keluarkanAnggota'])->name('kelas.pengajar.keluarkan');

        Route::get('/tugas/baru', [KelasPengajarController::class, 'formTugas'])->name('kelas.pengajar.tugas-baru');
        Route::post('/tugas', [KelasPengajarController::class, 'simpanTugas'])->name('kelas.pengajar.tugas-simpan');
        Route::post('/tugas/{idTugas}/terbitkan', [KelasPengajarController::class, 'terbitkanTugas'])->name('kelas.pengajar.tugas-terbitkan');
    });
});
