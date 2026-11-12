@extends('layouts.app')

@section('judul', 'Kelola Kelas')

@push('gaya') @include('kelas-pengajar.gaya') @endpush

@section('konten')
<div class="row g-4">
    <div class="col-lg-9">
        <h1 class="h3 fw-bold mb-1">Kelola Kelas</h1>
        <p class="teks-redup">Buat kelas, bagikan kode, dan atur tugas untuk anggota.</p>

        <div class="kartu p-3 mb-3">
            <div class="fw-bold mb-2">Buat kelas baru</div>
            <form method="POST" action="{{ route('pengajar.kelas.buat') }}" class="d-flex gap-2">
                @csrf
                <input type="text" name="nama" value="{{ old('nama') }}" maxlength="100" required
                       class="form-control @error('nama') is-invalid @enderror"
                       placeholder="contoh: Aksara Sunda Dasar">
                <button type="submit" class="tombol tombol-utama text-nowrap">+ Buat kelas</button>
            </form>
            @error('nama')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
        </div>

        @if (session('sukses'))
            <div class="pesan pesan-berhasil mb-3">
                <div class="fw-bold">{{ session('sukses')['judul'] }}</div>
                <div class="small">{{ session('sukses')['isi'] }}</div>
            </div>
        @endif
        @error('kode')
            <div class="pesan pesan-gagal mb-3">{{ $message }}</div>
        @enderror

        @forelse ($daftarKelas as $kelas)
            <div class="kartu p-3 mb-3">
                <div class="fw-bold fs-5">{{ $kelas->nama }}</div>
                <div class="teks-redup small mb-2">
                    {{ $kelas->jumlah_anggota }} anggota aktif · {{ $kelas->jumlah_tugas }} tugas terbit
                </div>

                <div class="kotak-kode d-flex justify-content-between align-items-center p-2 px-3 mb-2">
                    <div>
                        <div class="small teks-redup">KODE BERGABUNG</div>
                        <div class="kode fs-5">{{ $kelas->kode_bergabung }}</div>
                    </div>
                    <form method="POST" action="{{ route('pengajar.kelas.ganti-kode', $kelas->id_kelas) }}">
                        @csrf
                        <button type="submit" class="tombol tombol-sekunder tombol-kecil">Ganti kode</button>
                    </form>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('pengajar.kelas.tugas', $kelas->id_kelas) }}" class="tombol tombol-utama tombol-kecil">Terbitkan tugas</a>
                    <a href="{{ route('pengajar.kelas.anggota', $kelas->id_kelas) }}" class="tombol tombol-teal tombol-kecil">Kelola anggota</a>
                    <a href="{{ route('pemantauan.index', $kelas->id_kelas) }}" class="tombol tombol-emas tombol-kecil">Pantau progres</a>
                </div>
            </div>
        @empty
            <div class="kartu p-4 text-center teks-redup">Belum ada kelas. Buat kelas pertama Anda di atas.</div>
        @endforelse
    </div>

    <div class="col-lg-3">
        <div class="kotak-tips p-3">
            <div class="fw-bold" style="color:var(--nk-emas-tua)">Tips Pak Aksa</div>
            <div class="small">Kode lama tidak bisa dipakai lagi setelah kode diganti. Bagikan kode baru ke Pelajar.</div>
        </div>
    </div>
</div>
@endsection
