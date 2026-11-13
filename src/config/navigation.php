<?php

// Menu sidebar per peran (I-03-01, mengikuti Figma); 'route' = nama route, 'aktif' = pola route penanda menu aktif.

return [
    'tujuan_menu' => [
        'BERANDA'        => ['label' => 'Beranda',        'ikon' => 'beranda', 'route' => 'beranda',              'aktif' => ['beranda']],
        'MATERI'         => ['label' => 'Materi',         'ikon' => 'buku',    'route' => 'materi.index',         'aktif' => ['materi.index', 'materi.show']],
        'LATIHAN'        => ['label' => 'Latihan',        'ikon' => 'pena',    'route' => 'latihan.index',        'aktif' => ['latihan.*']],
        'PROGRES'        => ['label' => 'Progres',        'ikon' => 'grafik',  'route' => 'progres.index',        'aktif' => ['progres.*']],
        'KELAS'          => ['label' => 'Kelas',          'ikon' => 'joglo',   'route' => 'kelas-pelajar.index',  'aktif' => ['kelas-pelajar.*']],
        'KELOLA_KELAS'   => ['label' => 'Kelola Kelas',   'ikon' => 'joglo',   'route' => 'pengajar.kelas.index', 'aktif' => ['pengajar.kelas.*']],
        'PANTAU_PROGRES' => ['label' => 'Pantau Progres', 'ikon' => 'grafik',  'route' => 'pemantauan.kelas',     'aktif' => ['pemantauan.*']],
        'KELOLA_MATERI'  => ['label' => 'Kelola Materi',  'ikon' => 'buku',    'route' => 'materi.kelola',        'aktif' => ['materi.kelola', 'materi.sunting']],
        'UNGGAH_MATERI'  => ['label' => 'Unggah Materi',  'ikon' => 'unggah',  'route' => 'materi.buat',          'aktif' => ['materi.buat']],
        'FEEDBACK'       => ['label' => 'Feedback',       'ikon' => 'surat',   'route' => 'feedback.index',       'aktif' => ['feedback.*']],
    ],

    // Kunci = nilai kolom peran (huruf kecil).
    'menu_peran' => [
        'pelajar'    => ['BERANDA', 'MATERI', 'LATIHAN', 'PROGRES', 'KELAS', 'FEEDBACK'],
        'pengajar'   => ['BERANDA', 'KELOLA_KELAS', 'PANTAU_PROGRES', 'FEEDBACK'],
        'tim_materi' => ['BERANDA', 'KELOLA_MATERI', 'UNGGAH_MATERI'],
    ],

    'label_peran' => [
        'pelajar'    => 'Pelajar',
        'pengajar'   => 'Pengajar',
        'tim_materi' => 'Tim Materi',
    ],
];
