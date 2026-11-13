<?php

use App\Http\Controllers\BerandaController;
use Illuminate\Support\Facades\Route;

// Halaman Utama (I-03). Pelajar melihat ringkasan; peran lain dialihkan ke menu pertamanya.
Route::get('/', [BerandaController::class, 'tampilkan'])->middleware('auth')->name('beranda');
