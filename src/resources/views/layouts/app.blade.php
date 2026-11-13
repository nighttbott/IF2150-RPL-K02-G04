{{-- Layout setelah login: sidebar per peran + konten; @section('judul'), @section('konten'), opsional @section('samping'), @push('gaya'|'skrip'). --}}
@extends('layouts.base')

@section('badan')
<div class="nk-kerangka">
    <aside class="nk-sidebar d-none d-md-flex">
        @include('layouts.sidebar')
    </aside>

    {{-- Bilah atas + menu geser untuk layar sempit --}}
    <header class="nk-bilah-mobile d-flex d-md-none">
        <a href="{{ Route::has('beranda') ? route('beranda') : url('/') }}" class="nk-merek">
            <img src="{{ asset('img/logo-ngaksara.png') }}" alt="" width="32" height="32">
            <span class="merek">Ngaksara</span>
        </a>
        <button class="nk-tombol-menu" type="button" data-bs-toggle="offcanvas" data-bs-target="#nk-menu-mobile" aria-controls="nk-menu-mobile" aria-label="Buka menu">
            @include('layouts.ikon', ['nama' => 'menu'])
        </button>
    </header>
    <div class="offcanvas offcanvas-start nk-offcanvas d-md-none" tabindex="-1" id="nk-menu-mobile" aria-label="Menu utama">
        <div class="offcanvas-body nk-sidebar">
            @include('layouts.sidebar')
        </div>
    </div>

    <main class="nk-konten">
        @hasSection('samping')
            <div class="nk-dua-kolom">
                <div class="nk-kolom-utama">@yield('konten')</div>
                <aside class="nk-kolom-samping">@yield('samping')</aside>
            </div>
        @else
            @yield('konten')
        @endif
    </main>
</div>
@endsection
