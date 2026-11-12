<?php

namespace Tests\Unit\Latihan;

use App\Latihan\AkurasiGoresan;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AkurasiGoresanTest extends TestCase
{
    /** @var array<string, list<list<array{x: float, y: float}>>> goresan templat per karakter */
    private static array $templat;

    private AkurasiGoresan $akurasi;

    public static function setUpBeforeClass(): void
    {
        $json = json_decode(file_get_contents(__DIR__.'/../../../resources/aksara/template_aksara.json'), true);
        self::$templat = array_column($json['aksara'], 'goresan', 'karakter');
    }

    protected function setUp(): void
    {
        mt_srand(1);
        $this->akurasi = new AkurasiGoresan(64, 0.12);
    }

    public static function kasusTepi(): array
    {
        $titik = ['x' => 0.3, 'y' => 0.4];
        $garis = [[['x' => 10, 'y' => 10], ['x' => 90, 'y' => 90]]];

        return [
            'goresan kosong' => [[], $garis],
            'satu titik' => [[[$titik]], $garis],
            'semua titik sama' => [[[$titik, $titik, $titik], [$titik, $titik]], $garis],
            'templat kosong' => [[[['x' => 0, 'y' => 0], ['x' => 1, 'y' => 1]]], []],
        ];
    }

    #[DataProvider('kasusTepi')]
    public function test_kasus_tepi_mengembalikan_nol(array $goresan, array $templat): void
    {
        $this->assertSame(0.0, $this->akurasi->hitung($goresan, $templat));
    }

    public function test_goresan_tepat_di_templat_bernilai_seratus(): void
    {
        $this->assertSame(100.0, $this->akurasi->hitung(self::$templat['ꦲ'], self::$templat['ꦲ']));
    }

    public function test_goresan_rapi_mengikuti_templat_lulus(): void
    {
        foreach (['ꦲ', 'ꦏ', 'ꦤ', 'ꦩ', 'ᮊ', 'ᮞ'] as $aksara) {
            $skor = $this->akurasi->hitung($this->derau(self::$templat[$aksara], 1), self::$templat[$aksara]);
            $this->assertGreaterThanOrEqual(70, $skor, "aksara $aksara");
        }
    }

    public function test_goresan_separuh_tidak_lulus(): void
    {
        $t = self::$templat['ꦲ'];
        $separuh = array_map(fn ($g) => array_slice($g, 0, intdiv(count($g), 2)), $t);

        $this->assertLessThan(70, $this->akurasi->hitung($separuh, $t));
    }

    public function test_aksara_lain_tidak_lulus(): void
    {
        $this->assertLessThan(70, $this->akurasi->hitung(self::$templat['ꦗ'], self::$templat['ꦲ']));
        $this->assertLessThan(70, $this->akurasi->hitung(self::$templat['ᮙ'], self::$templat['ᮊ']));
    }

    public function test_geser_dan_perbesar_tidak_mengubah_skor(): void
    {
        $t = self::$templat['ꦏ'];
        $goresan = array_map(fn ($g) => array_map(fn ($p) => ['x' => $p['x'] / 100, 'y' => $p['y'] / 100], $g), $this->derau($t, 2));
        $diubah = array_map(fn ($g) => array_map(fn ($p) => ['x' => $p['x'] * 3 + 0.7, 'y' => $p['y'] * 3 - 0.2], $g), $goresan);

        $this->assertEqualsWithDelta($this->akurasi->hitung($goresan, $t), $this->akurasi->hitung($diubah, $t), 0.1);
    }

    public function test_urutan_dan_arah_goresan_dibalik_skor_hampir_sama(): void
    {
        $t = self::$templat['ꦤ'];
        $goresan = $this->derau($t, 1);
        $dibalik = array_reverse(array_map('array_reverse', $goresan));

        // Titik sampel bergeser sedikit karena grid sampel ulang dimulai dari ujung yang lain.
        $this->assertEqualsWithDelta($this->akurasi->hitung($goresan, $t), $this->akurasi->hitung($dibalik, $t), 1.0);
    }

    public function test_hasil_di_rentang_nol_sampai_seratus_satu_desimal(): void
    {
        $t = self::$templat['ꦩ'];
        for ($i = 0; $i < 20; $i++) {
            $acak = [array_map(fn () => ['x' => mt_rand() / mt_getrandmax(), 'y' => mt_rand() / mt_getrandmax()], range(1, 30))];
            $skor = $this->akurasi->hitung($acak, $t);
            $this->assertGreaterThanOrEqual(0, $skor);
            $this->assertLessThanOrEqual(100, $skor);
            $this->assertSame(round($skor, 1), $skor);
        }
    }

    /** Derau normal pada tiap titik, dalam satuan kotak templat 0–100. */
    private function derau(array $goresan, float $simpangan): array
    {
        return array_map(fn ($g) => array_map(function ($p) use ($simpangan) {
            $u = max(mt_rand() / mt_getrandmax(), 1e-9);
            $v = mt_rand() / mt_getrandmax();
            $r = sqrt(-2 * log($u)) * $simpangan;

            return ['x' => $p['x'] + $r * cos(2 * M_PI * $v), 'y' => $p['y'] + $r * sin(2 * M_PI * $v)];
        }, $g), $goresan);
    }
}
