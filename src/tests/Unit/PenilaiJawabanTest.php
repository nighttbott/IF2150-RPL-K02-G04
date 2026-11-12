<?php

namespace Tests\Unit;

use App\Services\Latihan\PenilaiJawaban;
use PHPUnit\Framework\TestCase;

class PenilaiJawabanTest extends TestCase
{
    public function test_bunyi_benar_dan_salah(): void
    {
        $this->assertSame(100.0, PenilaiJawaban::bunyi('b', 'b')->skor);
        $this->assertTrue(PenilaiJawaban::bunyi('b', 'b')->benar);
        $this->assertSame(0.0, PenilaiJawaban::bunyi('a', 'b')->skor);
    }

    public function test_bunyi_tanpa_jawaban_bernilai_nol(): void
    {
        $this->assertSame(0.0, PenilaiJawaban::bunyi(null, 'b')->skor);
        $this->assertSame(0.0, PenilaiJawaban::bunyi('', 'b')->skor);
    }

    public function test_merangkai_benar_penuh(): void
    {
        $hasil = PenilaiJawaban::merangkai(['ꦲ', 'ꦤ'], ['ꦲ', 'ꦤ']);

        $this->assertTrue($hasil->benar);
        $this->assertSame(100.0, $hasil->skor);
        $this->assertSame([], $hasil->posisiSalah);
    }

    public function test_merangkai_menandai_posisi_yang_salah(): void
    {
        $hasil = PenilaiJawaban::merangkai(['ꦲ', 'ꦕ', 'ꦤ', 'ꦫ'], ['ꦲ', 'ꦤ', 'ꦕ', 'ꦫ']);

        $this->assertFalse($hasil->benar);
        $this->assertSame([1, 2], $hasil->posisiSalah);
        $this->assertSame(50.0, $hasil->skor);
    }

    public function test_merangkai_kurang_atau_kelebihan_ubin_dianggap_salah(): void
    {
        $kurang = PenilaiJawaban::merangkai(['ꦲ'], ['ꦲ', 'ꦤ']);
        $this->assertSame([1], $kurang->posisiSalah);
        $this->assertSame(50.0, $kurang->skor);

        $lebih = PenilaiJawaban::merangkai(['ꦲ', 'ꦤ', 'ꦕ'], ['ꦲ', 'ꦤ']);
        $this->assertSame([2], $lebih->posisiSalah);
    }

    public function test_merangkai_kosong_tidak_error(): void
    {
        $this->assertSame(0.0, PenilaiJawaban::merangkai([], [])->skor);
        $this->assertSame(0.0, PenilaiJawaban::merangkai([], ['ꦲ', 'ꦤ'])->skor);
    }

    public function test_transliterasi_mengabaikan_spasi_tepi_dan_huruf_besar(): void
    {
        $this->assertTrue(PenilaiJawaban::transliterasi('  ANA ', 'ana')->benar);
        $this->assertFalse(PenilaiJawaban::transliterasi('anu', 'ana')->benar);
    }

    public function test_transliterasi_kosong_bernilai_nol(): void
    {
        $this->assertSame(0.0, PenilaiJawaban::transliterasi('   ', 'ana')->skor);
        $this->assertSame(0.0, PenilaiJawaban::transliterasi(null, 'ana')->skor);
    }
}
