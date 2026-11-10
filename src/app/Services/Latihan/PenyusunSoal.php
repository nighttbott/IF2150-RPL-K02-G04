<?php

namespace App\Services\Latihan;

use App\Enums\ModeLatihan;
use App\Models\MateriAksara;

/**
 * Menyusun soal latihan non-kanvas dari konten materi (bahan: MateriAksara::ambilTargetLatihan).
 * Setiap soal berisi kunci; hanya bagian publik (lihat publik()) yang boleh dikirim ke browser.
 *
 * Bentuk soal:
 *  - bunyi         : id, target (aksara), opsi [{id, teks}], kunci (id opsi)
 *  - merangkai     : id, target (kata), bunyi, ubin [{id, teks}], kunci (list teks ubin berurutan)
 *  - transliterasi : id, target (aksara/kata), kunci (teks latin)
 */
final class PenyusunSoal
{
    private const PENGECOH = ['ha', 'na', 'ca', 'ra', 'ka', 'da', 'ta', 'sa', 'wa', 'la', 'pa', 'dha', 'ja', 'ya', 'nya', 'ma', 'ga', 'ba', 'tha', 'nga'];

    /**
     * @return list<array<string, mixed>>  kosong bila materi belum punya bahan untuk mode ini
     */
    public function susun(MateriAksara $materi, ModeLatihan $mode, int $jumlah): array
    {
        $soal = match ($mode) {
            ModeLatihan::Bunyi => $this->soalBunyi($materi, $jumlah),
            ModeLatihan::Merangkai => $this->soalMerangkai($materi, $jumlah),
            ModeLatihan::Transliterasi => $this->soalTransliterasi($materi, $jumlah),
            default => [],
        };

        foreach ($soal as $i => &$s) {
            $s['id'] = 's'.$i;
        }
        unset($s);

        return $soal;
    }

    /**
     * Bagian soal yang aman dikirim ke browser (tanpa kunci).
     *
     * @param  list<array<string, mixed>>  $soal
     * @return list<array<string, mixed>>
     */
    public static function publik(array $soal): array
    {
        // Pada mode merangkai, target adalah kata yang benar sehingga ikut disembunyikan.
        return array_map(function ($s) {
            $sembunyi = ['kunci' => true] + (isset($s['ubin']) ? ['target' => true] : []);

            return array_diff_key($s, $sembunyi);
        }, $soal);
    }

    private function soalBunyi(MateriAksara $materi, int $jumlah): array
    {
        $butir = array_values(array_filter(
            $materi->ambilTargetLatihan('bunyi')->butir,
            fn ($b) => ($b['bunyi'] ?? null) !== null && $b['bunyi'] !== '',
        ));
        shuffle($butir);

        $semuaBunyi = array_values(array_unique(array_column($butir, 'bunyi')));
        $soal = [];
        foreach (array_slice($butir, 0, $jumlah) as $b) {
            $pengecoh = array_values(array_diff($semuaBunyi, [$b['bunyi']]));
            shuffle($pengecoh);
            $pengecoh = array_slice($pengecoh, 0, 3);
            if (count($pengecoh) < 3) {
                $cadangan = array_values(array_diff(self::PENGECOH, [$b['bunyi']], $pengecoh));
                shuffle($cadangan);
                $pengecoh = array_merge($pengecoh, array_slice($cadangan, 0, 3 - count($pengecoh)));
            }

            $teks = array_merge([$b['bunyi']], $pengecoh);
            shuffle($teks);
            $opsi = [];
            $kunci = '';
            foreach ($teks as $n => $t) {
                $id = chr(97 + $n); // a, b, c, d
                $opsi[] = ['id' => $id, 'teks' => $t];
                if ($t === $b['bunyi']) {
                    $kunci = $id;
                }
            }
            $soal[] = ['target' => $b['karakter'], 'opsi' => $opsi, 'kunci' => $kunci];
        }

        return $soal;
    }

    private function soalMerangkai(MateriAksara $materi, int $jumlah): array
    {
        $pasangan = $materi->ambilTargetLatihan('merangkai')->butir;
        shuffle($pasangan);

        $soal = [];
        foreach ($pasangan as $p) {
            $kunci = $this->pecahAksara($p['kata']);
            if (count($kunci) < 2) {
                continue; // satu aksara tidak bisa dirangkai
            }

            $acak = $kunci;
            for ($coba = 0; $coba < 5; $coba++) {
                shuffle($acak);
                if ($acak !== $kunci) {
                    break;
                }
            }
            $ubin = [];
            foreach ($acak as $n => $teks) {
                $ubin[] = ['id' => 't'.$n, 'teks' => $teks];
            }

            $soal[] = ['target' => $p['kata'], 'bunyi' => $p['bunyi'], 'ubin' => $ubin, 'kunci' => $kunci];
            if (count($soal) >= $jumlah) {
                break;
            }
        }

        return $soal;
    }

    private function soalTransliterasi(MateriAksara $materi, int $jumlah): array
    {
        $kumpulan = [];
        foreach ($materi->ambilTargetLatihan('bunyi')->butir as $b) {
            if (($b['bunyi'] ?? null) !== null && $b['bunyi'] !== '') {
                $kumpulan[$b['karakter']] = $b['bunyi'];
            }
        }
        foreach ($materi->ambilTargetLatihan('merangkai')->butir as $p) {
            $kumpulan[$p['kata']] = $p['bunyi'];
        }

        $kunciAcak = array_keys($kumpulan);
        shuffle($kunciAcak);

        $soal = [];
        foreach (array_slice($kunciAcak, 0, $jumlah) as $target) {
            $soal[] = ['target' => (string) $target, 'kunci' => $kumpulan[$target]];
        }

        return $soal;
    }

    /** @return list<string> satu elemen per gugus aksara (aksara dasar beserta tanda yang menempel) */
    private function pecahAksara(string $kata): array
    {
        preg_match_all('/\X/u', trim($kata), $m);

        return $m[0];
    }
}
