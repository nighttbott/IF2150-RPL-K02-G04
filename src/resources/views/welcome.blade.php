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
    @if (Route::has('dev.masuk'))
        {{-- Hanya muncul di lingkungan local. Hapus bersama routes/dev.php sebelum release. --}}
        <div class="kartu">
            <p class="label-kecil mb-2">Mode pengembangan</p>
            @auth
                <p class="mb-2">Masuk sebagai <strong>{{ auth()->user()->nama_tampilan }}</strong> ({{ auth()->user()->peran }}).</p>
            @endauth
            <div class="d-flex flex-wrap gap-2">
                <a class="tombol tombol-sekunder tombol-kecil" href="{{ route('dev.masuk', 'pelajar') }}">Masuk sebagai Pelajar</a>
                <a class="tombol tombol-sekunder tombol-kecil" href="{{ route('dev.masuk', 'pengajar') }}">Masuk sebagai Pengajar</a>
                <a class="tombol tombol-sekunder tombol-kecil" href="{{ route('dev.masuk', 'tim_materi') }}">Masuk sebagai Tim Materi</a>
                <a class="tombol tombol-sekunder tombol-kecil" href="{{ route('dev.keluar') }}">Keluar</a>
            </div>
            @auth
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @foreach ([['materi.index', 'Materi (I-04)'], ['materi.kelola', 'Kelola Materi (I-18)'], ['progres.index', 'Progres Pelajar (I-11)'], ['pemantauan.kelas', 'Pantau Progres (I-17)']] as [$namaRoute, $label])
                        @if (Route::has($namaRoute))
                            <a class="tombol tombol-utama tombol-kecil" href="{{ route($namaRoute) }}">{{ $label }}</a>
                        @endif
                    @endforeach
                </div>
            @endauth
        </div>
    @endif
</main>
</body>
</html>
