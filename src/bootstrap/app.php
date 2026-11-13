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
            // Autentikasi terbuka untuk tamu; guest/auth diatur di dalam routes/auth.php.
            Route::middleware('web')->group(base_path('routes/auth.php'));

            // Peta peran per modul (Algo-002); modul lintas peran memasang peran:... di berkas route-nya sendiri.
            $modul = [
                'latihan'        => ['auth', 'peran:pelajar'],
                'kelas-pelajar'  => ['auth', 'peran:pelajar'],
                'kelas-pengajar' => ['auth', 'peran:pengajar'],
                'feedback'       => ['auth', 'peran:pelajar,pengajar'],
                'materi'         => ['auth'],
                'progres'        => ['auth'],
            ];

            foreach ($modul as $nama => $middleware) {
                Route::middleware(['web', ...$middleware])->group(base_path("routes/{$nama}.php"));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias pembatas peran, contoh: ->middleware(['auth', 'peran:pelajar']).
        $middleware->alias([
            'peran' => \App\Http\Middleware\CekPeran::class,
        ]);

        // Tamu yang membuka halaman berlogin diarahkan ke halaman Masuk (I-02).
        $middleware->redirectGuestsTo(fn () => route('masuk'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
