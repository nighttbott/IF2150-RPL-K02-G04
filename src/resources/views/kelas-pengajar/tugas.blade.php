@extends('layouts.app')

@section('judul', 'Terbitkan Tugas')

@push('gaya') @include('kelas-pengajar.gaya') @endpush

@section('konten')
<a href="{{ route('pengajar.kelas.index') }}" class="small text-decoration-none">← Kembali ke Kelola Kelas</a>
<h1 class="h3 fw-bold mb-1 mt-2">Terbitkan Tugas</h1>
<p class="teks-redup">Kelas {{ $kelas->nama }}</p>

@if ($errors->any())
    <div class="pesan pesan-gagal mb-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-9">
        <form method="POST" action="{{ route('pengajar.kelas.terbitkan', $kelas->id_kelas) }}">
            @csrf

            <div class="kartu p-3 mb-3">
                <label for="instruksi" class="fw-bold small">Instruksi</label>
                <textarea id="instruksi" name="instruksi" rows="3" maxlength="2000" required
                          class="form-control mb-3">{{ old('instruksi') }}</textarea>

                <label for="tenggat" class="fw-bold small">Tenggat</label>
                <input id="tenggat" type="datetime-local" name="tenggat" value="{{ old('tenggat') }}" required
                       class="form-control" style="max-width:260px">
            </div>

            <div class="kartu p-3 mb-3">
                <div class="fw-bold mb-2">Komponen tugas</div>
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <select id="pilih-materi" class="form-select w-auto">
                        @foreach ($daftarMateri as $m)
                            <option value="{{ $m['id'] }}">{{ $m['nama'] }}</option>
                        @endforeach
                    </select>
                    <select id="pilih-jenis" class="form-select w-auto">
                        @foreach ($jenis as $nilai => $label)
                            <option value="{{ $nilai }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="pilih-wajib" checked>
                        <label class="form-check-label small" for="pilih-wajib">Wajib</label>
                    </div>
                    <button type="button" id="tambah" class="btn btn-sm btn-light border fw-semibold">+ Tambah</button>
                </div>

                <table class="table align-middle mb-0">
                    <thead><tr class="small teks-redup"><th>MATERI</th><th>JENIS</th><th>WAJIB</th><th></th></tr></thead>
                    <tbody id="daftar-komponen"></tbody>
                </table>
                <div id="kosong" class="small teks-redup pt-2">Belum ada komponen.</div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('pengajar.kelas.index') }}" class="tombol tombol-sekunder">Batal</a>
                <button type="submit" class="tombol tombol-utama">Terbitkan</button>
            </div>
        </form>
    </div>

    <div class="col-lg-3">
        <div class="kotak-tips p-3">
            <div class="fw-bold" style="color:var(--nk-emas-tua)">Kata Pak Aksa</div>
            <div class="small">Tugas hanya terlihat oleh anggota aktif kelas ini.</div>
        </div>
    </div>
</div>
@endsection

@push('skrip')
<script>
    const tbody = document.getElementById('daftar-komponen');
    const kosong = document.getElementById('kosong');
    let urutan = 0;

    function sel(id) {
        const el = document.getElementById(id);
        return { nilai: el.value, label: el.options[el.selectedIndex].text };
    }

    function inputTersembunyi(nama, nilai) {
        const i = document.createElement('input');
        i.type = 'hidden';
        i.name = 'komponen[' + urutan + '][' + nama + ']';
        i.value = nilai;
        return i;
    }

    function tambahBaris(k) {
        urutan++;
        const tr = document.createElement('tr');

        const tdMateri = document.createElement('td');
        tdMateri.textContent = k.materi_label;
        tdMateri.appendChild(inputTersembunyi('materi', k.materi));
        tdMateri.appendChild(inputTersembunyi('materi_label', k.materi_label));

        const tdJenis = document.createElement('td');
        tdJenis.textContent = k.jenis_label;
        tdJenis.appendChild(inputTersembunyi('jenis', k.jenis));
        tdJenis.appendChild(inputTersembunyi('jenis_label', k.jenis_label));

        const tdWajib = document.createElement('td');
        tdWajib.textContent = k.wajib ? 'Ya' : 'Tidak';
        tdWajib.appendChild(inputTersembunyi('wajib', k.wajib ? '1' : '0'));

        const tdHapus = document.createElement('td');
        tdHapus.className = 'text-end';
        const hapus = document.createElement('button');
        hapus.type = 'button';
        hapus.className = 'btn btn-sm btn-light border';
        hapus.textContent = 'Hapus';
        hapus.addEventListener('click', function () { tr.remove(); segarkan(); });
        tdHapus.appendChild(hapus);

        tr.append(tdMateri, tdJenis, tdWajib, tdHapus);
        tbody.appendChild(tr);
        segarkan();
    }

    function segarkan() {
        kosong.classList.toggle('d-none', tbody.children.length > 0);
    }

    document.getElementById('tambah').addEventListener('click', function () {
        const m = sel('pilih-materi');
        const j = sel('pilih-jenis');
        tambahBaris({
            materi: m.nilai, materi_label: m.label,
            jenis: j.nilai, jenis_label: j.label,
            wajib: document.getElementById('pilih-wajib').checked,
        });
    });

    // Pulihkan komponen bila validasi server gagal
    const lama = @json(array_values(old('komponen', [])));
    lama.forEach(function (k) {
        tambahBaris({
            materi: k.materi, materi_label: k.materi_label || k.materi,
            jenis: k.jenis, jenis_label: k.jenis_label || k.jenis,
            wajib: k.wajib === '1' || k.wajib === 1 || k.wajib === true,
        });
    });
    segarkan();
</script>
@endpush
