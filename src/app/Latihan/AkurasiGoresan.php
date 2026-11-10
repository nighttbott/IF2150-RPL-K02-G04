<?php

namespace App\Latihan;

/**
 * Logika Algo-023 (DPPL 5.16.3) tanpa ketergantungan Laravel.
 */
final class AkurasiGoresan
{
    public function __construct(
        private int $jumlahTitik = 64,
        private float $dMaks = 0.12,
    ) {}

    /**
     * Goresan dan templat memakai bentuk yang sama: daftar goresan, tiap goresan daftar titik
     * berurutan. Skala bebas (kanvas 0–1, templat 0–100) karena keduanya dinormalisasi.
     *
     * @param  list<list<array{x: float, y: float}>>  $goresan
     * @param  list<list<array{x: float, y: float}>>  $templat
     * @return float 0.0–100.0, dibulatkan satu desimal
     */
    public function hitung(array $goresan, array $templat): float
    {
        $p = $this->sampelUlang($goresan);
        $t = $this->sampelUlang($templat);
        if ($p === [] || $t === []) {
            return 0.0;
        }

        $p = $this->normalisasi($p);
        $t = $this->normalisasi($t);
        $jarak = ($this->jarakRataRata($p, $t) + $this->jarakRataRata($t, $p)) / 2;

        return round(max(0.0, 100 * (1 - $jarak / $this->dMaks)), 1);
    }

    /**
     * Sampel ulang berjarak sama di sepanjang ruas tiap goresan. Ujung satu goresan tidak
     * disambung ke awal goresan berikutnya, supaya lompatan pena tidak dihitung sebagai garis.
     *
     * @param  list<list<array{x: float, y: float}>>  $goresan
     * @return list<array{0: float, 1: float}>
     */
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

    /**
     * Pusat massa ke (0, 0), sisi terpanjang kotak pembatas menjadi 1.
     *
     * @param  list<array{0: float, 1: float}>  $titik
     * @return list<array{0: float, 1: float}>
     */
    private function normalisasi(array $titik): array
    {
        $xs = array_column($titik, 0);
        $ys = array_column($titik, 1);
        $cx = array_sum($xs) / count($xs);
        $cy = array_sum($ys) / count($ys);
        $sisi = max(max($xs) - min($xs), max($ys) - min($ys));
        $skala = $sisi > 0 ? $sisi : 1.0;

        return array_map(fn ($t) => [($t[0] - $cx) / $skala, ($t[1] - $cy) / $skala], $titik);
    }

    /**
     * Rata-rata jarak setiap titik $a ke titik $b terdekat.
     *
     * @param  list<array{0: float, 1: float}>  $a
     * @param  list<array{0: float, 1: float}>  $b
     */
    private function jarakRataRata(array $a, array $b): float
    {
        $jumlah = 0.0;
        foreach ($a as $p) {
            $min = INF;
            foreach ($b as $q) {
                $min = min($min, hypot($p[0] - $q[0], $p[1] - $q[1]));
            }
            $jumlah += $min;
        }

        return $jumlah / count($a);
    }
}
