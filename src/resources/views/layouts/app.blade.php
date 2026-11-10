{{--
  Kerangka layout aplikasi utama (Ngaksara) dengan sidebar menu dan area konten.
  Mengembangkan layouts.base. Sesuai rancangan antarmuka BAB 6 DPPL2 (I-03, I-14, I-18, I-20).
  Konvensi yang dipakai: @section('judul'), @section('konten'), @stack('gaya'), @stack('skrip').
--}}
@extends('layouts.base')

@section('badan')
<div class="nk-kerangka">
    <aside class="nk-sidebar d-none d-md-flex">
        <a href="{{ route('beranda') }}" class="d-flex align-items-center gap-2 text-decoration-none">
            <img src="{{ asset('img/logo-ngaksara.png') }}" alt="Logo Ngaksara" width="40" height="40">
            <span class="merek">Ngaksara</span>
        </a>

        @php
            $peran = auth()->user()->peran ?? null;
        @endphp

        <nav class="nk-menu d-flex flex-column gap-1">
            <a href="{{ route('beranda') }}" @class(['aktif' => request()->routeIs('beranda')])>Beranda</a>

            @if ($peran === 'tim_materi')
                <a href="{{ route('materi.kelola') }}" @class(['aktif' => request()->routeIs('materi.kelola', 'materi.sunting')])>Kelola Materi</a>
                <a href="{{ route('materi.buat') }}" @class(['aktif' => request()->routeIs('materi.buat')])>Unggah Materi</a>
            @elseif ($peran === 'pengajar')
                @if (Route::has('kelas-pengajar.index'))
                    <a href="{{ route('kelas-pengajar.index') }}" @class(['aktif' => request()->routeIs('kelas-pengajar.*', 'kelola-kelas.*')])>Kelola Kelas</a>
                @endif
                @if (Route::has('pemantauan.kelas'))
                    <a href="{{ route('pemantauan.kelas') }}" @class(['aktif' => request()->routeIs('pemantauan.*')])>Pantau Progres</a>
                @endif
                @if (Route::has('feedback.index'))
                    <a href="{{ route('feedback.index') }}" @class(['aktif' => request()->routeIs('feedback.*')])>Feedback</a>
                @endif
            @else
                <a href="{{ route('materi.index') }}" @class(['aktif' => request()->routeIs('materi.index', 'materi.show')])>Materi</a>
                @if (Route::has('latihan.index'))
                    <a href="{{ route('latihan.index') }}" @class(['aktif' => request()->routeIs('latihan.*')])>Latihan</a>
                @endif
                @if (Route::has('progres.index'))
                    <a href="{{ route('progres.index') }}" @class(['aktif' => request()->routeIs('progres.*')])>Progres</a>
                @endif
                @if (Route::has('kelas.index'))
                    <a href="{{ route('kelas.index') }}" @class(['aktif' => request()->routeIs('kelas.*', 'kelas-pelajar.*')])>Kelas</a>
                @endif
                @if (Route::has('feedback.index'))
                    <a href="{{ route('feedback.index') }}" @class(['aktif' => request()->routeIs('feedback.*')])>Feedback</a>
                @endif
            @endif
        </nav>

        @auth
            @php
                $user = auth()->user();
                $inisial = method_exists($user, 'inisial') ? $user->inisial() : mb_strtoupper(mb_substr($user->nama_tampilan ?? 'U', 0, 1));
                $labelPeran = match ($user->peran ?? '') {
                    'pelajar' => 'Pelajar',
                    'pengajar' => 'Pengajar',
                    'tim_materi' => 'Tim Materi',
                    default => ucfirst($user->peran ?? ''),
                };
            @endphp
            <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <span class="avatar flex-shrink-0">{{ $inisial }}</span>
                    <div class="overflow-hidden">
                        <div class="fw-bold small text-truncate">{{ $user->nama_tampilan }}</div>
                        <div class="teks-redup" style="font-size: .75rem">{{ $labelPeran }}</div>
                    </div>
                </div>
                @if (Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="tombol tombol-sekunder tombol-kecil px-2 py-1" style="font-size: .75rem">Keluar</button>
                    </form>
                @elseif (Route::has('dev.keluar'))
                    <a href="{{ route('dev.keluar') }}" class="tombol tombol-sekunder tombol-kecil px-2 py-1" style="font-size: .75rem">Keluar</a>
                @endif
            </div>
        @endauth
    </aside>

    <main class="nk-konten">
        @yield('konten')
    </main>
</div>
@endsection
