{{-- I-15 Terbitkan Tugas (Marvel). Data: $kelas, $daftarMateri, $daftarMode --}}
@extends('layouts.app')
@section('judul', 'Tugas Baru · '.$kelas->nama)

@php $jumlahBaris = 6; @endphp

@section('konten')
<a href="{{ route('kelas.pengajar.tampil', $kelas->id_kelas) }}" class="teks-redup text-decoration-none">← {{ $kelas->nama }}</a>
<h1 class="mb-3 mt-1">Tugas Baru</h1>

@if ($errors->any())
    <div class="pesan pesan-gagal mb-3">
        <ul class="mb-0">@foreach ($errors->all() as $galat)<li>{{ $galat }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('kelas.pengajar.tugas-simpan', $kelas->id_kelas) }}" class="kartu">
    @csrf
    <div class="mb-3">
        <label for="instruksi" class="fw-bold mb-1">Instruksi</label>
        <textarea id="instruksi" name="instruksi" rows="3" maxlength="2000" required class="form-control">{{ old('instruksi') }}</textarea>
    </div>
    <div class="mb-3">
        <label for="tenggat" class="fw-bold mb-1">Tenggat</label>
        <input id="tenggat" type="datetime-local" name="tenggat" value="{{ old('tenggat') }}" required class="form-control" style="max-width:280px">
    </div>

    <div class="fw-bold mb-1">Komponen tugas</div>
    <p class="teks-redup small">Isi minimal satu baris. Baris tanpa materi diabaikan. Hanya materi terbit yang bisa dipilih.</p>
    <div class="table-responsive mb-3">
        <table class="tabel-nk align-middle">
            <thead><tr><th>Jenis</th><th>Materi</th><th>Mode latihan</th><th>Wajib</th></tr></thead>
            <tbody>
            @for ($i = 0; $i < $jumlahBaris; $i++)
                <tr>
                    <td>
                        <select name="komponen[{{ $i }}][jenis]" class="form-select">
                            <option value="materi" @selected(old("komponen.$i.jenis") === 'materi')>Baca materi</option>
                            <option value="latihan" @selected(old("komponen.$i.jenis", $i === 1 ? 'latihan' : null) === 'latihan')>Latihan</option>
                        </select>
                    </td>
                    <td>
                        <select name="komponen[{{ $i }}][id_materi]" class="form-select">
                            <option value="">(kosong)</option>
                            @foreach ($daftarMateri as $m)
                                <option value="{{ $m->id_materi }}" @selected((string) old("komponen.$i.id_materi") === (string) $m->id_materi)>{{ $m->nama_materi }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="komponen[{{ $i }}][mode]" class="form-select">
                            <option value="">-</option>
                            @foreach ($daftarMode as $mode)
                                <option value="{{ $mode->value }}" @selected(old("komponen.$i.mode") === $mode->value)>{{ ucfirst($mode->value) }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="komponen[{{ $i }}][wajib]" value="0">
                        <input type="checkbox" name="komponen[{{ $i }}][wajib]" value="1" class="form-check-input" @checked(old("komponen.$i.wajib", '1') == '1')>
                    </td>
                </tr>
            @endfor
            </tbody>
        </table>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('kelas.pengajar.tampil', $kelas->id_kelas) }}" class="tombol tombol-sekunder">Batal</a>
        <button name="aksi" value="draf" class="tombol tombol-sekunder">Simpan draf</button>
        <button name="aksi" value="terbitkan" class="tombol tombol-utama">Terbitkan</button>
    </div>
</form>
@endsection
