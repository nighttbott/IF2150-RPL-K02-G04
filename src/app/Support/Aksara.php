<?php

namespace App\Support;

/** Pembantu tampilan: mengubah satu aksara dasar menjadi nama Latinnya (misalnya ꦲ menjadi "ha"). Dipakai untuk label pola kesalahan agar mudah dibaca. */
final class Aksara
{
    private const LATIN = [
        // Aksara Jawa nglegena (ha na ca ra ka ... ma ga ba tha nga)
        'ꦲ' => 'ha', 'ꦤ' => 'na', 'ꦕ' => 'ca', 'ꦫ' => 'ra', 'ꦏ' => 'ka',
        'ꦢ' => 'da', 'ꦠ' => 'ta', 'ꦱ' => 'sa', 'ꦮ' => 'wa', 'ꦭ' => 'la',
        'ꦥ' => 'pa', 'ꦝ' => 'dha', 'ꦗ' => 'ja', 'ꦪ' => 'ya', 'ꦚ' => 'nya',
        'ꦩ' => 'ma', 'ꦒ' => 'ga', 'ꦧ' => 'ba', 'ꦛ' => 'tha', 'ꦔ' => 'nga',
        // Aksara Sunda ngalagena
        'ᮊ' => 'ka', 'ᮌ' => 'ga', 'ᮍ' => 'nga', 'ᮎ' => 'ca', 'ᮏ' => 'ja',
        'ᮑ' => 'nya', 'ᮒ' => 'ta', 'ᮓ' => 'da', 'ᮔ' => 'na', 'ᮕ' => 'pa',
        'ᮘ' => 'ba', 'ᮙ' => 'ma', 'ᮚ' => 'ya', 'ᮛ' => 'ra', 'ᮜ' => 'la',
        'ᮝ' => 'wa', 'ᮞ' => 'sa', 'ᮠ' => 'ha',
    ];

    public static function latin(string $teks): string
    {
        return self::LATIN[$teks] ?? $teks;
    }
}
