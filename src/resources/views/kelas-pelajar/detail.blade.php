@extends('layouts.app')
@section('judul', 'Pengerjaan Tugas · Ngaksara')

@section('konten')
@php
    $wajibSelesai = $progres['wajib'] - count($progres['kurang']);
    $persenWajib = (int) round($wajibSelesai / max(1, $progres['wajib']) * 100);
@endphp

<div class="nk-kelas-halaman">
    @include('kelas-pelajar.layout')

    <a href="{{ route('kelas-pelajar.kelas', $kelas) }}" class="d-inline-block mb-2 fw-bold text-decoration-none">← Kembali ke Kelas Saya</a>
    <h1>Pengerjaan Tugas</h1>

    <div class="nk-kelas">
        <section class="d-flex flex-column gap-4">
            <div class="kartu">
                <h2>Instruksi</h2>
                <p class="nk-instruksi">{{ $detail->instruksi }}</p>
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <strong>Tenggat {{ $detail->tenggat->format('d M Y, H:i') }} WIB</strong>
                    @if ($detail->tenggat->isPast())
                        <span class="label label-gagal">Sudah lewat tenggat</span>
                    @endif
                </div>
            </div>

            <div class="kartu">
                <h2>Komponen tugas</h2>
                <p class="teks-redup mb-2">{{ $wajibSelesai }} dari {{ $progres['wajib'] }} komponen wajib selesai</p>
                <div class="bilah mb-2" role="progressbar" aria-label="Kemajuan komponen wajib"
                     aria-valuenow="{{ $wajibSelesai }}" aria-valuemin="0" aria-valuemax="{{ max(1, $progres['wajib']) }}">
                    <span style="width: {{ $persenWajib }}%"></span>
                </div>

                @foreach ($detail->komponen as $c)
                    @php($selesai = (bool) $progres['selesai'][$c->id_komponen])
                    <div class="nk-baris">
                        <div>
                            <strong>{{ ucfirst($c->jenis) }} {{ $c->mode ? '· '.$c->mode : '' }}</strong>
                            <small>Materi #{{ $c->id_materi }} · {{ $c->wajib ? 'Wajib' : 'Opsional' }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span @class(['label', 'label-berhasil' => $selesai, 'label-netral' => ! $selesai])>{{ $selesai ? 'Selesai' : 'Belum' }}</span>
                            <a class="tombol tombol-sekunder tombol-kecil" href="{{ route('kelas-pelajar.kerjakan', [$kelas, $detail->id_tugas, $c->id_komponen]) }}">Kerjakan</a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($hasil['status'] !== 'belum_selesai')
                <div class="pesan pesan-berhasil" role="status">Tugas sudah dikumpulkan: {{ str_replace('_', ' ', $hasil['status']) }}.</div>
            @else
                <form method="post" action="{{ route('kelas-pelajar.kumpulkan', [$kelas, $detail->id_tugas]) }}">
                    @csrf
                    <button class="tombol tombol-utama" @disabled(! $progres['lengkap'])>Kumpulkan tugas</button>
                    @unless ($progres['lengkap'])
                        <p class="teks-redup mt-2 mb-0">Selesaikan seluruh komponen wajib untuk mengumpulkan tugas.</p>
                    @endunless
                </form>
            @endif
        </section>

        <aside class="kartu kartu-emas nk-kelas-tips d-flex gap-3">
            <img src="{{ asset('img/pak-aksa-jempol.svg') }}" alt="">
            <div>
                <h2>{{ $progres['lengkap'] ? 'Siap dikumpulkan!' : 'Terus semangat!' }}</h2>
                <p>Pelajari materi dan selesaikan latihan. Kemajuan diambil dari riwayat belajarmu.</p>
            </div>
        </aside>
    </div>
</div>
@endsection