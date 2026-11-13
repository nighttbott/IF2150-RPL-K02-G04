@extends('layouts.app')
@section('judul', 'Kelas Saya · Ngaksara')

@section('konten')
<div class="nk-kelas-halaman">
    @include('kelas-pelajar.layout')

    <h1>Kelas Saya</h1>
    <p class="teks-redup mb-0">Gabung ke kelas dengan kode dari Pengajar, lalu kerjakan tugasnya.</p>

    <div class="nk-kelas">
        <section class="d-flex flex-column gap-4">
            <div class="kartu">
                <h2>Gabung kelas</h2>
                <form class="nk-gabung d-flex align-items-center gap-3" method="post" action="{{ route('kelas-pelajar.gabung') }}">
                    @csrf
                    <label for="kode" class="visually-hidden">Kode kelas</label>
                    <div class="nk-isian flex-grow-1">
                        <input id="kode" name="kode" value="{{ old('kode') }}" placeholder="Kode kelas, contoh: K7M2QX"
                               maxlength="6" minlength="6" pattern="[A-Za-z0-9]{6}" required autocomplete="off">
                    </div>
                    <button class="tombol tombol-utama">Gabung</button>
                </form>
            </div>

            @if ($daftar->isNotEmpty())
                <nav class="d-flex flex-wrap gap-2" aria-label="Kelas yang diikuti">
                    @foreach ($daftar as $item)
                        @php($terpilih = (int) $kelas === (int) $item->id_kelas)
                        <a href="{{ route('kelas-pelajar.kelas', $item->id_kelas) }}"
                           @class(['tombol', 'tombol-teal' => $terpilih, 'tombol-sekunder' => ! $terpilih])
                           @if ($terpilih) aria-current="page" @endif>{{ $item->nama }}</a>
                    @endforeach
                </nav>
            @endif

            <div class="kartu">
                <h2>Tugas aktif <span class="label label-netral align-middle">{{ $tugas->count() }} tugas</span></h2>
                @forelse ($tugas as $t)
                    @php($s = $status[$t->id_tugas]['status'])
                    <div class="nk-baris">
                        <div>
                            <strong>{{ \Illuminate\Support\Str::limit($t->instruksi, 65) }}</strong>
                            <small>Tenggat {{ $t->tenggat->format('d M Y, H:i') }} WIB</small>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span @class(['label', 'label-berhasil' => $s === 'tepat_waktu', 'label-gagal' => $s === 'terlambat', 'label-netral' => ! in_array($s, ['tepat_waktu', 'terlambat'], true)])>
                                {{ $s === 'belum_selesai' ? 'Belum dikumpulkan' : str_replace('_', ' ', $s) }}
                            </span>
                            <a class="tombol tombol-sekunder tombol-kecil" href="{{ route('kelas-pelajar.detail', [$kelas, $t->id_tugas]) }}">Buka</a>
                        </div>
                    </div>
                @empty
                    <p class="teks-redup mb-0">{{ $daftar->isEmpty() ? 'Belum ada kelas yang diikuti. Masukkan kode dari Pengajar.' : 'Belum ada tugas yang diterbitkan di kelas ini.' }}</p>
                @endforelse
            </div>
        </section>

        <aside class="kartu kartu-emas nk-kelas-tips d-flex gap-3">
            <img src="{{ asset('img/pak-aksa-berpikir.svg') }}" alt="">
            <div>
                <h2>Kata Pak Aksa</h2>
                <p>Tugas yang lewat tenggat tetap bisa dikerjakan. Status pengumpulan mengikuti waktu seluruh komponen wajib selesai.</p>
            </div>
        </aside>
    </div>
</div>
@endsection