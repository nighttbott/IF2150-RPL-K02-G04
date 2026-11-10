{{--
  I-01 Pendaftaran (HalamanPendaftaran, C05) — UC-01.
  Nama isian yang dikirim: peran, email, username, password, setuju_tnc.
  Galat konflik email/username (I-01-09) dibaca dari kunci galat 'akun';
  galat format per isian dibaca dari kunci isian masing-masing (Algo-009).
--}}
@extends('layouts.tamu')

@section('judul', 'Daftar · Ngaksara')
@section('ucapan', 'Sugeng rawuh! Aku Pak Aksa. Ayo sinau aksara Jawa bareng.')

@section('konten')
<div class="kartu nk-kartu-tamu nk-kartu-daftar">
    <header>
        <h1 class="nk-judul-tamu">Buat akun baru</h1>
        <p class="teks-redup mb-0">Pilih peranmu, lalu isi data di bawah ini.</p>
    </header>

    <form method="POST" action="{{ route('daftar') }}" id="form-daftar" novalidate class="nk-form-tamu">
        @csrf

        {{-- I-01-01 Pilihan peran --}}
        <fieldset>
            <legend class="nk-label">Daftar sebagai</legend>
            @php($peran = old('peran', 'pelajar'))
            <div class="nk-pilihan-peran">
                <label class="nk-kartu-peran">
                    <input type="radio" name="peran" value="pelajar" @checked($peran === 'pelajar')>
                    <img src="{{ asset('img/ikon-buku.svg') }}" alt="" width="30" height="30">
                    <span class="nama">Pelajar</span>
                    <span class="ket">Belajar dan berlatih aksara</span>
                </label>
                <label class="nk-kartu-peran">
                    <input type="radio" name="peran" value="pengajar" @checked($peran === 'pengajar')>
                    <img src="{{ asset('img/ikon-joglo.svg') }}" alt="" width="30" height="30">
                    <span class="nama">Pengajar</span>
                    <span class="ket">Membuat kelas dan memantau</span>
                </label>
            </div>
            @error('peran')<div class="nk-galat-isian">{{ $message }}</div>@enderror
        </fieldset>

        {{-- I-01-02 Email --}}
        <div>
            <label for="email" class="nk-label">Email</label>
            <div @class(['nk-isian', 'galat' => $errors->has('email')])>
                <img src="{{ asset('img/ikon-surat.svg') }}" alt="" width="18" height="18">
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="nama@contoh.com" autocomplete="email" required>
            </div>
            @error('email')<div class="nk-galat-isian">{{ $message }}</div>@enderror
        </div>

        {{-- I-01-03 Username --}}
        <div>
            <label for="username" class="nk-label">Username</label>
            <div @class(['nk-isian', 'galat' => $errors->has('username')])>
                <img src="{{ asset('img/ikon-orang.svg') }}" alt="" width="18" height="18">
                <input type="text" id="username" name="username" value="{{ old('username') }}"
                       placeholder="sari_belajar" autocomplete="username" required>
            </div>
            @error('username')<div class="nk-galat-isian">{{ $message }}</div>@enderror
        </div>

        {{-- I-01-04 Kata sandi (tidak pernah diisi ulang dari old()) --}}
        <div>
            <label for="password" class="nk-label">Kata sandi</label>
            <div @class(['nk-isian', 'galat' => $errors->has('password')])>
                <img src="{{ asset('img/ikon-kunci.svg') }}" alt="" width="18" height="18">
                <input type="password" id="password" name="password"
                       placeholder="Minimal 8 karakter" autocomplete="new-password" required>
            </div>
            @error('password')<div class="nk-galat-isian">{{ $message }}</div>@enderror
        </div>

        {{-- I-01-05 Syarat & Ketentuan --}}
        <div>
            <span class="nk-label">Syarat &amp; Ketentuan</span>
            <ol class="nk-syarat">
                <li>Data yang disimpan hanya email, username, dan nama tampilan.</li>
                <li>Riwayat belajar dapat dilihat Pengajar kelas yang kamu ikuti.</li>
                <li>Kata sandi disimpan dalam bentuk hash.</li>
            </ol>
        </div>

        {{-- I-01-06 Persetujuan --}}
        <div>
            <label class="nk-centang">
                <input type="checkbox" name="setuju_tnc" value="1" id="setuju_tnc" @checked(old('setuju_tnc'))>
                <span class="kotak" aria-hidden="true">
                    <img src="{{ asset('img/ikon-centang.svg') }}" alt="" width="16" height="16">
                </span>
                <span>Saya sudah membaca dan menyetujui Syarat &amp; Ketentuan</span>
            </label>
            <div class="nk-galat-isian" id="galat-tnc" @if (! $errors->has('setuju_tnc')) hidden @endif>
                {{ $errors->first('setuju_tnc') ?: 'Setujui Syarat & Ketentuan untuk melanjutkan.' }}
            </div>
        </div>

        {{-- I-01-09 Galat akun sudah terdaftar --}}
        @error('akun')
            <div class="nk-pesan-galat" role="alert">
                <span class="ikon"><img src="{{ asset('img/ikon-silang.svg') }}" alt="" width="16" height="16"></span>
                <div>
                    <strong>Email sudah terdaftar</strong>
                    <span>{{ $message }}</span>
                </div>
            </div>
        @enderror

        {{-- I-01-07 Daftar --}}
        <button type="submit" class="tombol tombol-utama nk-tombol-penuh" id="tombol-daftar">Daftar</button>

        {{-- I-01-08 Tautan masuk --}}
        <p class="nk-tautan-tamu">Sudah punya akun? <a href="{{ route('masuk') }}">Masuk</a></p>
    </form>
</div>
@endsection

@push('skrip')
<script>
    // Algo-005: pemeriksaan cepat di browser. Pemeriksaan sebenarnya tetap di server (Algo-009).
    (() => {
        const form = document.getElementById('form-daftar');
        const tnc = document.getElementById('setuju_tnc');
        const galatTnc = document.getElementById('galat-tnc');
        const tombol = document.getElementById('tombol-daftar');

        tnc.addEventListener('change', () => { if (tnc.checked) galatTnc.hidden = true; });

        form.addEventListener('submit', (e) => {
            if (!tnc.checked) {
                e.preventDefault();
                galatTnc.textContent = 'Setujui Syarat & Ketentuan untuk melanjutkan.';
                galatTnc.hidden = false;
                return;
            }
            const kosong = [...form.querySelectorAll('input[required]')].filter(i => !i.value.trim());
            form.querySelectorAll('.nk-isian').forEach(k => k.classList.remove('galat'));
            if (kosong.length) {
                e.preventDefault();
                kosong.forEach(i => i.closest('.nk-isian').classList.add('galat'));
                kosong[0].focus();
                return;
            }
            tombol.disabled = true; // mencegah pengiriman ganda
        });
    })();
</script>
@endpush