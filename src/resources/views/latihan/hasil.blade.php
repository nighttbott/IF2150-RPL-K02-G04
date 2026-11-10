{{-- I-10 Hasil Latihan (mode non-kanvas, Marvel). Data: $hasil (array), $materi --}}
@extends('layouts.app')
@section('judul', 'Hasil Latihan · Ngaksara')

@section('konten')
<h1 class="mb-1">Hasil Latihan {{ ucfirst($hasil['mode']) }}</h1>
@if ($materi)<p class="teks-redup">{{ $materi->nama_materi }}</p>@endif

<div class="pesan {{ $hasil['lulus'] ? 'pesan-berhasil' : 'pesan-info' }} mb-3">
    Skor rata-rata <strong>{{ rtrim(rtrim(number_format($hasil['rata_rata'], 2, ',', ''), '0'), ',') }}</strong>.
    {{ $hasil['lulus'] ? 'Bagus, latihan ini berhasil.' : 'Belum mencapai batas berhasil. Coba ulangi.' }}
    @if ($hasil['poin_didapat'] > 0) Anda mendapat <strong>+{{ $hasil['poin_didapat'] }} poin</strong>.@endif
    @if ($hasil['dari_tugas'] && $hasil['lulus']) Komponen tugas ini tercatat selesai.@endif
</div>

@foreach ($hasil['badge_baru'] as $nama)
    <div class="pesan pesan-berhasil mb-2">Badge baru: <strong>{{ $nama }}</strong></div>
@endforeach

<div class="table-responsive mb-3">
    <table class="tabel-nk align-middle">
        <thead><tr><th>Soal</th><th>Jawaban Anda</th><th>Kunci</th><th>Hasil</th></tr></thead>
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

<div class="d-flex gap-2 flex-wrap">
    <a href="{{ route('latihan.soal.mulai', [$hasil['mode'], $hasil['id_materi']]) }}" class="tombol tombol-utama">Ulangi</a>
    @if (Route::has('progres.index'))<a href="{{ route('progres.index') }}" class="tombol tombol-sekunder">Lihat progres</a>@endif
    <a href="{{ route('materi.index') }}" class="tombol tombol-sekunder">Daftar materi</a>
</div>
@endsection
