{{-- I-07 Latihan Menulis (DPPL 6.7). Interaksi kanvas ada di resources/js/latihan/kanvas-menulis.js. --}}
@extends('latihan.layout-fokus', ['skrip' => ['resources/js/latihan/kanvas-menulis.js']])

@section('judul', 'Latihan menulis')

@section('isi')
<div class="lt-fokus">
    <header class="lt-bilah-atas">
        <a href="{{ route('latihan.mode', $soal->idMateri) }}" aria-label="Keluar dari latihan">
            <img src="{{ asset('img/ikon-silang-redup.svg') }}" alt="" width="26" height="26">
        </a>
        <div class="lt-kemajuan" role="progressbar" aria-label="Kemajuan latihan" aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $selesai }}">
            <span style="width: {{ $total ? round($selesai / $total * 100) : 0 }}%"></span>
        </div>
        <span class="lt-streak"><img src="{{ asset('img/ikon-blencong.svg') }}" alt="Streak" width="26" height="26">{{ $streak }}</span>
    </header>

    <main class="lt-isi">
        <div class="lt-instruksi">
            <div>
                <span class="lt-label">Latihan menulis</span>
                <h1>Tulis aksara "{{ $soal->latin }}"</h1>
                <p>Ikuti garis bantu, lalu tekan Periksa.</p>
            </div>
            <label class="lt-sakelar">
                <input type="checkbox" role="switch" id="outline" checked>
                <span aria-hidden="true"></span>
                Tampilkan outline
            </label>
        </div>

        <div class="lt-area">
            <canvas id="kanvas" class="lt-kanvas" aria-label="Kanvas menulis aksara {{ $soal->latin }}"
                data-id-soal="{{ $soal->idSoal }}"
                data-aksara="{{ $soal->targetSoal }}"
                data-url-nilai="{{ route('latihan.nilai') }}"
                data-url-selesai="{{ route('latihan.selesai') }}"></canvas>
            <div class="lt-samping">
                <div class="kartu lt-contoh">
                    <h2>Contoh</h2>
                    <span class="aksara">{{ $soal->targetSoal }}</span>
                    <p>Dibaca "{{ $soal->latin }}"</p>
                </div>
                <button type="button" id="bersihkan" class="lt-tombol lt-tombol-sekunder">
                    <img src="{{ asset('img/ikon-silang-indigo.svg') }}" alt="" width="20" height="20">Bersihkan
                </button>
            </div>
        </div>
    </main>

    <footer class="lt-bilah-bawah" id="bilah-hasil" data-keadaan="netral" aria-live="polite">
        <div class="lt-hasil">
            <span class="lt-lingkaran">
                <img src="{{ asset('img/ikon-silang-putih.svg') }}" alt="" width="28" height="28" data-ikon="perlu_diulang">
                <img src="{{ asset('img/ikon-centang-putih.svg') }}" alt="" width="28" height="28" data-ikon="berhasil">
            </span>
            <div>
                <strong id="hasil-judul"></strong>
                <span id="hasil-pesan"></span>
            </div>
        </div>
        <div class="lt-aksi">
            <button type="button" id="ulangi" class="lt-tombol lt-tombol-sekunder">Ulangi</button>
            <button type="button" id="muat-ulang" class="lt-tombol lt-tombol-sekunder" onclick="location.reload()">Muat ulang</button>
            <button type="button" id="periksa" class="lt-tombol lt-tombol-utama" disabled>Periksa</button>
        </div>
    </footer>
</div>
@endsection
