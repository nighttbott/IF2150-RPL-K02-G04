<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Rentang waktu untuk menyaring riwayat (tipe Periode pada DPPL).
 * Batas awal dan akhir sama-sama termasuk (inklusif).
 */
final class Periode
{
    public function __construct(
        public readonly CarbonImmutable $mulai,
        public readonly CarbonImmutable $selesai,
    ) {}

    /** Periode N hari terakhir sampai akhir hari ini. */
    public static function hariTerakhir(int $jumlahHari): self
    {
        $selesai = CarbonImmutable::now()->endOfDay();

        return new self($selesai->subDays(max(1, $jumlahHari) - 1)->startOfDay(), $selesai);
    }

    /** Periode tanpa batas, dipakai untuk pilihan "semua riwayat". */
    public static function semua(): self
    {
        return new self(CarbonImmutable::create(2000, 1, 1), CarbonImmutable::now()->addYears(50));
    }

    /**
     * Membuat periode dari pilihan pada halaman (7, 30, 90, atau semua).
     * Nilai yang tidak dikenal dianggap 30 hari terakhir.
     */
    public static function dariPilihan(?string $pilihan): self
    {
        return match ($pilihan) {
            '7' => self::hariTerakhir(7),
            '90' => self::hariTerakhir(90),
            'semua' => self::semua(),
            default => self::hariTerakhir(30),
        };
    }

    /** Pasangan [mulai, selesai] dalam format waktu basis data, untuk whereBetween. */
    public function rentang(): array
    {
        return [$this->mulai->format('Y-m-d H:i:s'), $this->selesai->format('Y-m-d H:i:s')];
    }
}
