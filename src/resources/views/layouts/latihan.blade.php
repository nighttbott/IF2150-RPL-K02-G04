{{--
  Layout untuk sesi latihan mandiri aksara (I-06 s.d. I-10: Menulis, Bunyi, Merangkai, Hasil Latihan).
  Mengembangkan layouts.base. Tampilan layar penuh tanpa sidebar untuk fokus belajar.
--}}
@extends('layouts.base')

@section('badan')
<div class="nk-latihan-kerangka d-flex flex-column min-vh-100">
    {{-- Bilah Atas: Tombol Tutup, Indikator Progres, Streak --}}
    <header class="nk-latihan-topbar px-4 py-3 d-flex align-items-center justify-content-between gap-3">
        <a href="@yield('url_tutup', route('materi.index'))" class="nk-latihan-tombol-tutup text-decoration-none fs-4 fw-bold" aria-label="Tutup Latihan">
            &times;
        </a>

        <div class="nk-latihan-progres flex-grow-1" style="max-width: 640px;">
            @yield('bilah_progres')
        </div>

        <div class="nk-latihan-status d-flex align-items-center gap-2">
            @yield('status_latihan')
        </div>
    </header>

    {{-- Area Utama Latihan --}}
    <main class="nk-latihan-konten flex-grow-1 d-flex flex-column align-items-center justify-content-center p-3 p-md-4">
        <div class="w-100" style="max-width: 900px;">
            @yield('konten')
        </div>
    </main>

    {{-- Bilah Aksi Bawah (opsional: Periksa, Ulangi, Lanjut) --}}
    @hasSection('bilah_bawah')
        <footer class="nk-latihan-bawah p-3">
            <div class="mx-auto d-flex align-items-center justify-content-between flex-wrap gap-3" style="max-width: 900px;">
                @yield('bilah_bawah')
            </div>
        </footer>
    @endif
</div>
@endsection
