@php
    $pengguna = auth()->user();
    $peran = $pengguna->peran ?? null;
    $tujuan = config('navigation.tujuan_menu');
    $menuTersedia = config("navigation.menu_peran.$peran", []);
    $namaTampilan = $pengguna->nama_tampilan ?? $pengguna->username ?? 'Tamu';
    $labelPeran = config("navigation.label_peran.$peran", '');
@endphp
<aside class="nk-sidebar d-none d-md-flex">
    <a href="{{ Route::has('beranda') ? route('beranda') : url('/') }}" class="nk-merek">
        <img src="{{ asset('img/logo-ngaksara.png') }}" alt="" width="40" height="40">
        <span>
            <span class="merek">Ngaksara</span>
            <span class="aksara nk-merek-aksara">ꦔꦏ꧀ꦱꦫ</span>
        </span>
    </a>

    <nav class="nk-menu d-flex flex-column gap-1" aria-label="Menu utama">{{-- I-03-01 --}}
        @foreach ($menuTersedia as $kode)
            @php $m = $tujuan[$kode] ?? null; @endphp
            @if ($m && Route::has($m['route']))
                @php $aktif = request()->routeIs(...$m['aktif']); @endphp
                <a href="{{ route($m['route']) }}" @class(['aktif' => $aktif]) @if ($aktif) aria-current="page" @endif>
                    {{ $m['label'] }}
                </a>
            @endif
        @endforeach
    </nav>

    <div class="nk-sidebar-bawah">
        @if ($pengguna)
            <div class="nk-profil">{{-- I-03-02 --}}
                <span class="avatar">{{ mb_strtoupper(mb_substr($namaTampilan, 0, 1)) }}</span>
                <span class="nk-profil-teks">
                    <span class="nk-profil-nama">{{ $namaTampilan }}</span>
                    <span class="nk-profil-peran">{{ $labelPeran }}</span>
                </span>
                @if (Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nk-tombol-keluar" title="Keluar" aria-label="Keluar">⇥</button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</aside>