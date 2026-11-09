{{-- I-04 Daftar Materi (C13 HalamanMateri). Data: $daftar (list MetadataMateri), $jenis, $tingkat --}}
@extends('layouts.app')
@section('judul', 'Materi Aksara · Ngaksara')

@section('konten')
@php
    $jenisAktif = $jenis?->value ?? 'jawa';
    $tingkatAktif = $tingkat ?? 1;
    $labelStatus = ['selesai' => 'Selesai', 'sedang' => 'Sedang', 'belum' => 'Belum'];
    $kelasStatus = ['selesai' => 'label-berhasil', 'sedang' => 'label-netral', 'belum' => 'label-netral'];
@endphp
<div class="mb-4">
    <h1 class="mb-1">Materi Aksara</h1>
    <p class="teks-redup mb-0">Pilih jenis aksara dan tingkat kesulitan, lalu mulai belajar.</p>
</div>

<div class="d-flex flex-wrap gap-4 mb-4">
    <div>{{-- I-04-01 --}}
        <div class="label-kecil mb-2">Jenis aksara</div>
        <div class="d-flex gap-2">
            @foreach (['jawa' => 'Jawa', 'sunda' => 'Sunda'] as $nilai => $label)
                <a href="{{ route('materi.index', ['jenis' => $nilai, 'tingkat' => $tingkatAktif]) }}"
                   class="tombol tombol-kecil {{ $jenisAktif === $nilai ? 'tombol-utama' : 'tombol-sekunder' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>
    <div>{{-- I-04-02 --}}
        <div class="label-kecil mb-2">Tingkat kesulitan</div>
        <div class="d-flex gap-2">
            @foreach ([1, 2, 3] as $t)
                <a href="{{ route('materi.index', ['jenis' => $jenisAktif, 'tingkat' => $t]) }}"
                   class="tombol tombol-kecil {{ $tingkatAktif === $t ? 'tombol-utama' : 'tombol-sekunder' }}">Tingkat {{ $t }}</a>
            @endforeach
        </div>
    </div>
</div>

@forelse ($daftar as $m)
    @php $status = $m->statusProgres; @endphp
    <div class="kartu mb-3 d-flex align-items-center gap-3 flex-wrap">{{-- I-04-03 --}}
        <div class="aksara fs-2 px-3" style="background:var(--nk-indigo-muda);border-radius:14px">
            {{ $m->jenisAksara->value === 'jawa' ? 'ꦲ' : 'ᮊ' }}
        </div>
        <div class="flex-grow-1">
            <div class="fw-bold">{{ $m->namaMateri }}</div>
            <div class="small teks-redup">{{ $m->jenisAksara->label() }} · Tingkat {{ $m->tingkatKesulitan }}</div>
            @if ($status !== null)
                <div class="bilah mt-2" style="max-width:260px"><span style="width:{{ $m->persenProgres }}%"></span></div>
                <div class="small teks-redup">{{ $m->persenProgres }}%</div>
            @endif
        </div>
        @if ($status !== null)
            <span class="label {{ $kelasStatus[$status] ?? 'label-netral' }}">{{ $labelStatus[$status] ?? 'Belum' }}</span>
        @endif
        <a href="{{ route('materi.show', $m->idMateri) }}"
           class="tombol {{ $status === 'selesai' ? 'tombol-sekunder' : 'tombol-utama' }}">{{ $status === 'selesai' ? 'Ulangi' : 'Pelajari' }}</a>{{-- I-04-04 --}}
    </div>
@empty
    <div class="pesan pesan-info">{{-- I-04-05 --}}
        Belum ada materi untuk pilihan ini. Coba jenis aksara atau tingkat kesulitan lain.
    </div>
@endforelse
@endsection
