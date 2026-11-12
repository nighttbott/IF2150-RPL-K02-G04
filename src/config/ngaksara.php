<?php

return [
    // Versi aplikasi dipakai KontenMateri::ambilKonten untuk memilih versi konten yang kompatibel.
    'versi_aplikasi' => env('NGAKSARA_VERSI', '1.0'),

    'materi' => [
        // Aturan berkas unggahan Tim Materi (Algo-052 validasiFormatFile).
        'ekstensi' => ['json', 'mp3', 'png', 'svg'],
        'maks_kb' => 10240,
        'disk' => env('NGAKSARA_DISK_MATERI', 'local'),
        'folder' => 'materi',
        // Skor penguasaan minimum agar materi dianggap tuntas (Algo-041).
        'ambang_tuntas' => 70,
        'maks_rekomendasi' => 3,
    ],

    // Jumlah latihan berhasil per hari untuk kartu "Target hari ini" (I-03-07).
    'target_harian' => 3,
];
