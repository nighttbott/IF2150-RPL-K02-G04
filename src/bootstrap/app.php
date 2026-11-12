<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Autentikasi: terbuka untuk tamu; route di dalamnya mengatur guest/auth sendiri.
            Route::middleware('web')->group(base_path('routes/auth.php'));

            // Peta peran per modul (Algo-002). Modul yang dipakai lebih dari satu peran
            // memasang peran: sendiri per grup di dalam berkas route-nya (lihat Langkah 5).
            $modul = [
                'latihan'        => ['auth', 'peran:pelajar'],
                'kelas-pelajar'  => ['auth', 'peran:pelajar'],
                'kelas-pengajar' => ['auth', 'peran:pengajar'],
                'feedback'       => ['auth', 'peran:pelajar,pengajar'],
                'materi'         => ['auth'],   // pelajar dan tim_materi: lihat routes/materi.php
                'progres'        => ['auth'],   // pelajar dan pengajar bila pemantauan di berkas ini
            ];

            foreach ($modul as $nama => $middleware) {
                Route::middleware(['web', ...$middleware])->group(base_path("routes/{$nama}.php"));
            }

            // Route masuk sementara untuk pengembangan (hanya di lingkungan local)
            if (app()->environment('local') && file_exists(base_path('routes/dev.php'))) {
                Route::middleware('web')->group(base_path('routes/dev.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias middleware pembatas peran: ->middleware(['auth', 'peran:pelajar'])
        $middleware->alias([
            'peran' => \App\Http\Middleware\CekPeran::class,
        ]);

        // Tamu yang membuka halaman berlogin diarahkan ke route 'login' (modul autentikasi).
        // Selama route itu belum ada, tamu diarahkan ke halaman awal.
        $middleware->redirectGuestsTo(fn () => route('masuk'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
