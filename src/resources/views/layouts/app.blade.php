{{--
  Kerangka layout aplikasi utama (Ngaksara): sidebar menu per peran dan area konten.
  Mengembangkan layouts.base. Sesuai BAB 6 DPPL2 (I-03, I-14, I-15, I-18, I-19, I-20).
  Konvensi: @section('judul'), @section('konten'), @stack('gaya'), @stack('skrip').
  Item menu hanya tampil bila route-nya sudah terdaftar.
--}}
@extends('layouts.base')

<style>
.nk-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 264px;
    height: 100vh;
    overflow-y: auto;
    background: white;
    border-right: 2px solid #eadfcb;
    padding: 30px 18px;
    display: flex;
    flex-direction: column;
    z-index: 1000;
}

.nk-konten {
    margin-left: 264px; 
    padding: 38px 44px;
    max-width: calc(100% - 264px);
}
</style>

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

    // Setiap item: [nama route, label, pola route untuk penanda aktif, ikon]
    $menuPeran = [
        'pelajar' => [
            ['materi.index',        'Materi',   ['materi.index', 'materi.show'], 'buku'],
            ['latihan.index',       'Latihan',  ['latihan.*'],                   'kuas'],
            ['progres.index',       'Progres',  ['progres.*'],                   'grafik'],
            ['kelas-pelajar.index', 'Kelas',    ['kelas-pelajar.*'],             'joglo'],
            ['feedback.index',      'Feedback', ['feedback.*'],                  'surat'],
        ],
        'pengajar' => [
            ['kelas-pengajar.index', 'Kelola Kelas',   ['kelas-pengajar.*'], 'joglo'],
            ['pemantauan.kelas',     'Pantau Progres', ['pemantauan.*'],     'grafik'],
            ['feedback.index',       'Feedback',       ['feedback.*'],       'surat'],
        ],
        'tim_materi' => [
            ['materi.kelola', 'Kelola Materi', ['materi.kelola', 'materi.sunting'], 'naskah'],
            ['materi.buat',   'Unggah Materi', ['materi.buat'],                     'unggah'],
        ],
    ];

    $menu = array_merge(
        $peran === 'pelajar' ? [['beranda', 'Beranda', ['beranda'], 'gunungan']] : [],
        $menuPeran[$peran] ?? [],
    );

    // Ikon menu (Figma: Ikon/<nama>), 24×24, mengikuti warna teks tautan.
    $ikonMenu = [
        'gunungan' => '<path d="M12 3c3 4 6 8 6 12v6H6v-6c0-4 3-8 6-12Z"/><path d="M12 8v13"/><path d="m9 15 3-2 3 2"/>',
        'buku'     => '<path d="M12 6c-2-1.5-5-2-8-2v14c3 0 6 .5 8 2 2-1.5 5-2 8-2V4c-3 0-6 .5-8 2Z"/><path d="M12 6v14"/>',
        'kuas'     => '<path d="m4 20 1-4L16 5l3 3L8 19Z"/><path d="m14 7 3 3"/>',
        'grafik'   => '<path d="M4 20h16"/><rect x="5" y="12" width="3" height="6"/><rect x="10.5" y="8" width="3" height="10"/><rect x="16" y="4" width="3" height="14"/>',
        'joglo'    => '<path d="M2 10 7 5h10l5 5"/><path d="M4 10h16"/><path d="M5 10v10h14V10"/><path d="M10 20v-5h4v5"/>',
        'surat'    => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'naskah'   => '<path d="M6 3h9l4 4v14H6Z"/><path d="M15 3v4h4"/><path d="M9 12h7M9 16h5"/>',
        'unggah'   => '<path d="M12 15V4"/><path d="m7 9 5-5 5 5"/><path d="M5 15v4h14v-4"/>',
    ];
@endphp

<div class="nk-kerangka">
    <aside class="nk-sidebar d-none d-md-flex">
        <a href="{{ Route::has('beranda') ? route('beranda') : url('/') }}"
           class="nk-sidebar-merek text-decoration-none">
            <img src="{{ asset('img/logo-ngaksara.png') }}" alt="Logo Ngaksara" width="40" height="40">
            <span class="d-flex flex-column">
                <span class="merek">Ngaksara</span>
                <span class="aksara nk-sidebar-aksara" aria-hidden="true">ꦔꦏ꧀ꦱꦫ</span>
            </span>
        </a>

        <nav class="nk-menu d-flex flex-column gap-1" aria-label="Menu utama">
            @foreach ($menu as [$nama, $label, $pola, $ikon])
                @if (Route::has($nama))
                    <a href="{{ route($nama) }}"
                       @class(['aktif' => request()->routeIs(...$pola)])
                       @if (request()->routeIs(...$pola)) aria-current="page" @endif>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikonMenu[$ikon] ?? '' !!}</svg>
                        {{ $label }}
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- Motif kawung; mt-auto mendorong motif dan profil ke dasar sidebar --}}
        <div class="nk-sidebar-motif mt-auto" aria-hidden="true"
             style="background-image: url('{{ asset('img/motif-kawung2.svg') }}')"></div>

        @auth
            <div class="nk-profil">
                <span class="avatar flex-shrink-0">{{ $user->inisial() }}</span>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="nk-profil-nama text-truncate">{{ $user->nama_tampilan }}</div>
                    <div class="nk-profil-peran">{{ $labelPeran }}</div>
                </div>

                @if (Route::has('keluar'))
                    <form method="POST" action="{{ route('keluar') }}" class="m-0">
                        @csrf
                        <button type="submit" class="nk-tombol-keluar" aria-label="Keluar" title="Keluar">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M10 4H5v16h5"/><path d="M14 8l4 4-4 4"/><path d="M18 12H9"/>
                            </svg>
                        </button>
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