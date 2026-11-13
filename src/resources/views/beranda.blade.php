{{-- I-03 Halaman Utama / Beranda (C07 HalamanUtama); data dari BerandaController::tampilkan (motivasi, saran, jalur materi, target harian, kelas, tips). --}}
@extends('layouts.app')
@section('judul', 'Beranda · Ngaksara')

@section('konten')
@php
    // Posisi horizontal simpul agar jalur berkelok (dari Figma 83:1018), berulang tiap lima simpul.
    $geser = [86, 186, 123, 38, 122];
    $hurufAwal = ['jawa' => 'ꦲ', 'sunda' => 'ᮃ'];
@endphp

<div class="nk-beranda">
    <div class="nk-beranda-utama d-flex flex-column gap-4">

        {{-- I-03-03 Sapaan --}}
        <section class="kartu kartu-emas nk-sapaan">
            <div>
                <div class="label-kecil nk-sapaan-salam">{{ $salam }}</div>
                <h1 class="nk-sapaan-judul">Halo, {{ $akun->nama_tampilan }}!</h1>
                <p class="teks-redup mb-0">
                    @if (! $pelajar)
                        Pilih menu di samping untuk mulai.
                    @elseif ($saran)
                        {{ $saranBaru ? 'Hari ini coba' : 'Hari ini kita lanjutkan' }} {{ $saran->nama_materi }}.
                    @else
                        Semua materi sudah tuntas. Ulangi latihan supaya hafalanmu tetap terjaga.
                    @endif
                </p>
            </div>
            <img src="{{ asset('img/pak-aksa-jempol.svg') }}" alt="" width="140" height="154" class="d-none d-sm-block">
        </section>

        @if ($pelajar)
            {{-- I-03-04 Lanjutkan belajar --}}
            @if ($lanjutkan)
                <section class="kartu nk-lanjutkan">
                    <div class="nk-lanjutkan-huruf aksara" aria-hidden="true">
                        {{ $hurufAwal[$lanjutkan['materi']->jenis_aksara?->value] ?? 'ꦲ' }}
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="label-kecil">Lanjutkan belajar</div>
                        <div class="nk-lanjutkan-nama text-truncate">{{ $lanjutkan['materi']->nama_materi }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="bilah nk-lanjutkan-bilah" role="progressbar" aria-label="Progres materi"
                                 aria-valuenow="{{ $lanjutkan['persen'] }}" aria-valuemin="0" aria-valuemax="100">
                                <span style="width: {{ $lanjutkan['persen'] }}%"></span>
                            </div>
                            <span class="nk-lanjutkan-persen">{{ $lanjutkan['persen'] }}%</span>
                        </div>
                    </div>
                    <a href="{{ route('materi.show', $lanjutkan['materi']->id_materi) }}" class="tombol tombol-utama nk-tombol-besar">
                        <img src="{{ asset('img/ikon-panah-putih.svg') }}" alt="" width="20" height="20">
                        Lanjut
                    </a>
                </section>
            @endif

            {{-- I-03-05 Peta belajar: satu bagian per tingkat kesulitan, gembok mengikuti Algo-041 --}}
            <section class="kartu d-flex flex-column gap-4">
                @forelse ($jalur['bagian'] as $bagian)
                    <div>
                        <div class="nk-spanduk">
                            <div>
                                <div class="nk-spanduk-bagian">Bagian {{ $bagian['tingkat'] }}</div>
                                <h2 class="nk-spanduk-judul">{{ $jalur['judul'] }}</h2>
                            </div>
                            @if (Route::has('materi.index'))
                                <a href="{{ route('materi.index', ['jenis' => $jalur['jenis'], 'tingkat' => $bagian['tingkat']]) }}" class="nk-tombol-panduan">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 6c-2-1.5-5-2-8-2v14c3 0 6 .5 8 2 2-1.5 5-2 8-2V4c-3 0-6 .5-8 2Z"/><path d="M12 6v14"/>
                                    </svg>
                                    Panduan
                                </a>
                            @endif
                        </div>

                        <ol class="nk-jalur" aria-label="Jalur belajar {{ $jalur['judul'] }} bagian {{ $bagian['tingkat'] }}">
                            @foreach ($bagian['langkah'] as $i => $langkah)
                                <li class="nk-langkah nk-langkah-{{ $langkah['keadaan'] }}" style="--geser: {{ $geser[$i % count($geser)] }}px">
                                    @if ($langkah['keadaan'] === 'mulai')
                                        <span class="nk-penanda-mulai">Mulai</span>
                                    @endif

                                    @if ($langkah['keadaan'] === 'terkunci')
                                        <span class="nk-simpul" aria-hidden="true">
                                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                                        </span>
                                        <span class="nk-langkah-nama">{{ \Illuminate\Support\Str::after($langkah['nama'], ': ') }} <span class="visually-hidden">(belum terbuka)</span></span>
                                    @else
                                        <a href="{{ route('materi.show', $langkah['id_materi']) }}" class="nk-simpul"
                                        aria-label="{{ \Illuminate\Support\Str::after($langkah['nama'], ': ') }} ({{ ['selesai' => 'selesai', 'mulai' => 'materi berikutnya', 'terbuka' => 'terbuka'][$langkah['keadaan']] }})">
                                            @if ($langkah['keadaan'] === 'selesai')
                                                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                                            @elseif ($langkah['keadaan'] === 'mulai')
                                                <svg width="34" height="34" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2.5 2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4l-5.9 3.1 1.2-6.5L2.5 9.4l6.6-.9z"/></svg>
                                            @else
                                                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6c-2-1.5-5-2-8-2v14c3 0 6 .5 8 2 2-1.5 5-2 8-2V4c-3 0-6 .5-8 2Z"/><path d="M12 6v14"/></svg>
                                            @endif
                                        </a>
                                        <span class="nk-langkah-nama" aria-hidden="true">{{ \Illuminate\Support\Str::after($langkah['nama'], ': ') }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @empty
                    <div class="pesan pesan-info">Belum ada materi terbit.</div>
                @endforelse
            </section>
        @endif
    </div>

    @if ($pelajar)
        <aside class="nk-beranda-samping d-flex flex-column gap-4">
            {{-- I-03-06 Ringkasan motivasi --}}
            <section class="kartu nk-statistik" aria-label="Ringkasan motivasi">
                <div class="nk-stat">
                    <img src="{{ asset('img/ikon-blencong.svg') }}" alt="" width="30" height="30">
                    <div><strong class="nk-stat-hari">{{ $motivasi['streak'] }}</strong><span>hari</span></div>
                </div>
                <div class="nk-stat">
                    <img src="{{ asset('img/ikon-gobog.svg') }}" alt="" width="30" height="30">
                    <div><strong class="nk-stat-poin">{{ $motivasi['poin'] }}</strong><span>poin</span></div>
                </div>
                <div class="nk-stat">
                    <img src="{{ asset('img/ikon-gunungan.svg') }}" alt="" width="30" height="30">
                    <div><strong class="nk-stat-badge">{{ $motivasi['badge'] }}</strong><span>badge</span></div>
                </div>
            </section>

            {{-- I-03-07 Target harian --}}
            <section class="kartu">
                <h2 class="nk-judul-kartu">Target hari ini</h2>
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('img/ikon-blencong.svg') }}" alt="" width="28" height="28">
                    <div class="flex-grow-1">
                        <div class="fw-bold small mb-1">{{ min($target['selesai'], $target['target']) }} dari {{ $target['target'] }} latihan</div>
                        <div class="bilah" role="progressbar" aria-label="Target latihan hari ini"
                             aria-valuenow="{{ $target['selesai'] }}" aria-valuemin="0" aria-valuemax="{{ $target['target'] }}">
                            <span class="emas" style="width: {{ min(100, round($target['selesai'] / $target['target'] * 100)) }}%"></span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Ringkasan kelas --}}
            <section class="kartu">
                <h2 class="nk-judul-kartu">Kelasmu</h2>
                @forelse ($kelas as $k)
                    <a href="{{ route('kelas-pelajar.kelas', $k['id_kelas']) }}" class="d-flex align-items-center gap-3 text-reset text-decoration-none @unless ($loop->last) mb-3 @endunless">
                        <img src="{{ asset('img/ikon-joglo.svg') }}" alt="" width="26" height="26">
                        <div class="overflow-hidden">
                            <div class="fw-bold small text-truncate">{{ $k['nama'] }}</div>
                            <div class="teks-redup" style="font-size: .8rem">{{ $k['tugas_menunggu'] > 0 ? $k['tugas_menunggu'].' tugas menunggu' : 'Semua tugas sudah dikumpulkan' }}</div>
                        </div>
                    </a>
                @empty
                    <p class="small teks-redup mb-0">
                        Kamu belum ikut kelas.
                        @if (Route::has('kelas-pelajar.index'))
                            <a href="{{ route('kelas-pelajar.index') }}" class="fw-bold text-decoration-none">Gabung kelas</a>
                        @endif
                    </p>
                @endforelse
            </section>

            {{-- Kata Pak Aksa --}}
            <section class="kartu kartu-emas d-flex align-items-center gap-3">
                <img src="{{ asset('img/pak-aksa-melambai.svg') }}" alt="" width="96" height="106">
                <div>
                    <h2 class="nk-judul-kartu nk-judul-tips">Kata Pak Aksa</h2>
                    <p class="small fw-semibold mb-0">{{ $tips }}</p>
                </div>
            </section>
        </aside>
    @endif
