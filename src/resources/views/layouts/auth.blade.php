{{--
  Layout untuk halaman autentikasi (I-01 Pendaftaran, I-02 Login).
--}}
@extends('layouts.base')

@section('badan')
<div class="nk-auth-kerangka">
    {{-- Banner Biru Sisi Kiri --}}
    <aside class="nk-auth-banner d-flex flex-column justify-content-between p-5">
        <div class="nk-auth-logo">
            <a href="{{ route('beranda') }}" class="d-inline-flex align-items-center gap-3 text-decoration-none">
                <img src="{{ asset('img/logo-ngaksara.png') }}" alt="Logo Ngaksara" width="48" height="48">
                <div>
                    <div class="merek text-white fs-3 mb-0" style="line-height:1">Ngaksara</div>
                    <span class="aksara" style="color:var(--nk-emas-muda);font-size:0.95rem">ꦔꦏ꧀ꦱꦫ</span>
                </div>
            </a>
        </div>

        <div class="nk-auth-hero text-center my-auto">
            <div class="nk-auth-balon mb-4 mx-auto">
                <p class="mb-0 fw-bold">@yield('sapaan', 'Sugeng rawuh! Sinau aksara Jawa lan Sunda bareng Pak Aksa.')</p>
            </div>
            <div class="nk-auth-karakter mx-auto">
                <img src="{{ asset('img/pak-aksa-melambai.svg') }}" alt="Pak Aksa" width="220" height="220" class="img-fluid">
            </div>
        </div>
    </aside>

    {{-- Area Form Sisi Kanan --}}
    <main class="nk-auth-konten d-flex align-items-center justify-content-center p-4 p-md-5">
        <div class="nk-auth-kotak w-100" style="max-width: 480px">
            <div class="kartu p-4 p-sm-5">
                @yield('konten')
            </div>
        </div>
    </main>
</div>
@endsection
