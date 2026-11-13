<?php

namespace App\Latihan;

/** Logika Algo-027 (DPPL 5.16.7) tanpa ketergantungan Laravel. */
final class PemeriksaBadge
{
    /** Deskripsi untuk kartu I-10-05. */
    public const DESKRIPSI = [
        'pemula' => 'Menyelesaikan latihan pertama.',
        'konsisten_7' => 'Berlatih 7 hari berturut-turut.',
        'seratus_poin' => 'Mengumpulkan 100 poin.',
    ];

    /** @param  list<string>  $dimiliki @return list<string> kode badge yang syaratnya baru terpenuhi */
    public function periksa(int $streak, int $poin, array $dimiliki): array
    {
        $syarat = [
            'pemula' => $poin >= 10,
            'konsisten_7' => $streak >= 7,
            'seratus_poin' => $poin >= 100,
        ];

        return array_keys(array_filter(
            $syarat,
            fn (bool $terpenuhi, string $kode) => $terpenuhi && ! in_array($kode, $dimiliki, true),
            ARRAY_FILTER_USE_BOTH,
        ));
    }
}
