{{-- Ikon garis 22px untuk menu (warna mengikuti currentColor); parameter: $nama. --}}
<svg class="nk-ikon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($nama)
    @case('beranda')
        <path d="M12 2.5c4.5 4.5 7 9.5 6.3 18.5H5.7C5 12 7.5 7 12 2.5Z"/><path d="M12 7v12M9.5 11.5 12 14l2.5-2.5"/>
        @break
    @case('buku')
        <path d="M3 5c3-1 6-.7 9 1 3-1.7 6-2 9-1v14c-3-1-6-.7-9 1-3-1.7-6-2-9-1Z"/><path d="M12 6v14"/>
        @break
    @case('pena')
        <path d="M4 20l1.2-5.6L16.5 3.1a1.8 1.8 0 0 1 2.6 0l1.8 1.8a1.8 1.8 0 0 1 0 2.6L9.6 18.8Z"/><path d="M14.5 5.5l4 4"/>
        @break
    @case('grafik')
        <path d="M4 20h16"/><path d="M7 16v-4M12 16V8M17 16v-7"/>
        @break
    @case('joglo')
        <path d="M2 11l5-5h10l5 5Z"/><path d="M5 11v9h14v-9M10 20v-5h4v5"/><path d="M9 6l1-3h4l1 3"/>
        @break
    @case('unggah')
        <path d="M12 15V4M7.5 8.5 12 4l4.5 4.5"/><path d="M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/>
        @break
    @case('surat')
        <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6.5 8.5 6 8.5-6"/>
        @break
    @case('keluar')
        <path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3"/><path d="M10 16l4-4-4-4M14 12H4"/>
        @break
    @case('menu')
        <path d="M4 7h16M4 12h16M4 17h16"/>
        @break
@endswitch
</svg>
