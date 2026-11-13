{{-- I-13 Pengerjaan Tugas (DPPL 6.13); data: $detail, $kelas, $progres, $hasil, $namaMateri, $namaKelas. --}}
@extends('layouts.app')
@section('judul', 'Pengerjaan Tugas · Ngaksara')

@php
    $jumlahSelesai = $progres['wajib'] - count($progres['kurang']);
    $persen = $progres['wajib'] > 0 ? round($jumlahSelesai / $progres['wajib'] * 100) : 100;
    $sisa = count($progres['kurang']);
    $status = $hasil['status'] ?? 'belum_selesai';
    $baru = session('baru_dikumpulkan');
@endphp

@section('konten')
<div class="kp-judul">
    <a href="{{ route('kelas-pelajar.kelas', $kelas) }}" class="kp-kembali">
        <img src="{{ asset('img/ikon-kembali.svg') }}" alt="" width="18" height="18">Kembali ke Kelas Saya
    </a>
    <h1>Pengerjaan Tugas</h1>
    <p>Kelas {{ $namaKelas }}</p>
</div>

<div class="kp-tumpuk">
    <section class="kartu kp-kartu" data-efek-muncul>
        <h2>Instruksi</h2>
        <p class="kp-instruksi">{{ $detail->instruksi }}</p>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span @class(['kp-tenggat', 'lewat' => $detail->tenggat->isPast()])>Tenggat {{ $detail->tenggat->translatedFormat('d M Y, H.i') }} WIB</span>
            @if ($detail->tenggat->isPast())<span class="label label-gagal">Sudah lewat tenggat</span>@endif
        </div>
    </section>

    <section class="kartu kp-kartu" data-efek-muncul>
        <div class="kp-kepala-komponen">
            <h2>Komponen tugas</h2>
            <span>{{ $jumlahSelesai }} dari {{ $progres['wajib'] }} wajib selesai</span>
        </div>
        <div class="bilah mb-2" role="progressbar" aria-label="Kemajuan komponen wajib" aria-valuenow="{{ $persen }}" aria-valuemin="0" aria-valuemax="100">
            <span data-efek-bilah style="width: {{ $persen }}%"></span>
        </div>
        @foreach ($detail->komponen as $c)
            @php $selesai = (bool) $progres['selesai'][$c->id_komponen]; @endphp
            <div class="kp-baris">
                <span @class(['kp-ikon-komponen', 'selesai' => $selesai])>
                    @if ($selesai)<img src="{{ asset('img/ikon-centang-putih.svg') }}" alt="" width="18" height="18">@else @include('layouts.ikon', ['nama' => $c->jenis === 'materi' ? 'buku' : 'pena'])@endif
                </span>
                <div class="kp-baris-teks">
                    <strong>{{ $c->jenis === 'materi' ? 'Materi' : 'Latihan '.$c->mode }}: {{ $namaMateri[$c->id_materi] ?? 'Materi #'.$c->id_materi }}</strong>
                    <small>{{ ucfirst($c->jenis) }} · {{ $c->wajib ? 'wajib' : 'opsional' }}</small>
                </div>
                <div class="kp-baris-aksi">
                    <span class="label {{ $selesai ? 'label-berhasil' : 'label-netral' }}">{{ $selesai ? 'Selesai' : 'Belum' }}</span>
                    <a class="tombol tombol-sekunder tombol-kecil" href="{{ route('kelas-pelajar.kerjakan', [$kelas, $detail->id_tugas, $c->id_komponen]) }}">Kerjakan</a>
                </div>
            </div>
        @endforeach
    </section>

    @if ($errors->any())
        <div class="pesan pesan-gagal kp-pesan" role="alert">
            <div><strong>Pengumpulan gagal disimpan</strong>{{ $errors->first() }}</div>
        </div>
    @endif

    @if ($status !== 'belum_selesai')
        <div @class(['pesan', 'kp-pesan', 'pesan-berhasil' => $status === 'tepat_waktu', 'kp-pesan-emas' => $status === 'terlambat']) role="status"
             @if ($baru) data-efek-kumpul="{{ $status }}" @endif>
            <div>
                <strong>Tugas dikumpulkan dengan status {{ $status === 'tepat_waktu' ? 'tepat waktu' : 'terlambat' }}</strong>
                {{ $status === 'tepat_waktu' ? 'Semua komponen wajib selesai sebelum tenggat.' : 'Waktu selesai melewati tenggat, jadi statusnya terlambat.' }}
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('kelas-pelajar.kumpulkan', [$kelas, $detail->id_tugas]) }}">
            @csrf
            <button class="tombol tombol-utama" @disabled(! $progres['lengkap'])>
                <img src="{{ asset('img/ikon-centang-putih.svg') }}" alt="" width="18" height="18">Kumpulkan tugas
            </button>
            @unless ($progres['lengkap'])
                <p class="teks-redup mt-2 mb-0">Selesaikan seluruh komponen wajib untuk mengumpulkan tugas.</p>
            @endunless
        </form>
    @endif
</div>
@endsection

@section('samping')
    <div class="kartu kartu-emas kp-tip" data-efek-muncul>
        <img src="{{ asset('img/pak-aksa-jempol.svg') }}" alt="Pak Aksa">
        <div>
            @if ($status !== 'belum_selesai')
                <h2>Apik tenan!</h2>
                <p>Tugas ini sudah kamu kumpulkan. Lanjutkan belajar di materi lain.</p>
            @elseif ($progres['lengkap'])
                <h2>Siap dikumpulkan!</h2>
                <p>Semua komponen wajib sudah selesai. Tekan tombol Kumpulkan tugas.</p>
            @else
                <h2>{{ $sisa === 1 ? 'Hampir selesai!' : 'Terus semangat!' }}</h2>
                <p>Tinggal {{ $sisa }} komponen wajib lagi sebelum tugas bisa dikumpulkan.</p>
            @endif
        </div>
    </div>
@endsection
