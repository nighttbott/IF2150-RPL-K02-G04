<?php

namespace App\Services\Latihan;

/**
 * Algo-020 nilaiJawaban untuk mode non-kanvas (Marvel): bunyi, merangkai, transliterasi.
 * Fungsi murni tanpa basis data. Skor selalu dihitung di server; kunci tidak pernah dikirim ke browser.
 */
final class PenilaiJawaban
{
    /** Bunyi: id_pilihan dibandingkan dengan id pilihan kunci. */
    public static function bunyi(?string $idPilihan, string $kunci): HasilPenilaian
    {
        $benar = $idPilihan !== null && $idPilihan !== '' && $idPilihan === $kunci;

        return new HasilPenilaian($benar ? 100.0 : 0.0, $benar);
    }

    /**
     * Merangkai: urutan ubin dibandingkan dengan urutan benar; posisi yang salah ditandai.
     * Skor = persen posisi benar terhadap panjang terpanjang antara jawaban dan kunci.
     *
     * @param  list<string>  $urutan
     * @param  list<string>  $kunci
     */
    public static function merangkai(array $urutan, array $kunci): HasilPenilaian
    {
        $urutan = array_values($urutan);
        $kunci = array_values($kunci);
        $panjang = max(count($kunci), count($urutan));
        if ($panjang === 0) {
            return new HasilPenilaian(0.0, false);
        }

        $salah = [];
        for ($i = 0; $i < $panjang; $i++) {
            if (! isset($urutan[$i], $kunci[$i]) || $urutan[$i] !== $kunci[$i]) {
                $salah[] = $i;
            }
        }

        return new HasilPenilaian(round(100 * ($panjang - count($salah)) / $panjang, 2), $salah === [], $salah);
    }

    /** Transliterasi: sama setelah trim dan huruf kecil. */
    public static function transliterasi(?string $jawaban, string $kunci): HasilPenilaian
    {
        $benar = $jawaban !== null && self::normalisasi($jawaban) !== '' && self::normalisasi($jawaban) === self::normalisasi($kunci);

        return new HasilPenilaian($benar ? 100.0 : 0.0, $benar);
    }

    public static function normalisasi(string $teks): string
    {
        return mb_strtolower(trim($teks));
    }
}
