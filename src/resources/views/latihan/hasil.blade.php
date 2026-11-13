{{-- I-10 Hasil Latihan (DPPL 6.10). --}}
@extends('latihan.layout-fokus')

@section('judul', 'Latihan rampung')

@section('isi')
<div class="lt-fokus">
    <main class="lt-isi-hasil" data-efek-hasil data-skor="{{ $skor }}" data-lulus="1">
        <img src="{{ asset('img/pak-aksa-jempol.svg') }}" alt="" width="220" height="242">
        <h1>Latihan rampung!</h1>
        <p>Apik tenan! Kamu menyelesaikan latihan {{ $mode->value }} "{{ $namaMateri }}".</p>

        <div class="lt-kartu-statistik">
            <div class="lt-stat lt-stat-emas">
                <span class="lt-pita">Poin</span>
                <div><img src="{{ asset('img/ikon-gobog.svg') }}" alt="" width="34" height="34"><span data-efek-denyut>+{{ $ringkasan->poinBertambah ? config('latihan.poin_per_soal') : 0 }}</span></div>
            </div>
            <div class="lt-stat lt-stat-indigo">
                <span class="lt-pita">Akurasi</span>
                <div><img src="{{ asset('img/ikon-bintang.svg') }}" alt="" width="34" height="34"><span data-efek-skor>{{ str_replace('.', ',', (string) $skor) }}</span>%</div>
            </div>
            <div class="lt-stat lt-stat-sogan">
                <span class="lt-pita">Streak</span>
                <div><img src="{{ asset('img/ikon-blencong.svg') }}" alt="" width="34" height="34"><span data-efek-denyut>{{ $ringkasan->streak }}</span> hari</div>
            </div>
        </div>
        @unless ($ringkasan->poinBertambah)
            <p class="lt-keterangan">Soal ini sudah pernah kamu selesaikan.</p>
        @endunless

        @foreach ($ringkasan->badgeBaru as $kode)
            <div class="lt-badge" data-efek-badge="{{ \App\Models\MotivasiBelajar::BADGE[$kode] }}">
                <img src="{{ asset('img/ikon-gunungan.svg') }}" alt="" width="44" height="44">
                <div>
                    <span class="lt-label">Badge baru</span>
                    <strong>{{ \App\Models\MotivasiBelajar::BADGE[$kode] }}</strong>
                    <span>{{ \App\Latihan\PemeriksaBadge::DESKRIPSI[$kode] }}</span>
                </div>
            </div>
        @endforeach
    </main>

    <footer class="lt-bilah-bawah">
        <form method="POST" action="{{ route('latihan.ulangi', [$idMateri, $mode]) }}">
            @csrf
            <button class="lt-tombol lt-tombol-sekunder">Ulangi latihan</button>
        </form>
        @if ($urlTugas)
            <a href="{{ $urlTugas }}" class="lt-tombol lt-tombol-utama">
                <img src="{{ asset('img/ikon-panah-putih.svg') }}" alt="" width="20" height="20">Kembali ke tugas
            </a>
        @else
            <form method="POST" action="{{ route('latihan.lanjut', [$idMateri, $mode]) }}">
                @csrf
                <button class="lt-tombol lt-tombol-utama lt-tombol-lanjut">
                    <img src="{{ asset('img/ikon-panah-putih.svg') }}" alt="" width="20" height="20">Lanjut
                </button>
            </form>
        @endif
    </footer>
</div>
@endsection
