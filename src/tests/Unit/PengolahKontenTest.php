<?php

namespace Tests\Unit;

use App\Support\Materi\PengolahKonten;
use PHPUnit\Framework\TestCase;

class PengolahKontenTest extends TestCase
{
    public function test_aturan_dan_contoh_dipisah_pada_kata_contoh(): void
    {
        $hasil = PengolahKonten::pisahAturanContoh('Tulis dari kiri ke kanan. Contoh: ꦩꦒ = "maga".');

        $this->assertSame('Tulis dari kiri ke kanan.', $hasil['aturan']);
        $this->assertSame('ꦩꦒ = "maga".', $hasil['contoh']);
    }

    public function test_tanpa_kata_contoh_bagian_contoh_kosong(): void
    {
        $hasil = PengolahKonten::pisahAturanContoh('Hanya aturan');

        $this->assertSame('Hanya aturan', $hasil['aturan']);
        $this->assertSame('', $hasil['contoh']);
    }

    public function test_pasangan_contoh_dibaca_dari_kata_dan_bunyi(): void
    {
        $pasang = PengolahKonten::pasangContoh('ꦲꦤ = "ana" (ada); ꦕꦫ = "cara"');

        $this->assertSame([['kata' => 'ꦲꦤ', 'bunyi' => 'ana'], ['kata' => 'ꦕꦫ', 'bunyi' => 'cara']], $pasang);
    }

    public function test_versi_minor_naik_secara_numerik(): void
    {
        $this->assertSame('1.1', PengolahKonten::naikkanMinor('1.0'));
        $this->assertSame('1.10', PengolahKonten::naikkanMinor('1.9'));
        $this->assertSame('2.1', PengolahKonten::naikkanMinor('2'));
    }

    public function test_versi_konten_terbaru_yang_kompatibel_dipilih(): void
    {
        $kandidat = [
            ['versi_konten' => '1.0', 'versi_aplikasi_minimum' => '1.0'],
            ['versi_konten' => '1.10', 'versi_aplikasi_minimum' => '2.0'],
            ['versi_konten' => '1.2', 'versi_aplikasi_minimum' => '1.0'],
        ];

        $this->assertSame('1.2', PengolahKonten::pilihVersi($kandidat, '1.5')['versi_konten']);
        $this->assertSame('1.10', PengolahKonten::pilihVersi($kandidat, '2.0')['versi_konten']);
        $this->assertNull(PengolahKonten::pilihVersi([['versi_konten' => '1.0', 'versi_aplikasi_minimum' => '3.0']], '1.0'));
    }
}
