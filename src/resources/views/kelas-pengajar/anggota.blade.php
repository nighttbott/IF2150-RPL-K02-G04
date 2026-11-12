@extends('layouts.app')

@section('judul', 'Kelola Anggota')

@push('gaya') @include('kelas-pengajar.gaya') @endpush

@section('konten')
<a href="{{ route('pengajar.kelas.index') }}" class="small text-decoration-none">← Kembali ke Kelola Kelas</a>
<h1 class="h3 fw-bold mb-1 mt-2">Kelola Anggota</h1>
<p class="teks-redup">Kelas {{ $kelas->nama }} · {{ count($anggota) }} anggota aktif</p>

@if (session('sukses'))
    <div class="pesan pesan-berhasil mb-3">
        <div class="fw-bold">{{ session('sukses')['judul'] }}</div>
        <div class="small">{{ session('sukses')['isi'] }}</div>
    </div>
@endif
@foreach (['anggota', 'scoreboard'] as $kunci)
    @error($kunci)
        <div class="pesan pesan-gagal mb-3">{{ $message }}</div>
    @enderror
@endforeach

<div class="row g-4">
    <div class="col-lg-8">
        <div class="kartu p-3 mb-3">
            <div class="fw-bold mb-2">Anggota aktif</div>
            <table class="tabel-nk align-middle">
                <thead>
                    <tr><th>NAMA</th><th>STATUS</th><th></th></tr>
                </thead>
                <tbody>
                @forelse ($anggota as $a)
                    <tr>
                        <td class="fw-semibold">{{ $a['namaTampilan'] }}</td>
                        <td><span class="label label-berhasil">Aktif</span></td>
                        <td class="text-end">
                            <button type="button" class="tombol tombol-kecil btn-keluarkan" style="background:var(--nk-merah);color:#fff"
                                    data-nama="{{ $a['namaTampilan'] }}"
                                    data-aksi="{{ route('pengajar.kelas.keluarkan', [$kelas->id_kelas, $a['idAkun']]) }}">
                                Keluarkan
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center teks-redup py-3">Belum ada anggota yang bergabung.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- I-16-03 dialog konfirmasi: tampil hanya setelah "Keluarkan" ditekan --}}
        <div id="konfirmasi" class="p-3 rounded-3 border border-danger-subtle bg-danger-subtle d-none">
            <div class="fw-bold text-danger" id="konfirmasi-judul"></div>
            <div class="small mb-2" id="konfirmasi-isi"></div>
            <form method="POST" id="konfirmasi-form" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger">Ya, keluarkan</button>
            </form>
            <button type="button" class="btn btn-sm btn-light border" id="konfirmasi-batal">Batal</button>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="kartu p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="fw-bold">Papan peringkat</div>
                <form method="POST" action="{{ route('pengajar.kelas.scoreboard', $kelas->id_kelas) }}">
                    @csrf
                    <input type="hidden" name="aktif" value="0">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" name="aktif" value="1"
                               @checked($kelas->status_scoreboard) onchange="this.form.submit()">
                    </div>
                </form>
            </div>
            <div class="small teks-redup mb-2">Bila aktif, anggota melihat urutan poin di halaman progres.</div>

            @if ($kelas->status_scoreboard)
                @forelse (array_slice($papan, 0, 3) as $p)
                    <div class="d-flex justify-content-between small py-1">
                        <span>{{ $p['peringkat'] }}. {{ $p['nama_tampilan'] }}</span>
                        <span class="fw-bold">{{ $p['poin'] }}</span>
                    </div>
                @empty
                    <div class="small teks-redup">Belum ada data poin.</div>
                @endforelse
            @else
                <div class="small teks-redup">Papan peringkat dinonaktifkan.</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('skrip')
<script>
    const panel = document.getElementById('konfirmasi');
    document.querySelectorAll('.btn-keluarkan').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            const nama = tombol.dataset.nama;
            document.getElementById('konfirmasi-judul').textContent = 'Keluarkan ' + nama + ' dari kelas?';
            document.getElementById('konfirmasi-isi').textContent =
                'Akses ' + nama + ' ke kelas berakhir. Riwayat belajarnya tidak dihapus.';
            document.getElementById('konfirmasi-form').action = tombol.dataset.aksi;
            panel.classList.remove('d-none');
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });
    document.getElementById('konfirmasi-batal').addEventListener('click', function () {
        panel.classList.add('d-none');
    });
</script>
@endpush
