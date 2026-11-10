{{-- I-06 Pilih Mode Latihan (DPPL 6.6), di dalam layout bersama. --}}
@extends('layouts.app')

@section('judul', 'Pilih mode latihan')

@php
    $kartu = [
        ['mode' => \App\Enums\ModeLatihan::Menulis, 'judul' => 'Menulis', 'ikon' => 'ikon-kuas.svg', 'warna' => 'indigo', 'teks' => 'Gambar aksara di kanvas dengan bantuan garis.'],
        ['mode' => \App\Enums\ModeLatihan::Bunyi, 'judul' => 'Mencocokkan bunyi', 'ikon' => 'ikon-speaker.svg', 'warna' => 'teal', 'teks' => 'Dengarkan bunyi, pilih aksara yang sesuai.'],
        ['mode' => \App\Enums\ModeLatihan::Merangkai, 'judul' => 'Merangkai aksara', 'ikon' => 'ikon-naskah.svg', 'warna' => 'emas', 'teks' => 'Susun aksara dan tanda baca menjadi kata.'],
        ['mode' => \App\Enums\ModeLatihan::Transliterasi, 'judul' => 'Transliterasi', 'ikon' => 'ikon-pena.svg', 'warna' => 'merah', 'teks' => 'Ubah aksara ke huruf Latin atau sebaliknya.'],
    ];
@endphp

@section('konten')
    <div class="lt-pilih">
        <div class="lt-pilih-utama">
            <div class="lt-judul">
                <a href="{{ route('materi.show', $idMateri) }}" class="lt-kembali">
                    <img src="{{ asset('img/ikon-kembali.svg') }}" alt="" width="20" height="20">Kembali ke materi
                </a>
                <h1>Pilih mode latihan</h1>
                <p>Materi: {{ $namaMateri }}</p>
            </div>

            @if (session('galat'))
                <div class="pesan pesan-gagal" role="alert">{{ session('galat') }}</div>
            @endif

            @foreach (array_chunk($kartu, 2) as $baris)
                <div class="lt-baris-mode">
                    @foreach ($baris as $k)
                        @php $tersedia = view()->exists('latihan.'.$k['mode']->value); @endphp
                        <div class="kartu lt-kartu-mode lt-mode-{{ $k['warna'] }}">
                            <div class="lt-kepala-mode">
                                <span class="lt-kotak-ikon"><img src="{{ asset('img/'.$k['ikon']) }}" alt="" width="34" height="34"></span>
                                <div>
                                    <h2>{{ $k['judul'] }}</h2>
                                    <p>{{ $k['teks'] }}</p>
                                </div>
                            </div>
                            @if ($tersedia)
                                <a href="{{ route('latihan.soal', [$idMateri, $k['mode']]) }}" class="lt-tombol-mulai">Mulai</a>
                            @else
                                <span class="lt-tombol-mulai" aria-disabled="true">Segera hadir</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <aside class="lt-pilih-samping">
            <div class="kartu kartu-emas lt-info">
                <img src="{{ asset('img/pak-aksa-berpikir.svg') }}" alt="" width="120" height="132">
                <div>
                    <h2>Mau latihan apa?</h2>
                    <p>Semua mode memberi poin untuk soal yang baru pertama kali kamu selesaikan.</p>
                </div>
            </div>
            <div class="kartu lt-statistik">
                <div><img src="{{ asset('img/ikon-blencong.svg') }}" alt="" width="30" height="30"><span><b class="lt-sogan">{{ $motivasi['streak'] }}</b>hari</span></div>
                <div><img src="{{ asset('img/ikon-gobog.svg') }}" alt="" width="30" height="30"><span><b class="lt-emas">{{ $motivasi['poin'] }}</b>poin</span></div>
                <div><img src="{{ asset('img/ikon-gunungan.svg') }}" alt="" width="30" height="30"><span><b class="lt-indigo">{{ count($motivasi['daftarBadge']) }}</b>badge</span></div>
            </div>
        </aside>
    </div>
@endsection
