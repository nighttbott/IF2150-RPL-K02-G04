<?php

namespace Tests\Unit\Hiupl;

use App\Models\PenyelesaianTugas;
use App\Models\RiwayatLatihan;
use App\Services\PengumpulanTugas;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\TestCase;

/**
 * HIUPL 4.2: pengujian unit kelas C26 PenyelesaianTugas (tingkat Mendalam: aturan ketepatan waktu dan transaksi pengumpulan).
 * simpanPengumpulan pada DPPL diwujudkan oleh layanan App\Services\PengumpulanTugas.
 */
class C26PenyelesaianTugasTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    /** Menyiapkan kelas, satu tugas terbit dengan satu komponen latihan wajib, dan Pelajar anggota. */
    private function siapkan(string $tenggat): array
    {
        $pelajar = $this->buatPelajar();
        $materi = $this->buatMateri();
        $idKelas = $this->buatKelas($this->buatPengajar());
        $this->gabung($idKelas, $pelajar);
        $idTugas = DB::table('tugas_kelas')->insertGetId(['id_kelas' => $idKelas, 'instruksi' => 'Uji', 'tenggat' => $tenggat, 'status' => 'terbit'], 'id_tugas');
        $idKomponen = DB::table('komponen_tugas')->insertGetId(['id_tugas' => $idTugas, 'jenis' => 'latihan', 'id_materi' => $materi->id_materi, 'mode' => 'menulis', 'wajib' => true], 'id_komponen');

        return [$pelajar, $materi, $idKelas, $idTugas, $idKomponen];
    }

    private function selesaikanKomponen($pelajar, $materi, int $idTugas, int $idKomponen, string $waktu): void
    {
        $h = RiwayatLatihan::simpanHasil(['id_pelajar' => $pelajar->id_akun, 'id_materi' => $materi->id_materi, 'target_soal' => 'ꦲ', 'mode' => 'menulis',
            'skor' => 90, 'status' => 'berhasil', 'id_tugas' => $idTugas, 'id_komponen' => $idKomponen]);
        DB::table('riwayat_latihan')->where('id_riwayat', $h->ambil('idRiwayat'))->update(['waktu_selesai' => $waktu]);
    }

    /** @uji C26-UT01 | Normal | tentukanStatusKetepatan(waktuSelesai: DateTime, tenggat: DateTime): String | Selesai 10-11-2026 20.00, tenggat 11-11-2026 21.50 | "tepat_waktu" */
    #[TestDox('C26-UT01 Normal: selesai sebelum tenggat tepat waktu')]
    public function test_ut01_tepat_waktu(): void
    {
        $this->assertSame('tepat_waktu', PenyelesaianTugas::tentukanStatusKetepatan(CarbonImmutable::parse('2026-11-10 20:00'), CarbonImmutable::parse('2026-11-11 21:50')));
    }

    /** @uji C26-UT02 | Batas | tentukanStatusKetepatan(waktuSelesai: DateTime, tenggat: DateTime): String | Selesai tepat pada detik tenggat, dan satu detik sesudahnya | Tepat di tenggat: "tepat_waktu"; +1 detik: "terlambat" */
    #[TestDox('C26-UT02 Batas: tepat di tenggat masih tepat waktu, satu detik sesudahnya terlambat')]
    public function test_ut02_tepat_di_tenggat(): void
    {
        $tenggat = CarbonImmutable::parse('2026-11-11 21:50:00');

        $this->assertSame('tepat_waktu', PenyelesaianTugas::tentukanStatusKetepatan($tenggat, $tenggat));
        $this->assertSame('terlambat', PenyelesaianTugas::tentukanStatusKetepatan($tenggat->addSecond(), $tenggat));
    }

    /** @uji C26-UT03 | Negatif | tentukanStatusKetepatan(waktuSelesai: DateTime, tenggat: DateTime): String | Selesai sehari setelah tenggat | "terlambat" */
    #[TestDox('C26-UT03 Negatif: selesai setelah tenggat terlambat')]
    public function test_ut03_terlambat(): void
    {
        $this->assertSame('terlambat', PenyelesaianTugas::tentukanStatusKetepatan(CarbonImmutable::parse('2026-11-12 08:00'), CarbonImmutable::parse('2026-11-11 21:50')));
    }

    /** @uji C26-UT04 | Normal | simpanPengumpulan(idPelajar, idTugas, waktuSelesai, tenggat): HasilOperasi | Komponen wajib selesai sebelum tenggat lalu tugas dikumpulkan | Berhasil (tugasDikumpulkan) dengan status tepat_waktu; baris penyelesaian dan catatan pengumpulan tersimpan */
    #[TestDox('C26-UT04 Normal: pengumpulan lengkap tersimpan tepat waktu')]
    public function test_ut04_kumpul_tepat_waktu(): void
    {
        [$p, $m, $k, $t, $c] = $this->siapkan(now()->addDay()->format('Y-m-d H:i:s'));
        $this->selesaikanKomponen($p, $m, $t, $c, now()->subMinute()->format('Y-m-d H:i:s'));
        $hasil = app(PengumpulanTugas::class)->simpanPengumpulan($k, $t, $p->id_akun);

        $this->assertSame(['tugasDikumpulkan', 'tepat_waktu'], [$hasil->kode, $hasil->data['status']]);
        $this->assertSame(1, DB::table('penyelesaian_tugas')->count());
        $this->assertSame(1, DB::table('riwayat_latihan')->where('mode_aktivitas', 'pengumpulan')->count());
    }

    /** @uji C26-UT05 | Batas | simpanPengumpulan(idPelajar, idTugas, waktuSelesai, tenggat): HasilOperasi | (a) komponen selesai setelah tenggat; (b) pengumpulan diulang | (a) status terlambat; (b) sudahDikumpulkan tanpa baris ganda */
    #[TestDox('C26-UT05 Batas: terlambat dicatat dan pengumpulan ulang tidak menggandakan')]
    public function test_ut05_kumpul_terlambat_dan_ulang(): void
    {
        [$p, $m, $k, $t, $c] = $this->siapkan(now()->subDay()->format('Y-m-d H:i:s'));
        $this->selesaikanKomponen($p, $m, $t, $c, now()->subMinute()->format('Y-m-d H:i:s'));
        $layanan = app(PengumpulanTugas::class);

        $this->assertSame('terlambat', $layanan->simpanPengumpulan($k, $t, $p->id_akun)->data['status']);
        $this->assertSame('sudahDikumpulkan', $layanan->simpanPengumpulan($k, $t, $p->id_akun)->kode);
        $this->assertSame(1, DB::table('penyelesaian_tugas')->count());
    }

    /** @uji C26-UT06 | Negatif | simpanPengumpulan(idPelajar, idTugas, waktuSelesai, tenggat): HasilOperasi | (a) komponen wajib belum selesai; (b) pengguna bukan anggota kelas | (a) komponenBelumLengkap; (b) aksesDitolak; tidak ada baris penyelesaian */
    #[TestDox('C26-UT06 Negatif: komponen belum lengkap dan bukan anggota ditolak')]
    public function test_ut06_kumpul_ditolak(): void
    {
        [$p, , $k, $t] = $this->siapkan(now()->addDay()->format('Y-m-d H:i:s'));
        $layanan = app(PengumpulanTugas::class);

        $this->assertSame('komponenBelumLengkap', $layanan->simpanPengumpulan($k, $t, $p->id_akun)->kode);
        $this->assertSame('aksesDitolak', $layanan->simpanPengumpulan($k, $t, $this->buatPelajar('Orang Lain')->id_akun)->kode);
        $this->assertSame(0, DB::table('penyelesaian_tugas')->count());
    }

    /** @uji C26-UT07 | Normal | ambilStatus(idPelajar: Integer, daftarTugas: List): List | Pelajar sudah mengumpulkan satu tugas | Status tugas tersebut tersedia (tepat_waktu) */
    #[TestDox('C26-UT07 Normal: ambilStatus mengembalikan status tugas yang dikumpulkan')]
    public function test_ut07_ambil_status(): void
    {
        [$p, $m, $k, $t, $c] = $this->siapkan(now()->addDay()->format('Y-m-d H:i:s'));
        $this->selesaikanKomponen($p, $m, $t, $c, now()->subMinute()->format('Y-m-d H:i:s'));
        app(PengumpulanTugas::class)->simpanPengumpulan($k, $t, $p->id_akun);
        $status = PenyelesaianTugas::ambilStatus($p->id_akun, [$t]);

        $this->assertSame('tepat_waktu', $status->first()['status']);
        $this->assertNotNull($status->first()['waktu_kumpul']);
    }

    /** @uji C26-UT08 | Batas | ambilStatus(idPelajar: Integer, daftarTugas: List): List | Daftar tugas kosong | Koleksi kosong */
    #[TestDox('C26-UT08 Batas: daftar tugas kosong menghasilkan koleksi kosong')]
    public function test_ut08_ambil_status_kosong(): void
    {
        $this->assertCount(0, PenyelesaianTugas::ambilStatus($this->buatPelajar()->id_akun, []));
    }

    /** @uji C26-UT10 | Negatif | ambilStatus(idPelajar: Integer, daftarTugas: List): List | Daftar tugas berisi ID 0 (tidak valid) | InvalidArgumentException dilempar */
    #[TestDox('C26-UT10 Negatif: ID tugas tidak valid ditolak')]
    public function test_ut10_ambil_status_id_tidak_valid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PenyelesaianTugas::ambilStatus(1, [0]);
    }

    /** @uji C26-UT09 | Negatif | ambilStatus(idPelajar: Integer, daftarTugas: List): List | Pelajar lain yang tidak mengumpulkan menanyakan tugas yang sama | Status belum_selesai (status milik Pelajar lain tidak terbawa) */
    #[TestDox('C26-UT09 Negatif: status milik Pelajar lain tidak terbawa')]
    public function test_ut09_ambil_status_pelajar_lain(): void
    {
        [$p, $m, $k, $t, $c] = $this->siapkan(now()->addDay()->format('Y-m-d H:i:s'));
        $this->selesaikanKomponen($p, $m, $t, $c, now()->subMinute()->format('Y-m-d H:i:s'));
        app(PengumpulanTugas::class)->simpanPengumpulan($k, $t, $p->id_akun);

        $this->assertSame('belum_selesai', PenyelesaianTugas::ambilStatus($this->buatPelajar('Lain')->id_akun, [$t])->first()['status']);
    }
}
