{{-- I-16 Kelola Anggota (Marvel). Data: $kelas, $anggota --}}
@extends('layouts.app')
@section('judul', 'Anggota · '.$kelas->nama)

@section('konten')
<a href="{{ route('kelas.pengajar.tampil', $kelas->id_kelas) }}" class="teks-redup text-decoration-none">← {{ $kelas->nama }}</a>
<h1 class="mb-3 mt-1">Anggota Kelas</h1>

@if (session('status'))<div class="pesan pesan-berhasil mb-3">{{ session('status') }}</div>@endif
@if (session('galat'))<div class="pesan pesan-gagal mb-3">{{ session('galat') }}</div>@endif

<div class="table-responsive">
    <table class="tabel-nk align-middle">
        <thead><tr><th>Nama</th><th>Email</th><th>Poin</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse ($anggota as $a)
            <tr>
                <td>{{ $a->nama_tampilan }}</td>
                <td>{{ $a->email }}</td>
                <td>{{ $a->poin ?? 0 }}</td>
                <td><span class="label {{ $a->status === 'aktif' ? 'label-berhasil' : 'label-netral' }}">{{ ucfirst($a->status) }}</span></td>
                <td class="text-end">
                    @if ($a->status === 'aktif')
                        <form method="POST" action="{{ route('kelas.pengajar.keluarkan', [$kelas->id_kelas, $a->id_pelajar]) }}"
                              onsubmit="return confirm('Keluarkan {{ e($a->nama_tampilan) }} dari kelas? Riwayat belajarnya tetap tersimpan.')">
                            @csrf @method('DELETE')
                            <button class="tombol tombol-sekunder tombol-kecil" style="color:var(--nk-merah-tua)">Keluarkan</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center teks-redup py-4">Belum ada anggota. Bagikan kode <strong>{{ $kelas->kode_bergabung }}</strong> kepada Pelajar.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
