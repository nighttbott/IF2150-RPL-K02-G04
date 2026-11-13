{{-- I-20 Kirim Feedback (C10 HalamanFeedback). --}}
@extends('layouts.app')
@section('judul', 'Kirim Feedback · Ngaksara')

@section('konten')
@php
    $kategoriLama = old('kategori');

    // Pesan gagal dari controller berupa satu kalimat panjang; kalimat pertama jadi judul.
    $galat = session('galat');
    $judulGalat = $galat ? \Illuminate\Support\Str::before($galat, '.') : null;
    $rincianGalat = ($galat && str_contains($galat, '.')) ? trim(\Illuminate\Support\Str::after($galat, '.')) : null;
@endphp

<div class="nk-feedback">
    <div class="nk-feedback-utama">
        <h1 class="nk-feedback-judul">Kirim Feedback</h1>
        <p class="teks-redup mb-4">Sampaikan masukan tentang aplikasi atau materi.</p>

        <form method="POST" action="{{ route('feedback.kirim') }}" class="kartu d-flex flex-column gap-3" id="form-feedback">
            @csrf

            {{-- I-20-01 Pilihan kategori --}}
            <fieldset class="m-0 p-0 border-0">
                <legend class="label-kecil float-none w-auto mb-2 p-0">Kategori</legend>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($kategori as $k)
                        <input type="radio" class="nk-chip-radio visually-hidden" name="kategori"
                               id="kategori-{{ $k->value }}" value="{{ $k->value }}" required
                               @checked($kategoriLama === $k->value)>
                        <label for="kategori-{{ $k->value }}" class="nk-chip">{{ ucfirst(strtolower($k->name)) }}</label>
                    @endforeach
                </div>
                @error('kategori') <div class="nk-galat-isian">{{ $message }}</div> @enderror
            </fieldset>

            {{-- I-20-02 Isian pesan --}}
            <div>
                <label for="pesan" class="nk-label">Pesan</label>
                <textarea id="pesan" name="pesan" rows="6" minlength="10" maxlength="1000" required
                          @class(['nk-isian-pesan', 'galat' => $errors->has('pesan')])
                          placeholder="Tulis masukanmu di sini (10–1.000 karakter)."
                          @error('pesan') aria-invalid="true" aria-describedby="galat-pesan" @enderror>{{ old('pesan') }}</textarea>
                @error('pesan') <div class="nk-galat-isian" id="galat-pesan">{{ $message }}</div> @enderror
            </div>

            {{-- I-20-03 Tombol kirim --}}
            <div>
                <button type="submit" class="tombol tombol-utama nk-tombol-kirim" id="tombol-kirim">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>
                    </svg>
                    Kirim
                </button>
            </div>

            {{-- I-20-04 Konfirmasi berhasil --}}
            @if (session('sukses'))
                <div class="nk-pesan-ikon nk-pesan-ikon-berhasil" role="status">
                    <span class="ikon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                    </span>
                    <strong>{{ session('sukses') }}</strong>
                </div>
            @endif

            {{-- I-20-05 Gagal terkirim (UC-09 Alternatif 1) --}}
            @if ($galat)
                <div class="nk-pesan-ikon nk-pesan-ikon-gagal" role="alert">
                    <span class="ikon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                    </span>
                    <div>
                        <strong>{{ $judulGalat }}</strong>
                        @if ($rincianGalat) <span>{{ $rincianGalat }}</span> @endif
                    </div>
                </div>
            @endif
        </form>
    </div>

    {{-- Kolom samping: Pak Aksa --}}
    <aside class="nk-feedback-samping">
        <div class="kartu kartu-emas d-flex flex-column align-items-center gap-2">
            {{-- Path sementara; ganti bila aset Pak Aksa untuk I-20 berbeda --}}
            <img src="{{ asset('img/pak-aksa-melambai.svg') }}" alt="Pak Aksa" width="170" height="187">
            <div class="nk-gelembung nk-feedback-gelembung">Matur nuwun! Masukanmu membantu Ngaksara jadi lebih baik.</div>
        </div>
    </aside>
</div>
@endsection

@push('gaya')
<style>
    /* Kolom utama 716px + kolom samping 340px, jarak 32px (Figma 90:2248) */
    .nk-feedback { display: grid; grid-template-columns: minmax(0, 716px) 340px; gap: 32px; align-items: start; }
    .nk-feedback-judul { font-size: 34px; line-height: 40px; margin: 0 0 4px; }
    @media (max-width: 1199.98px) {
        .nk-feedback { grid-template-columns: minmax(0, 1fr); }
        .nk-feedback-samping { max-width: 340px; }
    }

    /* Chip kategori: teal saat dipilih */
    .nk-chip { display: inline-flex; align-items: center; padding: 7px 14px; background: #fff; border: 2px solid var(--nk-garis); border-radius: 12px; box-shadow: 0 3px 0 var(--nk-garis); font-family: 'Baloo 2', sans-serif; font-weight: 700; font-size: 15px; line-height: 20px; color: var(--nk-redup); cursor: pointer; }
    .nk-chip-radio:checked + .nk-chip { background: var(--nk-teal); border-color: var(--nk-teal); color: #fff; box-shadow: 0 3px 0 var(--nk-teal-tua); }
    .nk-chip-radio:focus-visible + .nk-chip { outline: 3px solid var(--nk-emas); outline-offset: 2px; }

    /* Isian pesan */
    .nk-isian-pesan { display: block; width: 100%; min-height: 150px; resize: vertical; background: #fff; border: 2px solid var(--nk-garis); border-radius: 14px; padding: 12px 16px; font-weight: 500; font-size: 15px; line-height: 20px; color: var(--nk-tinta); outline: 0; }
    .nk-isian-pesan::placeholder { color: #B9AE9E; }
    .nk-isian-pesan:focus { border-color: var(--nk-indigo); }
    .nk-isian-pesan.galat { border-color: var(--nk-merah); background: var(--nk-merah-muda); }

    .nk-tombol-kirim { padding: 14px 24px; font-size: 17px; line-height: 22px; }
    .nk-tombol-kirim:disabled { opacity: .6; cursor: not-allowed; }

    /* Pesan berikon (pola .nk-pesan-galat, ditambah varian hijau) */
    .nk-pesan-ikon { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 2px solid; border-radius: 14px; }
    .nk-pesan-ikon .ikon { width: 28px; height: 28px; flex: none; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; }
    .nk-pesan-ikon strong { display: block; font-weight: 800; font-size: 15px; line-height: 20px; }
    .nk-pesan-ikon div > span { display: block; margin-top: 2px; font-size: 14px; line-height: 19px; font-weight: 500; }
    .nk-pesan-ikon-berhasil { background: var(--nk-hijau-muda); border-color: var(--nk-hijau); color: var(--nk-hijau-tua); }
    .nk-pesan-ikon-berhasil .ikon { background: var(--nk-hijau); }
    .nk-pesan-ikon-gagal { background: var(--nk-merah-muda); border-color: var(--nk-merah); color: var(--nk-merah-tua); }
    .nk-pesan-ikon-gagal .ikon { background: var(--nk-merah); }

    /* Kartu Pak Aksa */
    .nk-feedback-gelembung { width: 100%; max-width: 280px; }
</style>
@endpush

@push('skrip')
<script>
// Cegah kiriman ganda saat tombol diklik berulang.
document.getElementById('form-feedback').addEventListener('submit', () => {
    document.getElementById('tombol-kirim').disabled = true;
});
</script>
@endpush