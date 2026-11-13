{{-- I-09 Latihan Merangkai (Marvel). Data: $materi, $token, $soal (tanpa kunci dan tanpa kata benar) --}}
@extends('layouts.app')
@section('judul', 'Latihan Merangkai · '.$materi->nama_materi)

@section('konten')
<h1 class="mb-1">Latihan Merangkai</h1>
<p class="teks-redup">{{ $materi->nama_materi }}. Ketuk aksara untuk menyusun kata sesuai bunyinya. Ketuk lagi untuk melepas.</p>

<form method="POST" action="{{ route('latihan.soal.kirim', 'merangkai') }}" id="form-rangkai">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    @foreach ($soal as $n => $s)
        <div class="kartu mb-3 soal-rangkai" data-soal="{{ $s['id'] }}">
            <div class="label-kecil">Soal {{ $n + 1 }}</div>
            <p class="fs-5 mb-2">Susun kata dengan bunyi <strong>{{ $s['bunyi'] }}</strong></p>
            <div class="d-flex gap-2 flex-wrap mb-2 ubin-susun" style="min-height:64px;border:2px dashed var(--nk-garis);border-radius:14px;padding:8px" aria-label="Susunan Anda"></div>
            <div class="d-flex gap-2 flex-wrap ubin-sumber">
                @foreach ($s['ubin'] as $u)
                    <button type="button" class="tombol tombol-sekunder ubin aksara" data-id="{{ $u['id'] }}" style="font-size:2rem">{{ $u['teks'] }}</button>
                @endforeach
            </div>
            <div class="isian"></div>
        </div>
    @endforeach
    <noscript><div class="pesan pesan-gagal">Latihan ini membutuhkan JavaScript.</div></noscript>
    <button class="tombol tombol-utama">Kirim jawaban</button>
</form>
@endsection

@push('skrip')
<script>
document.querySelectorAll('.soal-rangkai').forEach(kartu => {
    const susun = kartu.querySelector('.ubin-susun');
    const sumber = kartu.querySelector('.ubin-sumber');
    const isian = kartu.querySelector('.isian');
    const idSoal = kartu.dataset.soal;

    // Isian tersembunyi selalu dibangun ulang dari urutan ubin pada area susun.
    const segarkan = () => {
        isian.innerHTML = '';
        susun.querySelectorAll('.ubin').forEach(u => {
            const i = document.createElement('input');
            i.type = 'hidden';
            i.name = `urutan[${idSoal}][]`;
            i.value = u.dataset.id;
            isian.appendChild(i);
        });
    };

    kartu.addEventListener('click', e => {
        const ubin = e.target.closest('.ubin');
        if (!ubin) return;
        (ubin.parentElement === sumber ? susun : sumber).appendChild(ubin);
        segarkan();
    });
});
</script>
@endpush
