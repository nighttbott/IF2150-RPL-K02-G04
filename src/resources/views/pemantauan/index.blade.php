{{--
    I-17 Pantau Progres Anggota (bagian HalamanKelolaKelas untuk UC-08, pemilik: Natan).
    Data disiapkan PemantauanKelasController::tampilkan(). Hanya anggota aktif yang tampil.
--}}
@extends('layouts.app')

@section('judul', 'Pantau Progres Anggota')

@section('konten')
@if (session('status'))
    <div class="pesan pesan-berhasil mb-4">{{ session('status') }}</div>
@endif
<div class="row g-4">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
            <div>
                <h1 class="mb-1">Pantau Progres Anggota</h1>
                <p class="teks-redup mb-0">{{ $kelas->nama }} · hanya anggota aktif yang ditampilkan.</p>
            </div>
            <form method="get" action="{{ route('pemantauan.index', $kelas->id_kelas) }}">
                <label class="d-flex flex-column">
                    <span class="label-kecil mb-1">Periode</span>
                    <select name="periode" class="form-select" onchange="this.form.submit()">
                        @foreach (\App\Http\Controllers\ProgresController::PILIHAN_PERIODE as $nilai => $label)
                            <option value="{{ $nilai }}" @selected($pilihanPeriode === (string) $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </form>
        </div>

        {{-- I-17-01 Ringkasan kelas --}}
        <div class="row g-3 mb-4">
            <div class="col"><div class="kartu"><div class="judul fs-3">{{ $ringkasan['jumlahAnggota'] }}</div><div class="small teks-redup fw-bold">anggota aktif</div></div></div>
            <div class="col"><div class="kartu"><div class="judul fs-3">{{ $ringkasan['rataPenguasaan'] !== null ? rtrim(rtrim(number_format($ringkasan['rataPenguasaan'], 1), '0'), '.').'%' : '-' }}</div><div class="small teks-redup fw-bold">rata-rata skor</div></div></div>
            <div class="col"><div class="kartu"><div class="judul fs-3">{{ $ringkasan['jumlahLatihan'] }}</div><div class="small teks-redup fw-bold">latihan dikerjakan</div></div></div>
        </div>

        @if ($ringkasan['kosong'])
            {{-- I-17-07 Belum ada riwayat (UC-08 alternatif 1) --}}
            <div class="pesan pesan-info mb-4">Belum ada riwayat latihan anggota pada periode ini.</div>
        @endif

        {{-- I-17-02 Progres tiap anggota --}}
        <section class="kartu mb-4">
            <h3 class="h5">Progres anggota</h3>
            @if ($anggota === [])
                <p class="teks-redup mb-0">Kelas ini belum memiliki anggota aktif.</p>
            @else
                <div class="table-responsive">
                    <table class="tabel-nk">
                        <thead><tr><th>Nama</th><th>Rata-rata skor</th><th>Latihan</th><th>Terakhir aktif</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($anggota as $id => $nama)
                            @php $p = $ringkasan['perPelajar'][$id] ?? null; @endphp
                            <tr>
                                <td><span class="avatar me-2">{{ mb_strtoupper(mb_substr($nama, 0, 1)) }}</span>{{ $nama }}</td>
                                <td style="min-width: 160px">
                                    @if ($p && $p['rataSkor'] !== null)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bilah flex-grow-1"><span class="{{ $p['rataSkor'] < 60 ? 'emas' : '' }}" style="width: {{ $p['rataSkor'] }}%"></span></div>
                                            <span>{{ rtrim(rtrim(number_format($p['rataSkor'], 1), '0'), '.') }}%</span>
                                        </div>
                                    @else
                                        <span class="teks-redup">-</span>
                                    @endif
                                </td>
                                <td>{{ $p['jumlahLatihan'] ?? 0 }}</td>
                                <td>{{ $p ? \Carbon\Carbon::parse($p['terakhir'])->diffForHumans() : '-' }}</td>
                                <td>
                                    {{-- I-17-03 Tombol detail (pilihAnggota) --}}
                                    <a class="tombol tombol-sekunder tombol-kecil" href="{{ route('pemantauan.index', ['idKelas' => $kelas->id_kelas, 'periode' => $pilihanPeriode, 'pelajar' => $id]) }}">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- Penguasaan per materi gabungan kelas --}}
        @if ($ringkasan['penguasaanMateri'] !== [])
            <section class="kartu">
                <h3 class="h5">Penguasaan kelas per materi</h3>
                @foreach ($ringkasan['penguasaanMateri'] as $idMateri => $nilai)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between"><span class="fw-bold">{{ $namaMateri[$idMateri] ?? 'Materi #'.$idMateri }}</span><span>{{ rtrim(rtrim(number_format($nilai, 1), '0'), '.') }}%</span></div>
                        <div class="bilah"><span class="{{ $nilai < 60 ? 'emas' : '' }}" style="width: {{ $nilai }}%"></span></div>
                    </div>
                @endforeach
            </section>
        @endif
    </div>

    <div class="col-lg-4">
        {{-- I-17-04 Aksara yang sering salah --}}
        <section class="kartu mb-4">
            <h3 class="h5">Aksara yang sering salah</h3>
            @forelse ($ringkasan['polaKesalahan'] as $target => $jumlah)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="aksara d-inline-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: var(--nk-merah-muda); color: var(--nk-merah-tua); font-size: 1.3rem">{{ $target }}</span>
                    <span class="flex-grow-1 fw-bold">{{ \App\Support\Aksara::latin($target) }}</span>
                    <span class="teks-redup fw-bold">{{ $jumlah }}x</span>
                </div>
            @empty
                <p class="small teks-redup mb-0">Belum ada pola kesalahan.</p>
            @endforelse
        </section>

        @if ($detail)
            @if (! $detail->berhasil)
                {{-- I-17-08 Bukan anggota aktif (UC-08 alternatif 2) --}}
                <div class="pesan pesan-gagal mb-4">{{ $detail->pesan }}</div>
            @else
                {{-- I-17-05 Detail anggota --}}
                <section class="kartu mb-4">
                    <h3 class="h5">Detail: {{ $detail->ambil('namaTampilan') }}</h3>
                    <p class="small teks-redup">{{ $detail->ambil('ringkasan')['jumlahLatihan'] }} latihan pada periode ini</p>
                    @forelse ($detail->ambil('riwayatTerbaru') as $r)
                        <div class="d-flex justify-content-between small border-top py-2">
                            <span>{{ $r->tanggal_pengerjaan->translatedFormat('d M') }} · {{ $r->materi?->nama_materi }}</span>
                            <span>@include('progres.label-status', ['status' => $r->status])</span>
                        </div>
                    @empty
                        <p class="small teks-redup mb-0">Belum ada riwayat.</p>
                    @endforelse
                </section>

                {{-- I-17-06 Umpan balik untuk anggota (kirimUmpanBalik) --}}
                <section class="kartu">
                    <h3 class="h5">Umpan balik untuk {{ $detail->ambil('namaTampilan') }}</h3>
                    <form method="post" action="{{ route('pemantauan.umpan-balik', $kelas->id_kelas) }}">
                        @csrf
                        <input type="hidden" name="id_pelajar" value="{{ $detail->ambil('idPelajar') }}">
                        <textarea name="pesan" class="form-control mb-2" rows="3" maxlength="1000" required>{{ old('pesan') }}</textarea>
                        @error('pesan') <div class="pesan pesan-gagal small mb-2">{{ $message }}</div> @enderror
                        <button class="tombol tombol-teal tombol-kecil">Kirim umpan balik</button>
                    </form>
                </section>
            @endif
        @endif
    </div>
</div>
@endsection
