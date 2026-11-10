<?php

namespace App\Support\Materi;

use App\Enums\JenisAksara;

/**
 * Akses ke template titik aksara (resources/aksara/template_aksara.json), dibuat sekali jalan oleh
 * scripts/buat_template_aksara.py dari font Noto Sans Javanese/Sundanese.
 */
final class TemplateAksara
{
    /** @var array{karakter: array<string, array<string, array>>, kode: array<string, array<string, string>>}|null */
    private static ?array $peta = null;

    private static function peta(): array
    {
        if (self::$peta === null) {
            $json = json_decode((string) file_get_contents(resource_path('aksara/template_aksara.json')), true, 512, JSON_THROW_ON_ERROR);
            $peta = ['karakter' => [], 'kode' => []];
            foreach ($json['aksara'] as $a) {
                $peta['karakter'][$a['jenis']][$a['karakter']] = $a;
                $peta['kode'][$a['jenis']][$a['kode']] = $a['karakter'];
            }
            self::$peta = $peta;
        }

        return self::$peta;
    }

    /** @return array{kode: string, karakter: string, goresan: array}|null */
    public static function cari(JenisAksara $jenis, string $karakter): ?array
    {
        return self::peta()['karakter'][strtoupper($jenis->value)][$karakter] ?? null;
    }

    public static function karakterDariKode(JenisAksara $jenis, string $kode): ?string
    {
        return self::peta()['kode'][strtoupper($jenis->value)][strtolower(trim($kode))] ?? null;
    }
}
