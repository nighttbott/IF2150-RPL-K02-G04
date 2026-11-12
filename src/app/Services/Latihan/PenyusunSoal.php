<?php

namespace App\Services\Latihan;

use App\Enums\ModeLatihan;
use App\Models\MateriAksara;

/**
 * Menyusun soal latihan non-kanvas dari konten materi (bahan: MateriAksara::ambilTargetLatihan).
 * Bagian dari C16 LatihanController.siapkanSoal (Algo-021). Setiap soal berisi kunci; hanya bagian
 * publik (lihat publik()) yang boleh dikirim ke browser.
 *
 * Bentuk soal:
 *  - bunyi         : id, target (aksara jawaban benar), bunyi (bacaan latin, penanda soal), opsi [{id, teks = aksara}], kunci (id opsi)
 *  - merangkai     : id, target (kata), bunyi, ubin [{id, teks}], kunci (list teks ubin berurutan)
 *  - transliterasi : id, target (aksara/kata), kunci (teks latin)
 */
final class PenyusunSoal
{
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
        // Pada mode bunyi dan merangkai, target adalah jawaban yang benar sehingga ikut disembunyikan.
        return array_map(function ($s) {
            $sembunyi = ['kunci' => true] + (isset($s['ubin']) || isset($s['opsi']) ? ['target' => true] : []);

            return array_diff_key($s, $sembunyi);
        }, $soal);
    }

    /**
     * I-08: Pelajar mendengar audio pengucapan lalu memilih aksara yang sesuai (Algo-021, cabang BUNYI).
     * Pilihan = aksara yang benar + sampai 3 aksara lain dari butir materi yang sama.
     */
    private function soalBunyi(MateriAksara $materi, int $jumlah): array
    {
        $butir = array_values(array_filter(
            $materi->ambilTargetLatihan('bunyi')->butir,
            fn ($b) => ($b['karakter'] ?? '') !== '' && ($b['bunyi'] ?? null) !== null && $b['bunyi'] !== '',
        ));
        $semuaAksara = array_values(array_unique(array_column($butir, 'karakter')));
        if (count($semuaAksara) < 2) {
            return []; // satu aksara saja tidak bisa dijadikan pilihan ganda
        }
        shuffle($butir);

        $soal = [];
        foreach (array_slice($butir, 0, $jumlah) as $b) {
            $pengecoh = array_values(array_diff($semuaAksara, [$b['karakter']]));
            shuffle($pengecoh);
            $teks = array_merge([$b['karakter']], array_slice($pengecoh, 0, 3));
            shuffle($teks);

            $opsi = [];
            $kunci = '';
            foreach ($teks as $n => $t) {
                $id = chr(97 + $n); // a, b, c, d
                $opsi[] = ['id' => $id, 'teks' => $t];
                if ($t === $b['karakter']) {
                    $kunci = $id;
                }
            }
            $soal[] = ['target' => $b['karakter'], 'bunyi' => $b['bunyi'], 'opsi' => $opsi, 'kunci' => $kunci];
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
