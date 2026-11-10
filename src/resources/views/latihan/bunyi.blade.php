{{-- I-08 Latihan Bunyi (Marvel). Data: $materi, $token, $soal (tanpa kunci) --}}
@extends('layouts.app')
@section('judul', 'Latihan Bunyi · '.$materi->nama_materi)

@section('konten')
<h1 class="mb-1">Latihan Bunyi</h1>
<p class="teks-redup">{{ $materi->nama_materi }}. Pilih bunyi yang tepat untuk tiap aksara.</p>

<form method="POST" action="{{ route('latihan.soal.kirim', 'bunyi') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    @foreach ($soal as $n => $s)
        <fieldset class="kartu mb-3">
            <legend class="label-kecil">Soal {{ $n + 1 }}</legend>
            <div class="aksara text-center mb-3" style="font-size:3rem">{{ $s['target'] }}</div>
            <div class="d-flex gap-2 flex-wrap justify-content-center">
                @foreach ($s['opsi'] as $o)
                    <div>
                        <input type="radio" class="btn-check" name="jawaban[{{ $s['id'] }}]" id="{{ $s['id'] }}-{{ $o['id'] }}" value="{{ $o['id'] }}" autocomplete="off">
                        <label for="{{ $s['id'] }}-{{ $o['id'] }}" class="tombol tombol-sekunder">{{ $o['teks'] }}</label>
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
</style>
@endpush
