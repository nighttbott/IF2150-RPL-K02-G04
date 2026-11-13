{{-- I-12 Kelas Saya dan Daftar Tugas (DPPL 6.12); data: $daftar, $kelas, $tugas, $status, $motivasi. --}}
@extends('layouts.app')
@section('judul', 'Kelas Saya · Ngaksara')

@php
    $labelStatus = [
        'belum_selesai' => ['Belum dikumpulkan', 'label-netral'],
        'tepat_waktu' => ['Tepat waktu', 'label-berhasil'],
        'terlambat' => ['Terlambat', 'label-gagal'],
    ];
@endphp

@section('konten')
<div class="kp-judul">
    <h1>Kelas Saya</h1>
    <p>Gabung ke kelas dengan kode dari Pengajar, lalu kerjakan tugasnya.</p>
</div>

<div class="kp-tumpuk">
    <section class="kartu kp-kartu" data-efek-muncul>
        <h2>Gabung kelas</h2>
        <form class="kp-gabung" method="POST" action="{{ route('kelas-pelajar.gabung') }}">
            @csrf
            <label for="kode" class="visually-hidden">Kode kelas</label>
            <div class="kp-isian">
                <img src="{{ asset('img/ikon-kunci.svg') }}" alt="" width="18" height="18">
                <input id="kode" name="kode" value="{{ old('kode') }}" placeholder="Masukkan kode, contoh K7M2QX" maxlength="6" minlength="6" pattern="[A-Za-z0-9]{6}" required autocomplete="off">
            </div>
            <button class="tombol tombol-utama">Gabung</button>
        </form>
        @if (session('sukses'))
            <div class="pesan pesan-berhasil mt-3" role="status">{{ session('sukses') }}</div>
        @endif
        @if ($errors->any())
            <div class="pesan pesan-gagal mt-3" role="alert">{{ $errors->first() }}</div>
        @endif
    </section>

    @if ($daftar->isNotEmpty())
        <nav class="kp-tab" aria-label="Kelas yang diikuti">
            @foreach ($daftar as $item)
                <a href="{{ route('kelas-pelajar.kelas', $item->id_kelas) }}" @class(['aktif' => (int) $kelas === (int) $item->id_kelas])>
                    @include('layouts.ikon', ['nama' => 'joglo']){{ $item->nama }}
                </a>
            @endforeach
        </nav>
    @endif

    <section class="kartu kp-kartu" data-efek-muncul>
        <h2>Tugas aktif <span class="label kp-hitung">{{ $tugas->count() }} tugas</span></h2>
        @forelse ($tugas as $t)
            @php [$teks, $kelasLabel] = $labelStatus[$status[$t->id_tugas]['status'] ?? 'belum_selesai'] ?? $labelStatus['belum_selesai']; @endphp
            <div class="kp-baris">
                <span class="kp-ikon-tugas">@include('layouts.ikon', ['nama' => 'buku'])</span>
                <div class="kp-baris-teks">
                    <strong>{{ \Illuminate\Support\Str::limit($t->instruksi, 65) }}</strong>
                    <small>Tenggat {{ $t->tenggat->translatedFormat('d M Y, H.i') }} WIB</small>
                </div>
                <div class="kp-baris-aksi">
                    <span class="label {{ $kelasLabel }}">{{ $teks }}</span>
                    <a class="tombol tombol-sekunder tombol-kecil" href="{{ route('kelas-pelajar.detail', [$kelas, $t->id_tugas]) }}">Buka</a>
                </div>
            </div>
        @empty
            <p class="teks-redup mb-0">{{ $daftar->isEmpty() ? 'Belum ada kelas yang diikuti. Masukkan kode dari Pengajar.' : 'Belum ada tugas yang diterbitkan di kelas ini.' }}</p>
        @endforelse
    </section>
</div>
@endsection

@section('samping')
    <div class="kartu kp-statistik" data-efek-muncul>
        <div><img src="{{ asset('img/ikon-blencong.svg') }}" alt="" width="26" height="26"><span><b>{{ $motivasi['streak'] }}</b>hari</span></div>
        <div><img src="{{ asset('img/ikon-gobog.svg') }}" alt="" width="26" height="26"><span><b>{{ $motivasi['poin'] }}</b>poin</span></div>
        <div><img src="{{ asset('img/ikon-gunungan.svg') }}" alt="" width="26" height="26"><span><b>{{ count($motivasi['daftarBadge']) }}</b>badge</span></div>
    </div>
    <div class="kartu kartu-emas kp-tip" data-efek-muncul>
        <img src="{{ asset('img/pak-aksa-berpikir.svg') }}" alt="Pak Aksa">
        <div>
            <h2>Kata Pak Aksa</h2>
            <p>Kumpulkan sebelum tenggat, ya. Tugas yang lewat tenggat tetap bisa dikumpulkan, tapi ditandai terlambat.</p>
        </div>
    </div>
@endsection
