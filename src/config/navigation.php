<?php

/*
 | Sidebar per peran.
 | 'route' = nama route Laravel, 'aktif' = pola route yang menandai menu aktif.
 | Daftar per peran mengikuti Figma
 */

return [
    'tujuan_menu' => [
        'BERANDA'        => ['label' => 'Beranda',        'route' => 'beranda',       'aktif' => ['beranda']],
        'MATERI'         => ['label' => 'Materi',         'route' => 'materi.index',  'aktif' => ['materi.index', 'materi.show']],
        'LATIHAN'        => ['label' => 'Latihan',        'route' => 'latihan.index', 'aktif' => ['latihan.*']],
        'PROGRES'        => ['label' => 'Progres',        'route' => 'progres',       'aktif' => ['progres*']],
        'KELAS'          => ['label' => 'Kelas',          'route' => 'kelas.index',   'aktif' => ['kelas.index', 'kelas.show', 'tugas.*']],
        'KELOLA_KELAS'   => ['label' => 'Kelola Kelas',   'route' => 'kelas.kelola',  'aktif' => ['kelas.kelola*']],
        'PANTAU_PROGRES' => ['label' => 'Pantau Progres', 'route' => 'kelas.pantau',  'aktif' => ['kelas.pantau*']],
        'KELOLA_MATERI'  => ['label' => 'Kelola Materi',  'route' => 'materi.kelola', 'aktif' => ['materi.kelola', 'materi.sunting']],
        'UNGGAH_MATERI'  => ['label' => 'Unggah Materi',  'route' => 'materi.buat',   'aktif' => ['materi.buat']],
        'FEEDBACK'       => ['label' => 'Feedback',       'route' => 'feedback',      'aktif' => ['feedback*']],
    ],

    // Kunci = nilai kolom peran yang dipakai kode (huruf kecil).
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