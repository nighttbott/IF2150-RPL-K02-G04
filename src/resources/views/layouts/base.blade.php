{{-- Layout dasar utama (root) seluruh aplikasi Ngaksara. Semua layout turunan (layouts.app, layouts.auth, layouts.latihan) mengekstensi berkas ini. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('judul', config('app.name', 'Ngaksara'))</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo-ngaksara.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('gaya')
</head>
<body class="@yield('kelas_body', '')">
    @hasSection('badan')
        @yield('badan')
    @else
        @yield('konten')
    @endif

    @stack('skrip')
</body>
</html>
