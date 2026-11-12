<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pembatas akses berdasarkan peran (DPPL Algo-002 verifikasiHakAkses).
 *
 * Contoh pada route:  ->middleware(['auth', 'peran:pelajar'])  atau  'peran:pengajar,tim_materi'
 * Perbandingan tidak peka huruf besar/kecil. Disiapkan Natan sebagai fondasi; dikembangkan
 * Ryuza bersama pembatasan akses modul (Unit 5).
 */
class CekPeran
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $akun = $request->user();

        // Cadangan bila dipakai tanpa 'auth': permintaan JSON mendapat 401, selain itu ke halaman masuk.
        if (! $akun) {
            return $request->expectsJson()
                ? abort(401, 'Silakan masuk terlebih dahulu.')
                : redirect()->guest(route('masuk'));
        }

        $diizinkan = array_map('strtolower', $peran);
        if (! in_array(strtolower((string) ($akun->peran ?? '')), $diizinkan, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}