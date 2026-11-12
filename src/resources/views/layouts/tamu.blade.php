{{--
  Layout tamu (belum login) untuk I-01 Pendaftaran dan I-02 Login.
  Konvensi sama dengan layouts/app: @section('judul'), @section('konten'), @stack('gaya'), @stack('skrip').
  Tambahan: @section('ucapan') untuk gelembung ucapan Pak Aksa di panel kiri.
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
<div class="nk-tamu">
    {{-- Panel maskot (kiri). Disembunyikan di layar sempit. --}}
    <aside class="nk-tamu-maskot d-none d-lg-flex">
        <div>
            <a href="{{ url('/') }}" class="nk-tamu-merek">
                <span class="cincin-logo">
                    <img src="{{ asset('img/logo-ngaksara.png') }}" alt="" width="56" height="56">
                </span>
                <span>Ngaksara</span>
            </a>
            <div class="aksara nk-tamu-aksara" aria-hidden="true">ꦔꦏ꧀ꦱꦫ</div>
        </div>

        <div class="nk-tamu-tengah">
            <div class="nk-gelembung">@yield('ucapan', 'Sugeng rawuh! Aku Pak Aksa.')</div>
            <div class="nk-lingkaran-maskot">
                <img src="{{ asset('img/pak-aksa-melambai.svg') }}" alt="Pak Aksa melambai" width="260" height="286">
            </div>
        </div>

        <img src="{{ asset('img/motif-kawung.svg') }}" alt="" class="nk-tamu-motif" width="560" height="40">
    </aside>

    {{-- Panel formulir (kanan) --}}
    <main class="nk-tamu-formulir">
        {{-- Merek ringkas untuk layar sempit --}}
        <a href="{{ url('/') }}" class="nk-tamu-merek nk-tamu-merek-kecil d-lg-none">
            <img src="{{ asset('img/logo-ngaksara.png') }}" alt="" width="40" height="40">
            <span>Ngaksara</span>
        </a>
        @yield('konten')
    </main>
</div>
@stack('skrip')
</body>
</html>