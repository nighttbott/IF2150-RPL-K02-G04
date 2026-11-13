<?php

// Parameter penilaian latihan menulis (Algo-023); dikalibrasi dengan simulasi garis tengah 38 aksara Jawa dan Sunda (lihat B.1 laporan M9).
return [
    'ambang_akurasi' => (float) env('LATIHAN_AMBANG_AKURASI', 70),
    'd_maks' => (float) env('LATIHAN_D_MAKS', 0.05),
    'buang_outlier' => (float) env('LATIHAN_BUANG_OUTLIER', 0.25),
    'faktor_toleransi' => (float) env('LATIHAN_FAKTOR_TOLERANSI', 0.5),
    'jumlah_titik' => 200,
    'poin_per_soal' => 10,
    'maks_goresan' => 50,
    'maks_titik_per_goresan' => 2000,
    'maks_titik_total' => 10000,
];
