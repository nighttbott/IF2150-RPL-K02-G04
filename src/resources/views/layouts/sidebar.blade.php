{{-- Sidebar per peran (I-03-01) dan profil + keluar (I-03-02); menu dari config/navigation.php, tampil hanya bila route terdaftar. --}}
@php
    $pengguna = auth()->user();
    $peran = strtolower((string) $pengguna?->peran);
    $tujuan = config('navigation.tujuan_menu');
    $menu = collect(config("navigation.menu_peran.$peran", []))
        ->map(fn ($kode) => $tujuan[$kode] ?? null)
        ->filter(fn ($m) => $m && Route::has($m['route']));
    $namaTampilan = $pengguna?->nama_tampilan ?? $pengguna?->username ?? 'Tamu';
    $labelPeran = config("navigation.label_peran.$peran", '');
@endphp

<a href="{{ Route::has('beranda') ? route('beranda') : url('/') }}" class="nk-merek">
    <img src="{{ asset('img/logo-ngaksara.png') }}" alt="" width="40" height="40">
    <span>
        <span class="merek">Ngaksara</span>
        <span class="aksara nk-merek-aksara" lang="jv">ꦔꦏ꧀ꦱꦫ</span>
    </span>
</a>

<nav class="nk-menu d-flex flex-column gap-1" aria-label="Menu utama">
    @foreach ($menu as $m)
        @php $aktif = request()->routeIs(...$m['aktif']); @endphp
        <a href="{{ route($m['route']) }}" @class(['aktif' => $aktif]) @if ($aktif) aria-current="page" @endif>
            @include('layouts.ikon', ['nama' => $m['ikon']])
            <span>{{ $m['label'] }}</span>
        </a>
    @endforeach
</nav>

<div class="nk-sidebar-bawah">
    <img src="{{ asset('img/motif-kawung.svg') }}" alt="" class="nk-motif-sidebar" width="560" height="40">
    @if ($pengguna)
        <div class="nk-profil">
            <span class="avatar">{{ mb_strtoupper(mb_substr($namaTampilan, 0, 1)) }}</span>
            <span class="nk-profil-teks">
                <span class="nk-profil-nama">{{ $namaTampilan }}</span>
                <span class="nk-profil-peran">{{ $labelPeran }}</span>
            </span>
            @if (Route::has('keluar'))
                <form method="POST" action="{{ route('keluar') }}" class="m-0">
                    @csrf
                    <button type="submit" class="nk-tombol-keluar" title="Keluar" aria-label="Keluar">
                        @include('layouts.ikon', ['nama' => 'keluar'])
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>
