@extends('layouts.app')
@section('judul', 'Latihan belum tersedia')

@section('konten')
<h1 class="mb-3">Latihan belum tersedia</h1>
<div class="pesan pesan-info">Materi <strong>{{ $materi->nama_materi }}</strong> belum punya bahan untuk mode {{ $mode->value }}. Coba mode lain atau materi lain.</div>
<a href="{{ route('materi.show', $materi->id_materi) }}" class="tombol tombol-sekunder">Kembali ke materi</a>
@endsection
