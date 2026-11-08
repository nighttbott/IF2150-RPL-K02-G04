{{--
    Halaman awal sementara Ngaksara (pengganti halaman bawaan Laravel yang memakai Tailwind).
    Akan diganti Halaman Utama (I-03) oleh pemilik modul autentikasi.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Ngaksara') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<main class="container py-5" style="max-width: 760px">
    <div class="d-flex align-items-center gap-3 mb-4">
        <img src="{{ asset('img/logo-ngaksara.png') }}" alt="Logo Ngaksara" width="64" height="64">
        <div>
            <h1 class="mb-0">Ngaksara</h1>
            <span class="aksara" style="color: var(--nk-sogan)">ꦔꦏ꧀ꦱꦫ</span>
        </div>
    </div>
    <div class="kartu kartu-emas d-flex gap-3 align-items-center mb-4">
        <img src="{{ asset('img/pak-aksa-melambai.svg') }}" alt="Pak Aksa" width="96">
        <p class="mb-0">Sugeng rawuh! Aplikasi pembelajaran aksara Jawa dan Sunda sedang dikembangkan.
            Halaman ini sementara sampai Halaman Utama (I-03) selesai.</p>
    </div>
</main>
</body>
</html>
