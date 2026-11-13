{{--
  Bagian bersama halaman Kelas Pelajar (I-15, I-16): pesan sesi dan gaya halaman.
  Dipanggil dengan @include('kelas-pelajar.layout') di awal @section('konten').
  Kerangka (sidebar, font, token warna) datang dari layouts.app + app.css.
--}}
@if (session('sukses'))
    <div class="pesan pesan-berhasil mb-3" role="status">{{ session('sukses') }}</div>
@endif
@if ($errors->any())
    <div class="pesan pesan-gagal mb-3" role="alert">
        @foreach ($errors->all() as $galat)
            <div>{{ $galat }}</div>
        @endforeach
    </div>
@endif

@once
@push('gaya')
<style>
    .nk-kelas-halaman h1 { font-size: 34px; line-height: 40px; margin: 0 0 4px; }
    .nk-kelas-halaman h2 { font-size: 22px; line-height: 28px; margin: 0 0 16px; }
    .nk-kelas { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 32px; align-items: start; margin-top: 24px; }
    .nk-kelas-tips img { width: 85px; flex: none; object-fit: contain; }
    .nk-kelas-tips p { margin: 0; }
    .nk-baris { display: flex; gap: 16px; align-items: center; justify-content: space-between; padding: 16px 0; border-bottom: 2px solid #F2ECE2; }
    .nk-baris:last-child { border-bottom: 0; padding-bottom: 0; }
    .nk-baris strong { display: block; }
    .nk-baris small { display: block; margin-top: 4px; color: var(--nk-redup); }
    .nk-instruksi { white-space: pre-wrap; overflow-wrap: anywhere; line-height: 1.6; }
    .tombol:disabled { opacity: .5; cursor: not-allowed; }
    @media (max-width: 1099.98px) {
        .nk-kelas { grid-template-columns: minmax(0, 1fr); }
        .nk-kelas-tips { max-width: 500px; }
    }
    @media (max-width: 575.98px) {
        .nk-baris { flex-wrap: wrap; }
        .nk-gabung { flex-wrap: wrap; }
        .nk-gabung .nk-isian { flex-basis: 100%; }
    }
</style>
@endpush
@endonce