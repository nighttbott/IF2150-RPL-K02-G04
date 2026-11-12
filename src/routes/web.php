<?php

use Illuminate\Support\Facades\Route;

// Halaman awal (sementara sampai Halaman Utama I-03 selesai)
Route::view('/', 'welcome')->middleware('auth')->name('beranda');