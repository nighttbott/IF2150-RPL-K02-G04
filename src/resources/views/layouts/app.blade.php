{{--
  Kerangka layout aplikasi utama (Ngaksara): sidebar menu per peran dan area konten.
  Mengembangkan layouts.base. Sesuai BAB 6 DPPL2 (I-03, I-14, I-15, I-18, I-19, I-20).
  Konvensi: @section('judul'), @section('konten'), @stack('gaya'), @stack('skrip').
  Item menu hanya tampil bila route-nya sudah terdaftar.
--}}
@extends('layouts.base')

@section('badan')
@php
    $user = auth()->user();
    $peran = $user?->peran;

    $labelPeran = match ($peran) {
        'pelajar' => 'Pelajar',
        'pengajar' => 'Pengajar',
        'tim_materi' => 'Tim Materi',
        default => '',
    };

    // Setiap item: [nama route, label, pola route untuk penanda aktif]
    $menuPeran = [
        'pelajar' => [
            ['materi.index',        'Materi',   ['materi.index', 'materi.show']],
            ['latihan.index',       'Latihan',  ['latihan.*']],
            ['progres.index',       'Progres',  ['progres.*']],
            ['kelas-pelajar.index', 'Kelas',    ['kelas-pelajar.*']],
            ['feedback.index',      'Feedback', ['feedback.*']],
        ],
        'pengajar' => [
            ['kelas-pengajar.index', 'Kelola Kelas',   ['kelas-pengajar.*']],
            ['pemantauan.kelas',     'Pantau Progres', ['pemantauan.*']],
            ['feedback.index',       'Feedback',       ['feedback.*']],
        ],
        'tim_materi' => [
            ['materi.kelola', 'Kelola Materi', ['materi.kelola', 'materi.sunting']],
            ['materi.buat',   'Unggah Materi', ['materi.buat']],
        ],
    ];

    $menu = array_merge(
        $peran === 'pelajar' ? [['beranda', 'Beranda', ['beranda']]] : [],
        $menuPeran[$peran] ?? [],
    );
@endphp

<div class="nk-kerangka">
    <aside class="nk-sidebar d-none d-md-flex">
        <a href="{{ Route::has('beranda') ? route('beranda') : url('/') }}"
           class="d-flex align-items-center gap-2 text-decoration-none">
            <img src="{{ asset('img/logo-ngaksara.png') }}" alt="Logo Ngaksara" width="40" height="40">
            <span class="merek">Ngaksara</span>
        </a>

        <nav class="nk-menu d-flex flex-column gap-1" aria-label="Menu utama">
            @foreach ($menu as [$nama, $label, $pola])
                @if (Route::has($nama))
                    <a href="{{ route($nama) }}"
                       @class(['aktif' => request()->routeIs(...$pola)])
                       @if (request()->routeIs(...$pola)) aria-current="page" @endif>
                        {{ $label }}
                    </a>
                @endif
            @endforeach
        </nav>

        @auth
            <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <span class="avatar flex-shrink-0">{{ $user->inisial() }}</span>
                    <div class="overflow-hidden">
                        <div class="fw-bold small text-truncate">{{ $user->nama_tampilan }}</div>
                        <div class="teks-redup" style="font-size: .75rem">{{ $labelPeran }}</div>
                    </div>
                </div>

                @if (Route::has('keluar'))
                    <form method="POST" action="{{ route('keluar') }}" class="m-0">
                        @csrf
                        <button type="submit"
                                class="tombol tombol-sekunder tombol-kecil px-2 py-1"
                                style="font-size: .75rem">Keluar</button>
                    </form>
                @endif
            </div>
        @endauth
    </aside>

    <main class="nk-konten">
        @yield('konten')
    </main>
</div>
@endsection