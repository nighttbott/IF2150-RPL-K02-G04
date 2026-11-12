{{-- Detail kelas (I-14 lanjutan). Data: $kelas, $jumlahAnggota, $daftarTugas, $scoreboard --}}
@extends('layouts.app')
@section('judul', $kelas->nama.' · Ngaksara')

@section('konten')
<a href="{{ route('kelas.pengajar.index') }}" class="teks-redup text-decoration-none">← Semua kelas</a>
<h1 class="mb-3 mt-1">{{ $kelas->nama }}</h1>

@if (session('status'))<div class="pesan pesan-berhasil mb-3">{{ session('status') }}</div>@endif
@if (session('galat'))<div class="pesan pesan-gagal mb-3">{{ session('galat') }}</div>@endif

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="kartu h-100">
            <div class="label-kecil">Kode bergabung</div>
            <div class="fs-1 fw-bold" style="letter-spacing:.15em">{{ $kelas->kode_bergabung }}</div>
            <form method="POST" action="{{ route('kelas.pengajar.ganti-kode', $kelas->id_kelas) }}"
                  onsubmit="return confirm('Ganti kode? Kode lama langsung tidak berlaku. Anggota lama tetap di kelas.')">
                @csrf
                <button class="tombol tombol-sekunder tombol-kecil">Ganti kode</button>
            </form>
        </div>
    </div>
    <div class="col-md-6">
        <div class="kartu h-100">
            <div class="label-kecil">Papan peringkat</div>
            <p class="mb-2">Status: <span class="label {{ $kelas->status_scoreboard ? 'label-berhasil' : 'label-netral' }}">{{ $kelas->status_scoreboard ? 'Menyala' : 'Mati' }}</span></p>
            <form method="POST" action="{{ route('kelas.pengajar.scoreboard', $kelas->id_kelas) }}">
                @csrf
                <input type="hidden" name="aktif" value="{{ $kelas->status_scoreboard ? 0 : 1 }}">
                <button class="tombol tombol-sekunder tombol-kecil">{{ $kelas->status_scoreboard ? 'Matikan' : 'Nyalakan' }}</button>
            </form>
        </div>
    </div>
</div>

<div class="d-flex gap-2 flex-wrap mb-4">
    <a href="{{ route('kelas.pengajar.anggota', $kelas->id_kelas) }}" class="tombol tombol-utama">Kelola anggota ({{ $jumlahAnggota }})</a>
    <a href="{{ route('kelas.pengajar.tugas-baru', $kelas->id_kelas) }}" class="tombol tombol-emas">+ Tugas baru</a>
    @if (Route::has('pemantauan.index'))
        <a href="{{ route('pemantauan.index', $kelas->id_kelas) }}" class="tombol tombol-sekunder">Pantau progres</a>
    @endif
</div>

<h2 class="h4">Tugas</h2>
<div class="table-responsive mb-4">
    <table class="tabel-nk align-middle">
        <thead><tr><th>Instruksi</th><th>Tenggat</th><th>Komponen</th><th>Terkumpul</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($daftarTugas as $t)
            <tr>
                <td>{{ \Illuminate\Support\Str::limit($t->instruksi, 80) }}</td>
                <td>{{ \Carbon\Carbon::parse($t->tenggat)->format('d M Y H:i') }}</td>
                <td>{{ $t->jumlah_komponen }}</td>
                <td>{{ $t->jumlah_kumpul }}</td>
                <td><span class="label {{ $t->status === 'terbit' ? 'label-berhasil' : 'label-netral' }}">{{ ucfirst($t->status) }}</span></td>
                <td class="text-end">
                    @if ($t->status === 'draf')
                        <form method="POST" action="{{ route('kelas.pengajar.tugas-terbitkan', [$kelas->id_kelas, $t->id_tugas]) }}">@csrf
                            <button class="tombol tombol-utama tombol-kecil">Terbitkan</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center teks-redup py-4">Belum ada tugas.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if ($kelas->status_scoreboard)
    <h2 class="h4">Peringkat kelas</h2>
    <div class="table-responsive">
        <table class="tabel-nk">
            <thead><tr><th>#</th><th>Nama</th><th>Poin</th></tr></thead>
            <tbody>
            @forelse ($scoreboard as $b)
                <tr><td>{{ $b['peringkat'] }}</td><td>{{ $b['nama_tampilan'] }}</td><td>{{ $b['poin'] }}</td></tr>
            @empty
                <tr><td colspan="3" class="text-center teks-redup py-3">Belum ada anggota aktif.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endif
@endsection
