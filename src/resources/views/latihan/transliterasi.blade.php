{{-- Latihan Transliterasi (Marvel, mode tambahan; catat di B.1). Data: $materi, $token, $soal --}}
@extends('layouts.app')
@section('judul', 'Latihan Transliterasi · '.$materi->nama_materi)

@section('konten')
<h1 class="mb-1">Latihan Transliterasi</h1>
<p class="teks-redup">{{ $materi->nama_materi }}. Tulis bacaan latin dari aksara berikut.</p>

<form method="POST" action="{{ route('latihan.soal.kirim', 'transliterasi') }}" autocomplete="off">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    @foreach ($soal as $n => $s)
        <div class="kartu mb-3">
            <label for="{{ $s['id'] }}" class="label-kecil">Soal {{ $n + 1 }}</label>
            <div class="aksara text-center mb-2" style="font-size:3rem">{{ $s['target'] }}</div>
            <input id="{{ $s['id'] }}" name="jawaban[{{ $s['id'] }}]" maxlength="200" class="form-control text-center" style="max-width:320px;margin:auto" placeholder="Tulis dengan huruf latin">
        </div>
    @endforeach
    <button class="tombol tombol-utama">Kirim jawaban</button>
</form>
@endsection
