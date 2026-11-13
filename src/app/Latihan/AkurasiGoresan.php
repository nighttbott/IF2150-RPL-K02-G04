<?php

namespace App\Latihan;

/** Logika Algo-023 (DPPL 5.16.3) tanpa ketergantungan Laravel; templat berupa kontur huruf tebal, goresan Pelajar berupa garis tengahnya. */
final class AkurasiGoresan
{
    /** @param float $buangOutlier bagian titik goresan Pelajar terjauh yang diabaikan @param float $faktorToleransi pengali setengah ketebalan huruf yang dianggap jarak nol @param float $buangTemplat bagian titik templat terjauh yang diabaikan (sisa cabang kecil garis tengah) */
    public function __construct(
        private int $jumlahTitik = 200,
        private float $dMaks = 0.05,
        private float $buangOutlier = 0.25,
        private float $faktorToleransi = 0.5,
        private float $buangTemplat = 0.03,
    ) {}

    /** $templat = garis tengah huruf (atau kontur bila belum ada); $kontur = kontur huruf untuk menghitung ketebalan (toleransi). @param  list<list<array{x: float, y: float}>>  $goresan @param  list<list<array{x: float, y: float}>>  $templat @param  list<list<array{x: float, y: float}>>|null  $kontur */
    public function hitung(array $goresan, array $templat, ?array $kontur = null): float
    {
        $p = $this->sampelUlang($goresan);
        $t = $this->sampelUlang($templat);
        if ($p === [] || $t === []) {
            return 0.0;
        }

        // Ketebalan huruf relatif terhadap sisi kotak kontur; 0 bila hanya ada garis terbuka.
        $kontur ??= $templat;
        $tebal = $this->ketebalan($kontur, $this->sampelUlang($kontur));
        $p = $this->normalisasi($p);
        // Bila templat masih berupa kontur, kotaknya lebih besar satu ketebalan daripada garis tengah Pelajar.
        $t = $this->normalisasi($t, $kontur === $templat ? $tebal : 0.0);
        // Goresan yang masih di dalam badan huruf (setengah ketebalan dari garis tengah) dianggap tepat.
        $toleransi = $this->faktorToleransi * $tebal / (2 * max(1e-9, 1 - $tebal));
        // Outlier hanya dibuang dari goresan Pelajar (coretan nyasar); setiap bagian templat tetap wajib tertulis.
        $jarak = ($this->jarakRataRata($p, $t, $toleransi, $this->buangOutlier) + $this->jarakRataRata($t, $p, $toleransi, $this->buangTemplat)) / 2;

        return round(max(0.0, min(100.0, 100 * (1 - $jarak / $this->dMaks))), 1);
    }

    /** Sampel ulang berjarak sama di sepanjang ruas tiap goresan. @param  list<list<array{x: float, y: float}>>  $goresan @return list<array{0: float, 1: float}> */
    private function sampelUlang(array $goresan): array
    {
        $ruas = [];
        $total = 0.0;
        foreach ($goresan as $titik) {
            for ($i = 1; $i < count($titik); $i++) {
                $a = [(float) $titik[$i - 1]['x'], (float) $titik[$i - 1]['y']];
                $b = [(float) $titik[$i]['x'], (float) $titik[$i]['y']];
                $panjang = hypot($b[0] - $a[0], $b[1] - $a[1]);
                if ($panjang > 0) {
                    $ruas[] = [$a, $b, $panjang];
                    $total += $panjang;
                }
            }
        }
        if ($ruas === []) {
            return [];
        }

        $hasil = [];
        $k = 0;
        $awalRuas = 0.0;
        for ($i = 0; $i < $this->jumlahTitik; $i++) {
            $jarak = $total * $i / ($this->jumlahTitik - 1);
            while ($k < count($ruas) - 1 && $awalRuas + $ruas[$k][2] < $jarak) {
                $awalRuas += $ruas[$k][2];
                $k++;
            }
            [$a, $b, $panjang] = $ruas[$k];
            $f = min(1.0, max(0.0, ($jarak - $awalRuas) / $panjang));
            $hasil[] = [$a[0] + ($b[0] - $a[0]) * $f, $a[1] + ($b[1] - $a[1]) * $f];
        }

        return $hasil;
    }

    /** Perkiraan ketebalan huruf = 2 x luas bersih / keliling kontur, dibagi sisi kotak; dibatasi 0..0,4. @param  list<list<array{x: float, y: float}>>  $templat @param  list<array{0: float, 1: float}>  $sampel */
    private function ketebalan(array $templat, array $sampel): float
    {
        $luas = 0.0;
        $keliling = 0.0;
        foreach ($templat as $kontur) {
            $n = count($kontur);
            if ($n < 3) {
                continue;
            }
            $tertutup = hypot($kontur[0]['x'] - $kontur[$n - 1]['x'], $kontur[0]['y'] - $kontur[$n - 1]['y']);
            $panjang = 0.0;
            for ($i = 1; $i < $n; $i++) {
                $panjang += hypot($kontur[$i]['x'] - $kontur[$i - 1]['x'], $kontur[$i]['y'] - $kontur[$i - 1]['y']);
            }
            if ($tertutup > 0.05 * $panjang) {
                continue; // garis terbuka (mis. templat garis tengah) tidak punya ketebalan
            }
            for ($i = 0; $i < $n; $i++) {
                $a = $kontur[$i];
                $b = $kontur[($i + 1) % $n];
                $luas += $a['x'] * $b['y'] - $b['x'] * $a['y'];
                $keliling += $i < $n - 1 ? hypot($b['x'] - $a['x'], $b['y'] - $a['y']) : $tertutup;
            }
        }
        $xs = array_column($sampel, 0);
        $ys = array_column($sampel, 1);
        $sisi = max(max($xs) - min($xs), max($ys) - min($ys));
        if ($keliling <= 0 || $sisi <= 0) {
            return 0.0;
        }

        return min(0.4, abs($luas / 2) * 2 / $keliling / $sisi);
    }

    /** Pusat massa ke (0, 0), sisi terpanjang kotak pembatas (dikurangi ketebalan) menjadi 1. @param  list<array{0: float, 1: float}>  $titik @return list<array{0: float, 1: float}> */
    private function normalisasi(array $titik, float $tebal = 0.0): array
    {
        $xs = array_column($titik, 0);
        $ys = array_column($titik, 1);
        $cx = array_sum($xs) / count($xs);
        $cy = array_sum($ys) / count($ys);
        $sisi = max(max($xs) - min($xs), max($ys) - min($ys)) * (1 - $tebal);
        $skala = $sisi > 0 ? $sisi : 1.0;

        return array_map(fn ($t) => [($t[0] - $cx) / $skala, ($t[1] - $cy) / $skala], $titik);
    }

    /** Rata-rata jarak titik $a ke titik $b terdekat, dikurangi toleransi, tanpa bagian titik terjauh (outlier). @param  list<array{0: float, 1: float}>  $a @param  list<array{0: float, 1: float}>  $b */
    private function jarakRataRata(array $a, array $b, float $toleransi, float $buang): float
    {
        $semua = [];
        foreach ($a as $p) {
            $min = INF;
            foreach ($b as $q) {
                $min = min($min, hypot($p[0] - $q[0], $p[1] - $q[1]));
            }
            $semua[] = max(0.0, $min - $toleransi);
        }
        sort($semua);
        $dipakai = array_slice($semua, 0, max(1, (int) ceil(count($semua) * (1 - $buang))));

        return array_sum($dipakai) / count($dipakai);
    }
}
