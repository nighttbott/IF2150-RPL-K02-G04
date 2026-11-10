{{-- I-18 Kelola Materi (C09 HalamanKelolaMateri::tampilkanDaftarMateri). Data: $daftar (list MetadataMateri), $jenis --}}
@extends('layouts.app')
@section('judul', 'Kelola Materi · Ngaksara')

@section('konten')
@php $jenisAktif = $jenis?->value; @endphp
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <h1 class="mb-1">Kelola Materi</h1>
        <p class="teks-redup mb-0">Daftar materi, status terbit, dan versi konten.</p>
    </div>
    <a href="{{ route('materi.buat') }}" class="tombol tombol-emas">+ Tambah materi</a>{{-- I-18-02 --}}
</div>

@if (session('status'))
    <div class="pesan pesan-berhasil mb-3">{{ session('status') }}</div>{{-- I-18-05 --}}
@endif
@if (session('galat'))
    <div class="pesan pesan-gagal mb-3">{{ session('galat') }}</div>
@endif

<div class="d-flex gap-2 mb-3">
    <a href="{{ route('materi.kelola') }}" class="tombol tombol-kecil {{ $jenisAktif === null ? 'tombol-utama' : 'tombol-sekunder' }}">Semua</a>
    <a href="{{ route('materi.kelola', ['jenis' => 'jawa']) }}" class="tombol tombol-kecil {{ $jenisAktif === 'jawa' ? 'tombol-utama' : 'tombol-sekunder' }}">Jawa</a>
    <a href="{{ route('materi.kelola', ['jenis' => 'sunda']) }}" class="tombol tombol-kecil {{ $jenisAktif === 'sunda' ? 'tombol-utama' : 'tombol-sekunder' }}">Sunda</a>
</div>

<div class="table-responsive">{{-- I-18-01 --}}
    <table class="tabel-nk align-middle">
        <thead>
            <tr><th>Materi</th><th>Jenis</th><th>Tingkat</th><th>Status</th><th>Versi</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
        @forelse ($daftar as $m)
            <tr>
                <td>{{ $m->namaMateri }}</td>
                <td>{{ $m->jenisAksara->label() }}</td>
                <td>{{ $m->tingkatKesulitan }}</td>
                <td><span class="label {{ $m->statusPublikasi->value === 'terbit' ? 'label-berhasil' : 'label-netral' }}">{{ $m->statusPublikasi->label() }}</span></td>
                <td>{{ $m->versiKonten ?? '-' }}</td>
                <td class="text-end">
                    <div class="d-inline-flex gap-2">
                        @if ($m->statusPublikasi->value === 'draf')
                            <form method="POST" action="{{ route('materi.terbitkan', $m->idMateri) }}">@csrf
                                <button class="tombol tombol-utama tombol-kecil">Terbitkan</button>
                            </form>
                        @endif
                        <a href="{{ route('materi.sunting', $m->idMateri) }}" class="tombol tombol-sekunder tombol-kecil">✎ Sunting</a>{{-- I-18-03 --}}
                        <form method="POST" action="{{ route('materi.arsipkan', $m->idMateri) }}"
                              onsubmit="return confirm('Arsipkan materi ini? Pelajar tidak akan melihatnya lagi, riwayat latihan tetap tersimpan.')">
                            @csrf @method('DELETE')
                            <button class="tombol tombol-sekunder tombol-kecil" style="color:var(--nk-merah-tua)">Arsipkan</button>{{-- I-18-04 --}}
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center teks-redup py-4">Belum ada materi. Tambahkan materi pertama lewat tombol "Tambah materi".</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
