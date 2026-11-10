{{--
  I-02 Login (HalamanLogin, C06) - UC-01.
  Nama isian yang dikirim: email, password.
  Disesuaikan dengan OtentikasiController (C15):
  - session('galat')  : pesan umum I-02-05 (gagal atau terkunci)
  - session('tunggu') : sisa detik saat dibatasi RateLimiter, tombol nonaktif sampai habis
  - session('sukses') : pesan berhasil (setelah keluar / setelah mendaftar)
  - $errors email/password : hanya dari validasi "wajib diisi"
--}}
@extends('layouts.tamu')

@section('judul', 'Masuk · Ngaksara')
@section('ucapan', 'Sugeng rawuh maneh! Mlebu dhisik, ya.')

@section('konten')
<div class="kartu nk-kartu-tamu nk-kartu-masuk">
    <header>
        <h1 class="nk-judul-tamu">Masuk</h1>
        <p class="teks-redup mb-0">Lanjutkan belajarmu bersama Pak Aksa.</p>
    </header>

    {{-- Pesan berhasil (setelah keluar, atau dari I-01 setelah pendaftaran berhasil) --}}
    @if (session('sukses'))
        <div class="pesan pesan-berhasil" role="status">{{ session('sukses') }}</div>
    @endif

    <form method="POST" action="{{ action([\App\Http\Controllers\OtentikasiController::class, 'prosesMasuk']) }}" id="form-masuk" novalidate class="nk-form-tamu">
        @csrf

        {{-- I-02-01 Email (tetap terisi bila gagal) --}}
        <div>
            <label for="email" class="nk-label">Email</label>
            <div @class(['nk-isian', 'galat' => $errors->has('email')])>
                <img src="{{ asset('img/ikon-surat.svg') }}" alt="" width="18" height="18">
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="nama@contoh.com" autocomplete="email" required autofocus>
            </div>
            @error('email')<div class="nk-galat-isian">{{ $message }}</div>@enderror
        </div>

        {{-- I-02-02 Kata sandi (selalu kosong) --}}
        <div>
            <label for="password" class="nk-label">Kata sandi</label>
            <div @class(['nk-isian', 'galat' => $errors->has('password')])>
                <img src="{{ asset('img/ikon-kunci.svg') }}" alt="" width="18" height="18">
                <input type="password" id="password" name="password"
                       placeholder="••••••••" autocomplete="current-password" required>
            </div>
            @error('password')<div class="nk-galat-isian">{{ $message }}</div>@enderror
        </div>

        {{-- I-02-05 Galat umum / batas percobaan --}}
        @if (session('galat'))
            <div class="nk-pesan-galat" role="alert">
                <span class="ikon"><img src="{{ asset('img/ikon-silang.svg') }}" alt="" width="16" height="16"></span>
                <strong id="pesan-masuk">{{ session('galat') }}</strong>
            </div>
        @endif

        {{-- I-02-03 Masuk --}}
        <button type="submit" class="tombol tombol-utama nk-tombol-penuh" id="tombol-masuk"
                @if (session('tunggu')) disabled data-tunggu="{{ (int) session('tunggu') }}" @endif>
            Masuk
        </button>

        {{-- I-02-04 Tautan daftar --}}
        <p class="nk-tautan-tamu">Belum punya akun? <a href="{{ route('daftar') }}">Daftar</a></p>
    </form>
</div>
@endsection

@push('skrip')
<script>
    // Algo-006: isian wajib dicek di browser, tombol nonaktif saat memproses.
    (() => {
        const form = document.getElementById('form-masuk');
        const tombol = document.getElementById('tombol-masuk');

        form.addEventListener('submit', (e) => {
            const kosong = [...form.querySelectorAll('input[required]')].filter(i => !i.value.trim());
            form.querySelectorAll('.nk-isian').forEach(k => k.classList.remove('galat'));
            if (kosong.length) {
                e.preventDefault();
                kosong.forEach(i => i.closest('.nk-isian').classList.add('galat'));
                kosong[0].focus();
                return;
            }
            tombol.disabled = true;
        });

        // Status TERKUNCI: hitung mundur, lalu aktifkan tombol lagi.
        let sisa = parseInt(tombol.dataset.tunggu || '0', 10);
        const pesan = document.getElementById('pesan-masuk');
        if (sisa > 0) {
            const t = setInterval(() => {
                sisa -= 1;
                // Pertahankan teks dari controller, ganti angkanya saja.
                if (pesan) pesan.textContent = pesan.textContent.replace(/\d+ detik/, `${sisa} detik`);
                if (sisa <= 0) {
                    clearInterval(t);
                    tombol.disabled = false;
                    pesan?.closest('.nk-pesan-galat')?.remove();
                }
            }, 1000);
        }
    })();
</script>
@endpush