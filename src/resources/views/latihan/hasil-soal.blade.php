{{-- I-10 Hasil Latihan mode non-kanvas (bunyi, merangkai, transliterasi); data: $hasil, $materi, $urlTugas. --}}
@extends('latihan.layout-fokus')

@section('judul', 'Hasil Latihan '.ucfirst($hasil['mode']))

@section('isi')
@php
    $skor = rtrim(rtrim(number_format($hasil['rata_rata'], 2, ',', ''), '0'), ',');
@endphp
<div class="lt-fokus">
    <main class="lt-isi-hasil" data-efek-hasil data-skor="{{ $hasil['rata_rata'] }}" data-lulus="{{ $hasil['lulus'] ? '1' : '0' }}">
        <img src="{{ asset($hasil['lulus'] ? 'img/pak-aksa-jempol.svg' : 'img/pak-aksa-berpikir.svg') }}" alt="" width="200" height="220">
        <h1>Hasil Latihan {{ ucfirst($hasil['mode']) }}</h1>
        <p>
            @if ($materi){{ $materi->nama_materi }}. @endif
            {{ $hasil['lulus'] ? 'Apik tenan! Latihan ini berhasil.' : 'Belum mencapai batas berhasil. Ayo coba sekali lagi.' }}
        </p>

        <div class="lt-kartu-statistik">
            <div class="lt-stat lt-stat-emas">
                <span class="lt-pita">Poin</span>
                <div><img src="{{ asset('img/ikon-gobog.svg') }}" alt="" width="34" height="34">+{{ $hasil['poin_didapat'] }} poin</div>
            </div>
            <div class="lt-stat lt-stat-indigo">
                <span class="lt-pita">Skor</span>
                <div><img src="{{ asset('img/ikon-bintang.svg') }}" alt="" width="34" height="34"><span data-efek-skor>{{ $skor }}</span></div>
            </div>
            <div class="lt-stat lt-stat-sogan">
                <span class="lt-pita">Streak</span>
                <div><img src="{{ asset('img/ikon-blencong.svg') }}" alt="" width="34" height="34"><span data-efek-denyut>{{ $hasil['streak'] }}</span> hari</div>
            </div>
        </div>

        @if ($hasil['dari_tugas'] && $hasil['lulus'])
            <p class="lt-keterangan">Komponen tugas ini tercatat selesai.</p>
        @endif

        @foreach ($hasil['badge_baru'] as $nama)
            <div class="lt-badge" data-efek-badge="{{ $nama }}">
                <img src="{{ asset('img/ikon-gunungan.svg') }}" alt="" width="44" height="44">
                <div>
                    <span class="lt-label">Badge baru</span>
                    <strong>{{ $nama }}</strong>
                </div>
            </div>
        @endforeach

        <div class="kartu lt-rincian">
            <table class="tabel-nk align-middle">
                <thead><tr><th>Soal</th><th>Jawabanmu</th><th>Kunci</th><th>Hasil</th></tr></thead>
                <tbody>
                @foreach ($hasil['rincian'] as $r)
                    <tr>
                        <td class="aksara fs-4">{{ $r['target'] }}</td>
                        <td>{{ $r['jawaban'] !== '' ? $r['jawaban'] : '(kosong)' }}</td>
                        <td>{{ $r['kunci'] }}</td>
                        <td>
                            <span class="label {{ $r['benar'] ? 'label-berhasil' : 'label-gagal' }}">{{ $r['benar'] ? 'Benar' : 'Salah' }}</span>
                            @if (! $r['benar'] && $r['posisi_salah'])
                                <div class="small teks-redup">Posisi salah: {{ implode(', ', array_map(fn ($p) => $p + 1, $r['posisi_salah'])) }}</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </main>

    <footer class="lt-bilah-bawah">
        <a href="{{ route('latihan.soal.mulai', [$hasil['mode'], $hasil['id_materi']]) }}" class="lt-tombol lt-tombol-sekunder">Ulangi latihan</a>
        @if ($urlTugas)
            <a href="{{ $urlTugas }}" class="lt-tombol lt-tombol-utama">
                <img src="{{ asset('img/ikon-panah-putih.svg') }}" alt="" width="20" height="20">Kembali ke tugas
            </a>
        @else
            <a href="{{ route('latihan.mode', $hasil['id_materi']) }}" class="lt-tombol lt-tombol-utama">
                <img src="{{ asset('img/ikon-panah-putih.svg') }}" alt="" width="20" height="20">Mode lain
            </a>
        @endif
    </footer>
</div>
@endsection
