<?php

use App\Http\Controllers\KelasPelajarController;
use Illuminate\Support\Facades\Route;

Route::prefix('pelajar/kelas')->name('kelas-pelajar.')->controller(KelasPelajarController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/gabung', 'gabung')->middleware('throttle:20,1')->name('gabung');
    Route::get('/{kelas}', 'index')->whereNumber('kelas')->name('kelas');
    Route::get('/{kelas}/tugas/{tugas}', 'detail')->whereNumber(['kelas', 'tugas'])->name('detail');
    Route::post('/{kelas}/tugas/{tugas}/kumpulkan', 'kumpulkan')->whereNumber(['kelas', 'tugas'])->name('kumpulkan');
    Route::get('/{kelas}/tugas/{tugas}/komponen/{komponen}', 'kerjakan')->whereNumber(['kelas', 'tugas', 'komponen'])->name('kerjakan');
});
