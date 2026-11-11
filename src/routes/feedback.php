<?php

use App\Http\Controllers\FeedbackController;
use Illuminate\Support\Facades\Route;

// Pelajar dan Pengajar; Tim Materi ditolak 403.
Route::middleware(['auth', 'peran:pelajar,pengajar'])->group(function () {
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback', [FeedbackController::class, 'kirim'])->name('feedback.kirim');
});