</div>
@endsection

@push('gaya')
<style>
    /* Kolom utama 716px + kolom samping 340px, jarak 32px (Figma 83:948) */
    .nk-beranda { display: grid; grid-template-columns: minmax(0, 716px) 340px; gap: 32px; align-items: start; }
    @media (max-width: 1199.98px) {
        .nk-beranda { grid-template-columns: minmax(0, 1fr); }
    }

    /* I-03-03 */
    .nk-sapaan { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 24px; }
    .nk-sapaan-salam { color: var(--nk-sogan); }
    .nk-sapaan-judul { font-size: 34px; line-height: 40px; margin: 4px 0; }

    /* I-03-04 */
    .nk-lanjutkan { display: flex; align-items: center; gap: 18px; }
    .nk-lanjutkan-huruf { width: 72px; height: 72px; flex: none; display: flex; align-items: center; justify-content: center; border-radius: 16px; background: var(--nk-indigo-muda); color: var(--nk-indigo); font-size: 40px; }
    .nk-lanjutkan-nama { font-family: 'Baloo 2', sans-serif; font-weight: 700; font-size: 17px; line-height: 24px; margin: 4px 0 6px; }
    .nk-lanjutkan-bilah { width: 100%; max-width: 300px; }
    .nk-lanjutkan-persen { font-size: 13px; font-weight: 800; color: var(--nk-indigo); }
    .nk-tombol-besar { flex: none; padding: 14px 24px; font-size: 17px; line-height: 22px; }

    /* I-03-05 Spanduk bagian */
    .nk-spanduk { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 20px; border-radius: 14px; background: var(--nk-indigo); color: #fff; }
    .nk-spanduk-bagian { font-size: .75rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: var(--nk-indigo-muda); }
    .nk-spanduk-judul { font-size: 22px; line-height: 30px; margin: 0; color: #fff; }
    .nk-tombol-panduan { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border: 2px solid #fff; border-radius: 12px; color: #fff; font-family: 'Baloo 2', sans-serif; font-weight: 700; text-decoration: none; }
    .nk-tombol-panduan:hover { background: rgba(255, 255, 255, .12); color: #fff; }

    /* I-03-05 Jalur simpul */
    .nk-jalur { list-style: none; margin: 18px 0 0; padding: 0; display: flex; flex-direction: column; gap: 18px; }
    .nk-langkah { width: 160px; margin-left: var(--geser); display: flex; flex-direction: column; align-items: center; gap: 8px; }
    .nk-simpul { width: 78px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none; transition: transform .1s ease; }
    a.nk-simpul:hover { transform: translateY(-2px); color: #fff; }
    a.nk-simpul:focus-visible { outline: 3px solid var(--nk-emas); outline-offset: 4px; }
    .nk-langkah-nama { font-size: 13px; line-height: 18px; font-weight: 800; text-align: center; color: var(--nk-tinta); }
    .nk-langkah-selesai .nk-simpul { background: var(--nk-indigo); box-shadow: 0 6px 0 var(--nk-indigo-tua); }
    .nk-langkah-mulai .nk-simpul { background: var(--nk-emas); box-shadow: 0 6px 0 var(--nk-emas-tua); }
    .nk-langkah-terkunci .nk-simpul { background: #EFE8DC; box-shadow: 0 6px 0 #C9BBA6; color: #B9AE9E; }
    .nk-langkah-terkunci .nk-langkah-nama { color: #B9AE9E; }
    .nk-langkah-terbuka .nk-simpul { background: var(--nk-indigo-muda); box-shadow: 0 6px 0 #C7CDEB; color: var(--nk-indigo); }
    .nk-penanda-mulai { padding: 3px 10px; border: 2px solid var(--nk-emas); border-radius: 8px; background: #fff; font-size: .75rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: var(--nk-emas-tua); }
    @media (max-width: 575.98px) {
        .nk-langkah { margin-left: calc(var(--geser) / 3); }
        .nk-lanjutkan { flex-wrap: wrap; }
    }

    /* Kolom samping */
    .nk-judul-kartu { font-size: 18px; line-height: 24px; margin: 0 0 14px; }
    .nk-judul-tips { color: var(--nk-sogan); margin-bottom: 4px; }
    .nk-statistik { display: flex; justify-content: space-between; padding: 14px 16px; }
    .nk-stat { display: flex; align-items: center; gap: 8px; }
    .nk-stat strong { display: block; font-family: 'Baloo 2', sans-serif; font-weight: 800; font-size: 20px; line-height: 24px; }
    .nk-stat span { display: block; font-size: 12px; line-height: 16px; color: var(--nk-redup); }
    .nk-stat-hari { color: var(--nk-sogan); }
    .nk-stat-poin { color: var(--nk-emas-tua); }
    .nk-stat-badge { color: var(--nk-indigo); }
</style>
@endpush