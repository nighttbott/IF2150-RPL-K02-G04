{{-- I-14 Kelola Kelas (Marvel). Data: $daftarKelas --}}
@extends('layouts.app')
@section('judul', 'Kelola Kelas · Ngaksara')

@section('konten')
<h1 class="mb-1">Kelola Kelas</h1>
<p class="teks-redup">Buat kelas, bagikan kode bergabung, dan terbitkan tugas.</p>

@if (session('status'))<div class="pesan pesan-berhasil mb-3">{{ session('status') }}</div>@endif
@if (session('galat'))<div class="pesan pesan-gagal mb-3">{{ session('galat') }}</div>@endif

<form method="POST" action="{{ route('kelas.pengajar.simpan') }}" class="kartu mb-4">
    @csrf
    <label for="nama" class="fw-bold mb-1">Nama kelas baru</label>
    <div class="d-flex gap-2 flex-wrap">
        <input id="nama" name="nama" value="{{ old('nama') }}" maxlength="100" required class="form-control" style="max-width:420px" placeholder="Contoh: Aksara Jawa Dasar">
        <button class="tombol tombol-utama">+ Buat kelas</button>
    </div>
    @error('nama')<div class="pesan pesan-gagal mt-2 mb-0">{{ $message }}</div>@enderror
</form>

@forelse ($daftarKelas as $k)
    <a href="{{ route('kelas.pengajar.tampil', $k->id_kelas) }}" class="kartu d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 text-decoration-none" style="color: var(--nk-tinta)">
        <div>
            <div class="fw-bold fs-5">{{ $k->nama }}</div>
            <div class="teks-redup">{{ $k->jumlah_anggota }} anggota aktif · kode <strong>{{ $k->kode_bergabung }}</strong></div>
        </div>
        <span class="label {{ $k->status->value === 'aktif' ? 'label-berhasil' : 'label-netral' }}">{{ ucfirst($k->status->value) }}</span>
    </a>
@empty
    <div class="pesan pesan-info">Anda belum memiliki kelas. Buat kelas pertama lewat form di atas.</div>
@endforelse
@endsection
