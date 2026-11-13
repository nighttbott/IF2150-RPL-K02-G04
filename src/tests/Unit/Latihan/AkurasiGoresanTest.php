<?php

namespace Tests\Unit\Latihan;

use App\Latihan\AkurasiGoresan;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Algo-023: goresan Pelajar (garis tengah huruf yang ditebalkan) dinilai terhadap garis tengah templat, dengan toleransi ketebalan dari kontur. */
class AkurasiGoresanTest extends TestCase
{
    /** @var array<string, array{goresan: list<list<array{x: float, y: float}>>, tengah: list<list<array{x: float, y: float}>>}> */
    private static array $templat;

    private AkurasiGoresan $akurasi;

    public static function setUpBeforeClass(): void
    {
        $json = json_decode(file_get_contents(__DIR__.'/../../../resources/aksara/template_aksara.json'), true);
        self::$templat = array_column($json['aksara'], null, 'karakter');
    }

    protected function setUp(): void
    {
        mt_srand(1);
        // Sama dengan config/latihan.php.
        $this->akurasi = new AkurasiGoresan(200, 0.05, 0.25, 0.5);
    }

    private function nilai(array $goresan, string $aksara): float
    {
        return $this->akurasi->hitung($goresan, self::$templat[$aksara]['tengah'], self::$templat[$aksara]['goresan']);
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

    public function test_semua_templat_punya_garis_tengah(): void
    {
        foreach (self::$templat as $k => $t) {
            $this->assertNotEmpty($t['tengah'] ?? [], "aksara $k");
        }
    }

    public function test_menebalkan_tepat_di_tengah_bernilai_seratus(): void
    {
        foreach (['ꦲ', 'ꦏ', 'ꦤ', 'ꦩ', 'ᮊ', 'ᮞ'] as $aksara) {
            $this->assertSame(100.0, $this->nilai(self::$templat[$aksara]['tengah'], $aksara), "aksara $aksara");
        }
    }

    public function test_menebalkan_dengan_tangan_bergetar_tetap_lulus(): void
    {
        foreach (['ꦲ', 'ꦏ', 'ꦤ', 'ꦩ', 'ꦗ', 'ᮊ', 'ᮞ'] as $aksara) {
            $skor = $this->nilai($this->derau(self::$templat[$aksara]['tengah'], 1.5), $aksara);
            $this->assertGreaterThanOrEqual(80, $skor, "aksara $aksara");
        }
    }

    public function test_menelusuri_tepi_outline_juga_lulus(): void
    {
        // Pelajar yang menggambar di pinggir badan huruf (bukan persis di tengah) tidak dihukum berat.
        foreach (['ꦲ', 'ꦏ', 'ꦩ'] as $aksara) {
            $this->assertGreaterThanOrEqual(70, $this->nilai(self::$templat[$aksara]['goresan'], $aksara), "aksara $aksara");
        }
    }

    public function test_coretan_nyasar_kecil_dimaafkan(): void
    {
        $t = self::$templat['ꦲ']['tengah'];
        $nyasar = [array_map(fn ($i) => ['x' => 5 + $i, 'y' => 95], range(0, 8))];

        $this->assertGreaterThanOrEqual(90, $this->nilai([...$t, ...$nyasar], 'ꦲ'));
    }

    public function test_goresan_separuh_tidak_lulus(): void
    {
        foreach (['ꦲ', 'ꦩ', 'ᮊ'] as $aksara) {
            $separuh = array_map(fn ($g) => array_slice($g, 0, intdiv(count($g), 2)), self::$templat[$aksara]['tengah']);
            $this->assertLessThan(70, $this->nilai($separuh, $aksara), "aksara $aksara");
        }
    }

    public function test_aksara_lain_tidak_lulus(): void
    {
        $this->assertLessThan(70, $this->nilai(self::$templat['ꦗ']['tengah'], 'ꦲ'));
        $this->assertLessThan(70, $this->nilai(self::$templat['ꦕ']['tengah'], 'ꦫ'));
        $this->assertLessThan(70, $this->nilai(self::$templat['ᮙ']['tengah'], 'ᮊ'));
    }

    public function test_geser_dan_perbesar_tidak_mengubah_skor(): void
    {
        $t = self::$templat['ꦏ']['tengah'];
        $goresan = array_map(fn ($g) => array_map(fn ($p) => ['x' => $p['x'] / 100, 'y' => $p['y'] / 100], $g), $this->derau($t, 1));
        $diubah = array_map(fn ($g) => array_map(fn ($p) => ['x' => $p['x'] * 3 + 0.7, 'y' => $p['y'] * 3 - 0.2], $g), $goresan);

        $this->assertEqualsWithDelta($this->nilai($goresan, 'ꦏ'), $this->nilai($diubah, 'ꦏ'), 0.1);
    }

    public function test_urutan_dan_arah_goresan_dibalik_skor_hampir_sama(): void
    {
        $goresan = $this->derau(self::$templat['ꦤ']['tengah'], 1);
        $dibalik = array_reverse(array_map('array_reverse', $goresan));

        $this->assertEqualsWithDelta($this->nilai($goresan, 'ꦤ'), $this->nilai($dibalik, 'ꦤ'), 1.0);
    }

    public function test_hasil_di_rentang_nol_sampai_seratus_satu_desimal(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $acak = [array_map(fn () => ['x' => mt_rand() / mt_getrandmax(), 'y' => mt_rand() / mt_getrandmax()], range(1, 30))];
            $skor = $this->nilai($acak, 'ꦩ');
            $this->assertGreaterThanOrEqual(0, $skor);
            $this->assertLessThanOrEqual(100, $skor);
            $this->assertSame(round($skor, 1), $skor);
        }
    }

    /** Derau halus (getaran tangan) pada tiap titik, dalam satuan kotak templat 0-100. */
    private function derau(array $goresan, float $simpangan): array
    {
        return array_map(function ($g) use ($simpangan) {
            $dx = $dy = 0.0;

            return array_map(function ($p) use ($simpangan, &$dx, &$dy) {
                $u = max(mt_rand() / mt_getrandmax(), 1e-9);
                $v = mt_rand() / mt_getrandmax();
                $r = sqrt(-2 * log($u)) * $simpangan;
                // Getaran berkorelasi antartitik, seperti tangan sungguhan.
                $dx = 0.7 * $dx + 0.3 * $r * cos(2 * M_PI * $v);
                $dy = 0.7 * $dy + 0.3 * $r * sin(2 * M_PI * $v);

                return ['x' => $p['x'] + $dx, 'y' => $p['y'] + $dy];
            }, $g);
        }, $goresan);
    }
}
