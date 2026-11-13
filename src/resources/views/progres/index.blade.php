{{-- I-11 Progres Pelajar (kelas C14 HalamanProgres, pemilik: Natan). Data disiapkan ProgresController::tampilkan(). Nomor objek I-11-xx mengikuti BAB 6 DPPL2. --}}
@extends('layouts.app')

@section('judul', 'Progres Belajar')

@section('konten')
<div class="row g-4">
    <div class="col-lg-8">
        {{-- I-11-01 Pilihan periode (pilihPeriode) --}}
        <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
            <div>
                <h1 class="mb-1">Progres Belajar</h1>
                <p class="teks-redup mb-0">Ringkasan penguasaan, riwayat latihan, dan pencapaianmu.</p>
            </div>
            <form method="get" action="{{ route('progres.index') }}" class="d-flex align-items-end gap-2">
                <label class="d-flex flex-column">
                    <span class="label-kecil mb-1">Periode</span>
                    <select name="periode" class="form-select" onchange="this.form.submit()">
                        @foreach (\App\Http\Controllers\ProgresController::PILIHAN_PERIODE as $nilai => $label)
                            <option value="{{ $nilai }}" @selected($pilihanPeriode === (string) $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <noscript><button class="tombol tombol-sekunder tombol-kecil">Terapkan</button></noscript>
            </form>
        </div>

        @if ($ringkasan['kosong'])
            {{-- I-11-08 Keadaan belum ada riwayat (UC-04 alternatif 1) --}}
            <div class="pesan pesan-info mb-4">
                Belum ada riwayat latihan pada periode ini. Mulai dari materi rekomendasi di samping, ya.
            </div>
        @endif

        {{-- I-11-02 Penguasaan per materi (tampilkanProgres) --}}
        <section class="kartu mb-4">
            <h3 class="h5">Penguasaan per materi</h3>
            @forelse ($ringkasan['penguasaan'] as $baris)
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <a class="fw-bold text-decoration-none" style="color: var(--nk-tinta)"
                           href="{{ route('progres.index', ['periode' => $pilihanPeriode, 'materi' => $baris['id_materi']]) }}">{{ $baris['nama_materi'] }}</a>
                        <span class="fw-bold">{{ rtrim(rtrim(number_format($baris['nilai'], 1), '0'), '.') }}%</span>
                    </div>
                    <div class="bilah"><span class="{{ $baris['nilai'] < 60 ? 'emas' : '' }}" style="width: {{ $baris['nilai'] }}%"></span></div>
                </div>
            @empty
                <p class="teks-redup mb-0">Penguasaan akan muncul setelah kamu mengerjakan latihan.</p>
            @endforelse
        </section>

        {{-- Detail hasil satu materi (pilihMateriRiwayat, tampilkanDetailHasil) --}}
        @if ($detail !== null)
            <section class="kartu mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h3 class="h5 mb-0">Detail hasil: {{ $materiDetail?->nama_materi ?? 'Materi' }}</h3>
                    <a class="small" href="{{ route('progres.index', ['periode' => $pilihanPeriode]) }}">Tutup</a>
                </div>
                @if ($detail->isEmpty())
                    <p class="teks-redup mb-0">Belum ada hasil pada materi ini.</p>
                @else
                    <table class="tabel-nk">
                        <thead><tr><th>Tanggal</th><th>Soal</th><th>Mode</th><th>Skor</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($detail as $r)
                            <tr>
                                <td>{{ $r->tanggal_pengerjaan->translatedFormat('d M Y H.i') }}</td>
                                <td class="aksara">{{ $r->target_soal ?? '-' }}</td>
                                <td>{{ ucfirst($r->mode ?? $r->mode_aktivitas) }}</td>
                                <td>{{ $r->skor !== null ? rtrim(rtrim(number_format($r->skor, 2), '0'), '.') : '-' }}</td>
                                <td>@include('progres.label-status', ['status' => $r->status])</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        @endif

        {{-- I-11-03 Riwayat latihan --}}
        <section class="kartu">
            <h3 class="h5">Riwayat latihan</h3>
            @if ($riwayat->isEmpty())
                <p class="teks-redup mb-0">Tidak ada riwayat pada periode ini.</p>
            @else
                <div class="table-responsive">
                    <table class="tabel-nk">
                        <thead><tr><th>Tanggal</th><th>Materi</th><th>Mode</th><th>Skor</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($riwayat->take(20) as $r)
                            <tr>
                                <td>{{ $r->tanggal_pengerjaan->translatedFormat('d M') }}</td>
                                <td>{{ $r->materi?->nama_materi ?? '-' }}</td>
                                <td>{{ ucfirst($r->mode ?? $r->mode_aktivitas) }}</td>
                                <td>{{ $r->skor !== null ? rtrim(rtrim(number_format($r->skor, 2), '0'), '.') : '-' }}</td>
                                <td>@include('progres.label-status', ['status' => $r->status])</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($riwayat->count() > 20)
                    <p class="small teks-redup mt-2 mb-0">Menampilkan 20 dari {{ $riwayat->count() }} riwayat terbaru.</p>
                @endif
            @endif
        </section>
    </div>

    <div class="col-lg-4">
        {{-- I-11-04 Streak dan poin (tampilkanMotivasi) --}}
        <section class="kartu mb-4 text-center">
            <div class="row">
                <div class="col">
                    <img src="{{ asset('img/ikon-blencong.svg') }}" alt="" width="40" height="40">
                    <div class="judul fs-3" style="color: var(--nk-sogan)">{{ $motivasi['streak'] }}</div>
                    <div class="small teks-redup fw-bold">hari streak</div>
                </div>
                <div class="col">
                    <img src="{{ asset('img/ikon-gobog.svg') }}" alt="" width="40" height="40">
                    <div class="judul fs-3" style="color: var(--nk-emas-tua)">{{ $motivasi['poin'] }}</div>
                    <div class="small teks-redup fw-bold">poin</div>
                </div>
            </div>
        </section>

        {{-- I-11-05 Badge --}}
        <section class="kartu mb-4">
            <h3 class="h5">Badge</h3>
            <div class="d-flex justify-content-between text-center">
                @foreach ($motivasi['badge'] as $b)
                    <div class="flex-fill" style="opacity: {{ $b['dimiliki'] ? 1 : .35 }}">
                        <img src="{{ asset('img/ikon-gunungan.svg') }}" alt="" width="36" height="36">
                        <div class="small fw-bold">{{ $b['nama'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- I-11-06 Rekomendasi materi (tampilkanRekomendasi, bukaRekomendasi) --}}
        <section class="kartu kartu-emas mb-4">
            <div class="d-flex gap-3 align-items-start">
                <img src="{{ asset('img/pak-aksa-berpikir.svg') }}" alt="Pak Aksa" width="80">
                <div>
                    <h3 class="h5" style="color: var(--nk-sogan)">Rekomendasi</h3>
                    @forelse ($rekomendasi as $rek)
                        <div class="mb-2">
                            <div class="fw-bold">{{ $rek['materi']->nama_materi }}</div>
                            <div class="small">{{ $rek['penguasaan'] !== null ? 'Penguasaan '.rtrim(rtrim(number_format($rek['penguasaan'], 1), '0'), '.').'%' : 'Belum pernah dicoba' }}</div>
                            @if (Route::has('materi.show'))
                                <a class="tombol tombol-emas tombol-kecil mt-1" href="{{ route('materi.show', $rek['materi']->id_materi) }}">Buka materi</a>
                            @endif
                        </div>
                    @empty
                        <p class="small mb-0">Belum ada materi terbit untuk direkomendasikan.</p>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- I-11-07 Papan peringkat kelas (tampilkanScoreboard) --}}
        @if ($kelasDiikuti->isNotEmpty())
            <section class="kartu">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h3 class="h5 mb-0 d-flex align-items-center gap-2"><img src="{{ asset('img/ikon-mahkota.svg') }}" alt="" width="22"> Papan peringkat</h3>
                    @if ($kelasDiikuti->count() > 1)
                        <form method="get" action="{{ route('progres.index') }}">
                            <input type="hidden" name="periode" value="{{ $pilihanPeriode }}">
                            <select name="kelas" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach ($kelasDiikuti as $k)
                                    <option value="{{ $k->id_kelas }}" @selected($k->id_kelas === $idKelasTerpilih)>{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>
                @if ($scoreboard && $scoreboard['aktif'])
                    @foreach ($scoreboard['daftar'] as $p)
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3 {{ $p['id_pelajar'] === $idPelajar ? 'fw-bold' : '' }}" style="{{ $p['id_pelajar'] === $idPelajar ? 'background: var(--nk-indigo-muda)' : '' }}">
                            <span class="judul" style="width: 22px; color: {{ $p['peringkat'] === 1 ? 'var(--nk-emas-tua)' : 'var(--nk-redup)' }}">{{ $p['peringkat'] }}</span>
                            <span class="flex-grow-1">{{ $p['nama_tampilan'] }}</span>
                            <span class="teks-redup">{{ $p['poin'] }} poin</span>
                        </div>
                    @endforeach
                @else
                    <p class="small teks-redup mb-0">{{ $scoreboard['pesan'] ?? 'Papan peringkat tidak tersedia.' }}</p>
                @endif
            </section>
        @endif
    </div>
</div>
@endsection
