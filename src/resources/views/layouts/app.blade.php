{{--
  Layout utama setelah login (I-03 s.d. I-06, I-10 s.d. I-20): sidebar + konten (+ kolom samping opsional).

    @extends('layouts.app')
    @section('judul', '... · Ngaksara')   judul tab browser
    @section('konten') ... @endsection     isi halaman (judul h1 dan pesan sukses/galat diatur halaman sendiri)
    @section('samping') ... @endsection    OPSIONAL: kolom kanan (motivasi, kartu Pak Aksa)
    @push('gaya') / @push('skrip')         CSS/JS khusus halaman
--}}
@extends('layouts.base')

@section('body')
<div class="nk-kerangka">
    @include('layouts.sidebar')

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