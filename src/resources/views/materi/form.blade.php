{{-- I-19 Unggah Materi (C09 HalamanKelolaMateri::unggahFileMateri / suntingMateri). Data: $materi (MateriAksara|null) --}}
@extends('layouts.app')
@section('judul', ($materi ? 'Sunting Materi' : 'Unggah Materi').' · Ngaksara')

@section('konten')
@php
    $sunting = $materi !== null;
    $nilaiJenis = old('jenis_aksara', $materi?->jenis_aksara->value ?? 'jawa');
    $nilaiTingkat = (int) old('tingkat_kesulitan', $materi?->tingkat_kesulitan ?? 1);
    $nilaiTeks = old('aturan_contoh', $materi?->konten ? \App\Support\Materi\PengolahKonten::gabungAturanContoh($materi->konten->aturan, $materi->konten->contoh) : '');
    $aksi = $sunting ? route('materi.perbarui', $materi->id_materi) : route('materi.simpan');
@endphp
<a href="{{ route('materi.kelola') }}" class="small text-decoration-none">← Kembali ke Kelola Materi</a>{{-- I-19 navigasi --}}
<h1 class="mt-2 mb-1">{{ $sunting ? 'Sunting Materi' : 'Unggah Materi' }}</h1>
<p class="teks-redup">Isi data materi dan unggah berkasnya.</p>

@if (session('galat'))
    <div class="pesan pesan-gagal mb-3">{{-- I-19-06 --}}
        {{ session('galat') }}. Pilih berkas lain atau perbaiki isian. Materi tidak berubah.
    </div>
@endif
@if ($errors->any())
    <div class="pesan pesan-gagal mb-3"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ $aksi }}" enctype="multipart/form-data" class="col-lg-8 p-0">
    @csrf
    @if ($sunting) @method('PUT') @endif

    <div class="kartu mb-3">
        <div class="mb-3">{{-- I-19-01 --}}
            <label for="nama_materi" class="label-kecil d-block mb-1">Nama materi</label>
            <input id="nama_materi" name="nama_materi" type="text" maxlength="100" required
                   class="form-control @error('nama_materi') is-invalid @enderror"
                   value="{{ old('nama_materi', $materi?->nama_materi) }}" placeholder="Nglegena: ma ga ba tha nga">
            <div class="small teks-redup mt-1">Tulis daftar aksara setelah tanda ":" (kode latin dipisah spasi) agar bentuk aksara terisi otomatis.</div>
        </div>

        <div class="d-flex flex-wrap gap-4 mb-3">{{-- I-19-02 --}}
            <div>
                <div class="label-kecil mb-1">Jenis aksara</div>
                <div class="d-flex gap-2">
                    @foreach (['jawa' => 'Jawa', 'sunda' => 'Sunda'] as $nilai => $label)
                        <input type="radio" class="pilih-radio visually-hidden" name="jenis_aksara" id="jenis-{{ $nilai }}" value="{{ $nilai }}" @checked($nilaiJenis === $nilai)>
                        <label class="tombol tombol-sekunder tombol-kecil" for="jenis-{{ $nilai }}">{{ $label }}</label>
                    @endforeach
                </div>
            </div>
            <div>
                <div class="label-kecil mb-1">Tingkat kesulitan</div>
                <div class="d-flex gap-2">
                    @foreach ([1, 2, 3] as $t)
                        <input type="radio" class="pilih-radio visually-hidden" name="tingkat_kesulitan" id="tingkat-{{ $t }}" value="{{ $t }}" @checked($nilaiTingkat === $t)>
                        <label class="tombol tombol-sekunder tombol-kecil" for="tingkat-{{ $t }}">{{ $t }}</label>
                    @endforeach
                </div>
            </div>
        </div>

        <div>{{-- I-19-03 --}}
            <label for="aturan_contoh" class="label-kecil d-block mb-1">Aturan penulisan dan contoh</label>
            <textarea id="aturan_contoh" name="aturan_contoh" rows="5" required
                      class="form-control @error('aturan_contoh') is-invalid @enderror"
                      placeholder='Tulis dari kiri ke kanan. Contoh: ꦩꦒ = "maga".'>{{ $nilaiTeks }}</textarea>
            <div class="small teks-redup mt-1">Bagian setelah kata "Contoh:" disimpan sebagai contoh materi.</div>
        </div>
    </div>

    <div class="kartu mb-3">{{-- I-19-04 --}}
        <label for="berkas" class="d-block text-center p-4" id="zona-berkas"
               style="border:2px dashed var(--nk-indigo);border-radius:18px;background:var(--nk-emas-muda);cursor:pointer">
            <div class="fw-bold mb-1">Seret berkas ke sini atau pilih berkas</div>
            <div class="small teks-redup mb-2">Format: json, mp3, png, svg. Maksimal 10 MB.</div>
            <span class="tombol tombol-sekunder tombol-kecil">Pilih berkas</span>
            <div id="nama-berkas" class="small mt-2 teks-redup"></div>
            <input id="berkas" name="berkas" type="file" accept=".json,.mp3,.png,.svg" class="visually-hidden">
        </label>
        <div class="small teks-redup mt-2">
            mp3 dipakai sebagai audio pengucapan. json (kunci bentukAksara, aturan, contoh) menggantikan isian konten.
            @if ($sunting) Kosongkan bila tidak ingin mengganti berkas. @endif
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('materi.kelola') }}" class="tombol tombol-sekunder">Batal</a>
        <button type="submit" class="tombol tombol-utama">{{ $sunting ? 'Simpan perubahan' : 'Simpan sebagai draf' }}</button>{{-- I-19-05 --}}
    </div>
</form>
@endsection

@push('gaya')
<style>
    .pilih-radio:checked + label { background: var(--nk-indigo); color: #fff; border-color: var(--nk-indigo); box-shadow: 0 3px 0 var(--nk-indigo-tua); }
    .pilih-radio:focus-visible + label { outline: 3px solid var(--nk-emas); }
</style>
@endpush

@push('skrip')
<script>
document.getElementById('berkas').addEventListener('change', e => {
    const f = e.target.files[0];
    document.getElementById('nama-berkas').textContent = f ? `${f.name} (${(f.size / 1048576).toFixed(2)} MB)` : '';
});
</script>
@endpush
