<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Audit proteksi route. Setiap route harus wajib login dan punya
 * aturan peran yang sesuai peta di bawah. Route baru yang belum masuk peta
 * akan gagal di sini, dan itu disengaja: tambahkan aturannya di peranDiharapkan().
 */
class RuteProtectedTest extends TestCase
{
    /** Route publik atau bawaan framework, dikecualikan dari audit (berdasarkan URI). */
    private const KECUALI_URI = ['up', 'masuk', 'daftar'];

    private const TIM_MATERI = [
        'materi.kelola', 'materi.buat', 'materi.simpan', 'materi.sunting',
        'materi.perbarui', 'materi.arsipkan', 'materi.terbitkan',
    ];

    /** [] = semua peran yang sudah masuk. null = belum ada aturan. */
    private function peranDiharapkan(string $nama): ?array
    {
        return match (true) {
            in_array($nama, ['beranda', 'keluar'], true) => [],
            in_array($nama, self::TIM_MATERI, true) => ['tim_materi'],
            str_starts_with($nama, 'materi.'),
            str_starts_with($nama, 'latihan.'),
            str_starts_with($nama, 'progres.'),
            str_starts_with($nama, 'kelas-pelajar.') => ['pelajar'],
            str_starts_with($nama, 'kelas-pengajar.'),
            str_starts_with($nama, 'pemantauan.') => ['pengajar'],
            str_starts_with($nama, 'feedback.') => ['pelajar', 'pengajar'],
            default => null,
        };
    }

    public function test_semua_route_wajib_login_dan_sesuai_peta_peran(): void
    {
        $diperiksa = 0;

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            if (in_array($uri, self::KECUALI_URI, true) || str_starts_with($uri, 'storage/')) {
                continue;
            }

            $nama = $route->getName();
            $this->assertNotNull($nama, "Route {$uri} belum diberi nama.");

            $middleware = collect($route->gatherMiddleware())->filter(fn ($m) => is_string($m));
            $this->assertContains('auth', $middleware->all(), "Route {$nama} ({$uri}) tidak dilindungi auth.");

            $diharapkan = $this->peranDiharapkan($nama);
            $this->assertNotNull($diharapkan, "Route {$nama} belum punya aturan peran di peta (RuteTerlindungiTest).");

            $aturan = $middleware->filter(fn ($m) => str_starts_with($m, 'peran:'))->values();

            if ($diharapkan === []) {
                $this->assertCount(0, $aturan, "Route {$nama} seharusnya terbuka bagi semua peran.");
            } else {
                $this->assertCount(1, $aturan, "Route {$nama} harus punya tepat satu aturan peran.");
                $peran = explode(',', substr($aturan[0], 6));
                sort($peran);
                sort($diharapkan);
                $this->assertSame($diharapkan, $peran, "Peran untuk {$nama} tidak sesuai peta.");
            }

            $diperiksa++;
        }

        $this->assertGreaterThan(0, $diperiksa);
    }
}