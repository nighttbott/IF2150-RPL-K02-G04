<?php

return [
    'ambang_akurasi' => (float) env('LATIHAN_AMBANG_AKURASI', 70),
    // 0,12 dari kalibrasi 38 templat (Jawa dan Sunda); dengan 0,25 (DPPL) hampir semua aksara yang salah ikut lulus.
    'd_maks' => (float) env('LATIHAN_D_MAKS', 0.12),
    'jumlah_titik' => 64,
    'poin_per_soal' => 10,
    'maks_goresan' => 50,
    'maks_titik_per_goresan' => 2000,
    'maks_titik_total' => 10000,
];
