{{-- I-05 Detail Materi (C13 HalamanMateri::tampilkanIsiMateri). Data: $paket (PaketMateri) --}}
@extends('layouts.app')
@section('judul', ($paket->namaMateri ?: 'Detail Materi').' · Ngaksara')

@section('konten')
@php
    $jenis = $paket->jenisAksara ?: 'jawa';
    $pertama = $paket->aksara[0] ?? ['karakter' => '', 'kode' => null];
    $urlLatihan = Route::has('latihan.mode') ? route('latihan.mode', ['materi' => $paket->idMateri]) : '#';
    $urlDaftar = route('materi.index', ['jenis' => $jenis, 'tingkat' => $paket->tingkatKesulitan ?: 1]);
@endphp

@isset($konteksTugas)
    <a href="{{ $konteksTugas['kembali'] }}" class="small text-decoration-none">← Kembali ke tugas</a>
@endisset

@if (! $paket->berhasil())
    <a href="{{ route('materi.index') }}" class="small text-decoration-none">← Kembali ke daftar materi</a>
    <div class="pesan pesan-gagal mt-3">
        Materi belum dapat dimuat.
        @if ($paket->galat === 'KONTEN_TIDAK_KOMPATIBEL')
            Konten materi ini membutuhkan versi aplikasi yang lebih baru.
        @else
            Konten materi belum tersedia. Coba lagi nanti.
        @endif
    </div>
@else
<a href="{{ $urlDaftar }}" class="small text-decoration-none">← Kembali ke daftar materi</a>{{-- I-05-01 --}}
<h1 class="mt-2 mb-0">{{ $paket->namaMateri }}</h1>
<p class="teks-redup">{{ $jenis === 'jawa' ? 'Aksara Jawa' : 'Aksara Sunda' }} · Tingkat {{ $paket->tingkatKesulitan }}</p>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="kartu kartu-emas text-center mb-3">{{-- I-05-02 --}}
            <div class="label-kecil">Aksara</div>
            <div id="aksara-terpilih" class="aksara" style="font-size:4rem;line-height:1.3">{{ $pertama['karakter'] }}</div>
            <div id="kode-terpilih" class="judul fs-4">{{ $pertama['kode'] }}</div>
            <div id="bunyi-terpilih" class="teks-redup mb-3">@if ($pertama['kode']) Bunyi: "{{ $pertama['kode'] }}" @endif</div>
            <button type="button" id="tombol-dengar" class="tombol tombol-teal">🔊 Dengarkan</button>{{-- I-05-03 --}}
            <div id="galat-audio" class="pesan pesan-gagal text-start mt-3 d-none">{{-- I-05-06 --}}
                Audio tidak dapat diputar. Periksa koneksi lalu tekan Dengarkan sekali lagi.
            </div>
        </div>

        <div class="kartu mb-3">{{-- I-05-04 --}}
            <h2 class="fs-5">Aturan penulisan</h2>
            <p class="mb-0" style="white-space:pre-line">{{ $paket->aturan }}</p>
        </div>

        <div class="kartu mb-3">{{-- I-05-05 --}}
            <h2 class="fs-5">Contoh</h2>
            <p class="mb-0 aksara fs-5" style="white-space:pre-line">{{ $paket->contoh }}</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ $urlLatihan }}" class="tombol tombol-utama">✎ Mulai latihan</a>{{-- I-05-07 --}}
            <a href="{{ $urlDaftar }}" class="tombol tombol-sekunder">← Kembali</a>{{-- I-05-08 --}}
        </div>
        @isset($konteksTugas)
            <form method="post" action="{{ $konteksTugas['selesai'] }}" class="mt-3">
                @csrf
                <p>Setelah selesai membaca materi ini, konfirmasikan untuk mencatat kemajuan tugasmu.</p>
                <button type="submit" class="tombol tombol-utama">Selesai membaca</button>
            </form>
        @endisset
    </div>

    <div class="col-lg-4">
        <div class="kartu">
            <h2 class="fs-5">Aksara dalam materi ini</h2>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($paket->aksara as $a)
                    <button type="button" class="tombol tombol-sekunder tombol-kecil pilih-aksara flex-column @if ($loop->first) aktif @endif"
                            data-karakter="{{ $a['karakter'] }}" data-kode="{{ $a['kode'] }}">
                        <span class="aksara fs-4">{{ $a['karakter'] }}</span>
                        @if ($a['kode'])<span class="small">{{ $a['kode'] }}</span>@endif
                    </button>
                @endforeach
            </div>
            <div class="small teks-redup mt-3">Versi konten {{ $paket->versiKonten }}</div>
        </div>
    </div>
</div>
@endif
@endsection

@push('gaya')
<style>
    .pilih-aksara.aktif { background: var(--nk-indigo-muda); border-color: var(--nk-indigo); }
</style>
@endpush

@push('skrip')
@if ($paket->berhasil())
<script>
(() => {
    const audioUrl = @json($paket->pathAudio ? route('materi.audio', $paket->idMateri) : null);
    let kodeAktif = @json($pertama['kode']);
    const galat = document.getElementById('galat-audio');

    document.querySelectorAll('.pilih-aksara').forEach(tombol => tombol.addEventListener('click', () => {
        document.querySelectorAll('.pilih-aksara').forEach(t => t.classList.remove('aktif'));
        tombol.classList.add('aktif');
        kodeAktif = tombol.dataset.kode || '';
        document.getElementById('aksara-terpilih').textContent = tombol.dataset.karakter;
        document.getElementById('kode-terpilih').textContent = kodeAktif;
        document.getElementById('bunyi-terpilih').textContent = kodeAktif ? `Bunyi: "${kodeAktif}"` : '';
        galat.classList.add('d-none');
    }));

    document.getElementById('tombol-dengar').addEventListener('click', async () => {
        galat.classList.add('d-none');
        try {
            if (audioUrl) {
                await new Audio(audioUrl).play();                   // berkas audio materi (mp3)
            } else if (kodeAktif && 'speechSynthesis' in window) {
                const u = new SpeechSynthesisUtterance(kodeAktif);  // cadangan: pengucapan latin
                u.lang = 'id-ID';
                speechSynthesis.cancel();
                speechSynthesis.speak(u);
            } else {
                throw new Error('audio tidak tersedia');
            }
        } catch (e) {
            galat.classList.remove('d-none');
        }
    });
})();
</script>
@endif
@endpush
