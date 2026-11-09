<?php

namespace Tests\Unit\Kelas;

use App\Models\PenyelesaianTugas;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StatusKetepatanTest extends TestCase
{
    public static function waktuSelesai(): array
    {
        return [
            'sebelum tenggat' => ['2026-11-11T21:49:59+07:00', 'tepat_waktu'],
            'persis tenggat' => ['2026-11-11T21:50:00+07:00', 'tepat_waktu'],
            'sesudah tenggat' => ['2026-11-11T21:50:01+07:00', 'terlambat'],
            'zona UTC sama' => ['2026-11-11T14:50:00+00:00', 'tepat_waktu'],
            'zona UTC terlambat' => ['2026-11-11T14:50:01+00:00', 'terlambat'],
            'lewat tengah malam' => ['2026-11-12T00:00:00+07:00', 'terlambat'],
        ];
    }

    #[DataProvider('waktuSelesai')]
    public function test_ketepatan_memakai_waktu_terverifikasi_dan_zona_waktu(string $selesai, string $status): void
    {
        $this->assertSame($status, PenyelesaianTugas::tentukanStatusKetepatan(
            new DateTimeImmutable($selesai), new DateTimeImmutable('2026-11-11T21:50:00+07:00'),
        ));
    }

    public function test_tanggal_mutable_tidak_diubah_saat_dibandingkan(): void
    {
        $selesai = new DateTime('2026-11-11T14:49:00+00:00');
        $tenggat = new DateTime('2026-11-11T21:50:00+07:00');
        $sebelum = [$selesai->format('c'), $tenggat->format('c')];
        $this->assertSame('tepat_waktu', PenyelesaianTugas::tentukanStatusKetepatan($selesai, $tenggat));
        $this->assertSame($sebelum, [$selesai->format('c'), $tenggat->format('c')]);
    }
}
