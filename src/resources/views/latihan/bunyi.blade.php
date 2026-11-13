{{-- I-08 Latihan Bunyi (Marvel). Data: $materi, $token, $soal (tanpa kunci), $audio (URL atau null) --}}
@extends('layouts.app')
@section('judul', 'Latihan Bunyi · '.$materi->nama_materi)

@section('konten')
<h1 class="mb-1">Latihan Bunyi</h1>
<p class="teks-redup">{{ $materi->nama_materi }}. Dengarkan pengucapannya, lalu pilih aksara yang sesuai.</p>

@unless ($audio)
    <div class="pesan pesan-info mb-3">Audio pengucapan belum tersedia untuk materi ini. Bacaan latin ditampilkan sebagai petunjuk.</div>
@endunless
<div id="galat-audio" class="pesan pesan-gagal mb-3 d-none" role="alert">Audio tidak dapat diputar, coba lagi.</div>

<form method="POST" action="{{ route('latihan.soal.kirim', 'bunyi') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    @foreach ($soal as $n => $s)
        <fieldset class="kartu mb-3">
            <legend class="label-kecil">Soal {{ $n + 1 }}</legend>
            <div class="text-center mb-3">
                <button type="button" class="tombol tombol-teal btn-speaker" @disabled(! $audio)
                        data-audio="{{ $audio }}" aria-label="Putar audio pengucapan soal {{ $n + 1 }}">🔊 Putar suara</button>
                <div class="teks-redup mt-2">Bacaan: <strong>{{ $s['bunyi'] }}</strong></div>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-center">
                @foreach ($s['opsi'] as $o)
                    <div>
                        <input type="radio" class="btn-check" name="jawaban[{{ $s['id'] }}]" id="{{ $s['id'] }}-{{ $o['id'] }}" value="{{ $o['id'] }}" autocomplete="off">
                        <label for="{{ $s['id'] }}-{{ $o['id'] }}" class="tombol tombol-sekunder aksara fs-2">{{ $o['teks'] }}</label>
                    </div>
                @endforeach
            </div>
        </fieldset>
    @endforeach
    <button class="tombol tombol-utama">Kirim jawaban</button>
</form>
@endsection

@push('gaya')
<style>
    .btn-check:checked + label { background: var(--nk-indigo); color: #fff; border-color: var(--nk-indigo); }
    .btn-check:focus-visible + label { outline: 3px solid var(--nk-emas); }
    .btn-speaker:disabled { opacity: .5; }
</style>
@endpush

@push('skrip')
<script>
    // putarAudio (C08): diputar di browser setelah tombol ditekan; gagal -> pesan galat (Skenario Alternatif 1).
    document.querySelectorAll('.btn-speaker').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            const galat = document.getElementById('galat-audio');
            galat.classList.add('d-none');
            const pemutar = new Audio(tombol.dataset.audio);
            pemutar.addEventListener('error', function () { galat.classList.remove('d-none'); });
            pemutar.play().catch(function () { galat.classList.remove('d-none'); });
        });
    });
</script>
@endpush
