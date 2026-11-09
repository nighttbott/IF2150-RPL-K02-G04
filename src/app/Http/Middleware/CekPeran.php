<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pembatas akses berdasarkan peran (DPPL Algo-004 verifikasiHakAkses).
 *
 * Contoh pada route:  ->middleware(['auth', 'peran:pelajar'])  atau  'peran:pengajar,tim_materi'
 * Perbandingan tidak peka huruf besar/kecil. Disiapkan Natan sebagai fondasi; Ryuza (pemilik
 * autentikasi) boleh mengembangkannya.
 */
class CekPeran
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $akun = $request->user();

        // Belum masuk: arahkan ke halaman login bila sudah ada, selain itu tolak.
        if (! $akun) {
            return Route::has('login') ? redirect()->guest(route('login')) : abort(401, 'Silakan masuk terlebih dahulu.');
        }

        $diizinkan = array_map('strtolower', $peran);
        if (! in_array(strtolower((string) ($akun->peran ?? '')), $diizinkan, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
