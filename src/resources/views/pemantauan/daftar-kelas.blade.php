{{-- Pilihan kelas sebelum memantau progres (pemilik: Natan) --}}
@extends('layouts.app')

@section('judul', 'Pantau Progres')

@section('konten')
    <h1>Pantau Progres Anggota</h1>
    <p class="teks-redup">Pilih kelas yang ingin dipantau.</p>

    @forelse ($daftarKelas as $k)
        <a href="{{ route('pemantauan.index', $k->id_kelas) }}" class="kartu d-flex justify-content-between align-items-center mb-3 text-decoration-none" style="color: var(--nk-tinta)">
            <span class="fw-bold fs-5">{{ $k->nama }}</span>
            <span class="teks-redup">{{ $k->jumlah_anggota }} anggota aktif</span>
        </a>
    @empty
        <div class="pesan pesan-info">Anda belum memiliki kelas aktif.</div>
    @endforelse
@endsection
