<?php

namespace Tests\Unit\Latihan;

use App\Enums\ModeLatihan;
use App\Latihan\Data\HasilLatihan;
use App\Latihan\Data\RingkasanMotivasi;
use App\Latihan\Data\SoalLatihan;
use PHPUnit\Framework\TestCase;

class DataLatihanTest extends TestCase
{
    /** Meniru session Laravel dengan serialisasi JSON (config/session.php). */
    private function lewatJson(array $data): array
    {
        return json_decode(json_encode($data), true);
    }

    public function test_tipe_data_utuh_setelah_lewat_session_json(): void
    {
        $soal = new SoalLatihan('a1', 1, ModeLatihan::Menulis, 'ꦲ', 'ha', templat: [[['x' => 10.5, 'y' => 20], ['x' => 30, 'y' => 40]]]);
        $hasil = new HasilLatihan(5, 1, 'a1', 'ꦲ', ModeLatihan::Menulis, 88.0, 'berhasil');
        $ringkasan = new RingkasanMotivasi(3, 40, true, ['pemula']);

        $this->assertEquals($soal, SoalLatihan::fromArray($this->lewatJson($soal->toArray())));
        $this->assertEquals($hasil, HasilLatihan::fromArray($this->lewatJson($hasil->toArray())));
        $this->assertEquals($ringkasan, RingkasanMotivasi::fromArray($this->lewatJson($ringkasan->toArray())));
    }
}
