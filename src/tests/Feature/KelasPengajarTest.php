<?php

namespace Tests\Feature;

use App\Models\KelasBelajar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Pengujian UC-07 Mengelola Kelas: kelas, kode bergabung, anggota, tugas, scoreboard. */
class KelasPengajarTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    private function tenggat(int $hari = 3): string
    {
        return now()->addDays($hari)->format('Y-m-d\TH:i');
    }

    private function dataTugas(array $ubah = []): array
    {
        $materi = $this->buatMateri();

        return array_replace([
            'instruksi' => 'Pelajari lalu berlatih.',
            'tenggat' => $this->tenggat(),
            'aksi' => 'draf',
            'komponen' => [
                ['jenis' => 'materi', 'id_materi' => $materi->id_materi, 'mode' => '', 'wajib' => '1'],
                ['jenis' => 'latihan', 'id_materi' => $materi->id_materi, 'mode' => 'bunyi', 'wajib' => '1'],
            ],
        ], $ubah);
    }

    public function test_kode_bergabung_6_karakter_tanpa_huruf_membingungkan(): void
    {
        for ($i = 0; $i < 200; $i++) {
            $this->assertMatchesRegularExpression('/^[A-HJ-NP-Z2-9]{6}$/', KelasBelajar::buatKodeBergabung());
        }
    }

    public function test_pengajar_membuat_kelas_dengan_kode_unik(): void
    {
        $pengajar = $this->buatPengajar();

        $this->actingAs($pengajar)->post('/kelola/kelas', ['nama' => 'Kelas A'])->assertRedirect();
        $this->actingAs($pengajar)->post('/kelola/kelas', ['nama' => 'Kelas B'])->assertRedirect();

        $this->assertSame(2, KelasBelajar::query()->where('pemilik_id', $pengajar->id_akun)->count());
        $this->assertSame(2, KelasBelajar::query()->distinct()->count('kode_bergabung'));
    }

    public function test_nama_kelas_wajib_diisi(): void
    {
        $this->actingAs($this->buatPengajar())->post('/kelola/kelas', ['nama' => '  '])->assertSessionHasErrors('nama');
    }

    public function test_pelajar_tidak_bisa_membuka_kelola_kelas(): void
    {
        $this->actingAs($this->buatPelajar())->get('/kelola/kelas')->assertForbidden();
    }

    public function test_pengajar_lain_tidak_bisa_mengelola_kelas_orang_lain(): void
    {
        $idKelas = $this->buatKelas($this->buatPengajar('Bu Rina'));
        $lain = $this->buatPengajar('Pak Dimas');

        $this->actingAs($lain)->get("/kelola/kelas/$idKelas")->assertForbidden();
        $this->actingAs($lain)->post("/kelola/kelas/$idKelas/ganti-kode")->assertForbidden();
        $this->actingAs($lain)->get("/kelola/kelas/$idKelas/anggota")->assertForbidden();
        $this->actingAs($lain)->post("/kelola/kelas/$idKelas/tugas", $this->dataTugas())->assertForbidden();
    }

    public function test_ganti_kode_membuat_kode_lama_tidak_berlaku_tanpa_mengeluarkan_anggota(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);
        $pelajar = $this->buatPelajar();
        $this->gabung($idKelas, $pelajar);
        $lama = KelasBelajar::find($idKelas)->kode_bergabung;

        $this->actingAs($pengajar)->post("/kelola/kelas/$idKelas/ganti-kode")->assertRedirect();

        $baru = KelasBelajar::find($idKelas)->kode_bergabung;
        $this->assertNotSame($lama, $baru);
        $this->assertFalse(KelasBelajar::query()->where('kode_bergabung', $lama)->exists());
        $this->assertSame('aktif', DB::table('keanggotaan_kelas')->where('id_pelajar', $pelajar->id_akun)->value('status'));
    }

    public function test_keluarkan_anggota_mengubah_status_dan_riwayat_tetap_ada(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);
        $pelajar = $this->buatPelajar();
        $this->gabung($idKelas, $pelajar);
        $this->riwayat($pelajar, $this->buatMateri(), 80);

        $this->actingAs($pengajar)->delete("/kelola/kelas/$idKelas/anggota/{$pelajar->id_akun}")->assertRedirect();

        $this->assertSame('keluar', DB::table('keanggotaan_kelas')->where('id_pelajar', $pelajar->id_akun)->value('status'));
        $this->assertSame(1, DB::table('riwayat_latihan')->where('id_pelajar', $pelajar->id_akun)->count());
    }

    public function test_keluarkan_bukan_anggota_aktif_ditolak(): void
    {
        $pengajar = $this->buatPengajar();
        $kelas = KelasBelajar::find($this->buatKelas($pengajar));

        $hasil = $kelas->keluarkanAnggota($this->buatPelajar()->id_akun);

        $this->assertFalse($hasil->berhasil);
        $this->assertSame('BUKAN_ANGGOTA_AKTIF', $hasil->kode);
    }

    public function test_scoreboard_bisa_dinyalakan_dan_dimatikan(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar, scoreboard: false);

        $this->actingAs($pengajar)->post("/kelola/kelas/$idKelas/scoreboard", ['aktif' => 1]);
        $this->assertTrue(KelasBelajar::find($idKelas)->status_scoreboard);

        $this->actingAs($pengajar)->post("/kelola/kelas/$idKelas/scoreboard", ['aktif' => 0]);
        $this->assertFalse(KelasBelajar::find($idKelas)->status_scoreboard);
    }

    public function test_simpan_draf_lalu_terbitkan_tugas(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);

        $this->actingAs($pengajar)->post("/kelola/kelas/$idKelas/tugas", $this->dataTugas())->assertRedirect();
        $idTugas = (int) DB::table('tugas_kelas')->value('id_tugas');
        $this->assertSame('draf', DB::table('tugas_kelas')->value('status'));
        $this->assertSame(2, DB::table('komponen_tugas')->where('id_tugas', $idTugas)->count());

        $this->actingAs($pengajar)->post("/kelola/kelas/$idKelas/tugas/$idTugas/terbitkan")->assertRedirect();
        $this->assertSame('terbit', DB::table('tugas_kelas')->value('status'));
    }

    public function test_tugas_langsung_terbit(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);

        $this->actingAs($pengajar)->post("/kelola/kelas/$idKelas/tugas", $this->dataTugas(['aksi' => 'terbitkan']))->assertRedirect();

        $this->assertSame('terbit', DB::table('tugas_kelas')->value('status'));
    }

    public function test_tenggat_harus_setelah_sekarang(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);

        $this->actingAs($pengajar)
            ->post("/kelola/kelas/$idKelas/tugas", $this->dataTugas(['tenggat' => now()->subHour()->format('Y-m-d\TH:i')]))
            ->assertSessionHasErrors('tenggat');
        $this->assertSame(0, DB::table('tugas_kelas')->count());
    }

    public function test_komponen_latihan_wajib_memilih_mode(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);
        $materi = $this->buatMateri();

        $this->actingAs($pengajar)->post("/kelola/kelas/$idKelas/tugas", $this->dataTugas([
            'komponen' => [['jenis' => 'latihan', 'id_materi' => $materi->id_materi, 'mode' => '', 'wajib' => '1']],
        ]))->assertSessionHasErrors('komponen.0.mode');
        $this->assertSame(0, DB::table('tugas_kelas')->count());
    }

    public function test_materi_draf_tidak_boleh_menjadi_komponen(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);
        $draf = $this->buatMateri('Materi Draf', 'draf');

        $this->actingAs($pengajar)->post("/kelola/kelas/$idKelas/tugas", $this->dataTugas([
            'komponen' => [['jenis' => 'materi', 'id_materi' => $draf->id_materi, 'wajib' => '1']],
        ]))->assertSessionHasErrors('umum');
        $this->assertSame(0, DB::table('tugas_kelas')->count());
    }

    public function test_tugas_tanpa_komponen_wajib_ditolak(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar);
        $materi = $this->buatMateri();

        $this->actingAs($pengajar)->post("/kelola/kelas/$idKelas/tugas", $this->dataTugas([
            'komponen' => [['jenis' => 'materi', 'id_materi' => $materi->id_materi, 'wajib' => '0']],
        ]))->assertSessionHasErrors('umum');
    }

    public function test_tidak_bisa_menerbitkan_tugas_kelas_lain(): void
    {
        $pengajar = $this->buatPengajar();
        $kelasSaya = $this->buatKelas($pengajar, nama: 'Saya');
        $kelasLain = $this->buatKelas($this->buatPengajar('Lain'), nama: 'Lain');

        $this->actingAs($pengajar)->post("/kelola/kelas/$kelasLain/tugas", $this->dataTugas())->assertForbidden();

        // tugas milik kelasLain tidak bisa diterbitkan lewat URL kelasSaya
        $materi = $this->buatMateri('M2');
        $idTugas = DB::table('tugas_kelas')->insertGetId(['id_kelas' => $kelasLain, 'instruksi' => 'x', 'tenggat' => now()->addDay()->format('Y-m-d H:i:s'), 'status' => 'draf'], 'id_tugas');
        DB::table('komponen_tugas')->insert(['id_tugas' => $idTugas, 'jenis' => 'materi', 'id_materi' => $materi->id_materi, 'mode' => null, 'wajib' => true]);

        $this->actingAs($pengajar)->post("/kelola/kelas/$kelasSaya/tugas/$idTugas/terbitkan");

        $this->assertSame('draf', DB::table('tugas_kelas')->where('id_tugas', $idTugas)->value('status'));
    }

    public function test_halaman_kelas_dan_anggota_tampil(): void
    {
        $pengajar = $this->buatPengajar();
        $idKelas = $this->buatKelas($pengajar, nama: 'Aksara Dasar');
        $this->gabung($idKelas, $this->buatPelajar('Budi'));

        $this->actingAs($pengajar)->get('/kelola/kelas')->assertOk()->assertSee('Aksara Dasar');
        $this->actingAs($pengajar)->get("/kelola/kelas/$idKelas")->assertOk()->assertSee('Kode bergabung');
        $this->actingAs($pengajar)->get("/kelola/kelas/$idKelas/anggota")->assertOk()->assertSee('Budi');
        $this->actingAs($pengajar)->get("/kelola/kelas/$idKelas/tugas/baru")->assertOk();
    }
}
