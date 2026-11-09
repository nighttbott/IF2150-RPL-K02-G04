<?php

use App\Models\AkunPengguna;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
| Route SEMENTARA untuk pengembangan (disiapkan Natan).
| Berguna untuk mencoba halaman sebelum fitur login (Ryuza) selesai.
| Dimuat hanya bila APP_ENV=local (lihat bootstrap/app.php) dan APP_DEBUG=true.
| HAPUS berkas ini sebelum release v9.0.
*/

if (config('app.debug')) {
    // Masuk sebagai akun uji pertama dengan peran tertentu, contoh: /dev/masuk/pelajar
    Route::get('/dev/masuk/{peran}', function (string $peran) {
        $akun = AkunPengguna::query()->where('peran', strtolower($peran))->orderBy('id_akun')->firstOrFail();
        Auth::login($akun);
        request()->session()->regenerate();

        return redirect()->route('beranda');
    })->whereIn('peran', ['pelajar', 'pengajar', 'tim_materi'])->name('dev.masuk');

    Route::get('/dev/keluar', function () {
        Auth::logout();
        request()->session()->invalidate();

        return redirect()->route('beranda');
    })->name('dev.keluar');
}
