{{--
  SEMENTARA (Diandra): kerangka layout agar halaman materi bisa dibuka sendiri. Memakai kelas dari
  resources/css/app.css (nk-kerangka, nk-sidebar, nk-menu, nk-konten). Ganti/hapus bila layout bersama
  Ryuza sudah ada. Konvensi yang dipakai halaman materi: @section('judul'), @section('konten'),
  @stack('gaya'), @stack('skrip').
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', config('app.name', 'Ngaksara'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('gaya')
</head>
<body>
<div class="nk-kerangka">
    <aside class="nk-sidebar d-none d-md-flex">
        <a href="{{ route('beranda') }}" class="d-flex align-items-center gap-2 text-decoration-none">
            <img src="{{ asset('img/logo-ngaksara.png') }}" alt="" width="40" height="40">
            <span class="merek">Ngaksara</span>
        </a>
        <nav class="nk-menu d-flex flex-column gap-1">
            <a href="{{ route('beranda') }}" @class(['aktif' => request()->routeIs('beranda')])>Beranda</a>
            @if ((auth()->user()->peran ?? null) === 'tim_materi')
                <a href="{{ route('materi.kelola') }}" @class(['aktif' => request()->routeIs('materi.kelola', 'materi.sunting')])>Kelola Materi</a>
                <a href="{{ route('materi.buat') }}" @class(['aktif' => request()->routeIs('materi.buat')])>Unggah Materi</a>
            @else
                <a href="{{ route('materi.index') }}" @class(['aktif' => request()->routeIs('materi.index', 'materi.show')])>Materi</a>
            @endif
            @if ((auth()->user()->peran ?? null) === 'pelajar' && Route::has('progres.index'))
                <a href="{{ route('progres.index') }}" @class(['aktif' => request()->routeIs('progres.*')])>Progres</a>
            @endif
            @if ((auth()->user()->peran ?? null) === 'pengajar' && Route::has('pemantauan.kelas'))
                <a href="{{ route('pemantauan.kelas') }}" @class(['aktif' => request()->routeIs('pemantauan.*')])>Pantau Progres</a>
            @endif
        </nav>
    </aside>
    <main class="nk-konten">
        @yield('konten')
    </main>
</div>
@stack('skrip')
</body>
</html>
