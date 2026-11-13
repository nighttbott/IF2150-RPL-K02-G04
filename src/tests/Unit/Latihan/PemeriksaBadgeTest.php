<?php

namespace Tests\Unit\Latihan;

use App\Latihan\PemeriksaBadge;
use PHPUnit\Framework\TestCase;

class PemeriksaBadgeTest extends TestCase
{
    public function test_aturan_badge(): void
    {
        $badge = new PemeriksaBadge;

        $this->assertSame([], $badge->periksa(1, 9, []));
        $this->assertSame(['pemula'], $badge->periksa(1, 10, []));
        $this->assertSame(['seratus_poin'], $badge->periksa(1, 100, ['pemula']));
        $this->assertSame(['konsisten_7'], $badge->periksa(7, 50, ['pemula']));
        $this->assertSame([], $badge->periksa(8, 120, ['pemula', 'konsisten_7', 'seratus_poin']));
    }

    public function test_kode_sama_dengan_daftar_badge_motivasi(): void
    {
        // Halaman progres (I-11) hanya mengenali kode yang ada di MotivasiBelajar::BADGE.
        $this->assertSame(array_keys(\App\Models\MotivasiBelajar::BADGE), array_keys(PemeriksaBadge::DESKRIPSI));
    }
}
