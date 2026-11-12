<?php

namespace Tests\Feature;

use App\Models\KeanggotaanKelas;
use App\Models\KelasBelajar;
use App\Models\TugasKelas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pengujian UC-07 Mengelola Kelas (Pengajar): KelasController (C20) bagian pengelolaan,
 * KelasBelajar (C23), KeanggotaanKelas (C24), TugasKelas (C25).
 */
class KelasPengajarTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    private function kelasBaru($pemilik, string $nama = 'Aksara Jawa Dasar'): KelasBelajar
    {
        $kelas = new KelasBelajar;
        $this->assertTrue($kelas->simpan(['nama' => $nama, 'pemilik_id' => $pemilik->id_akun])->berhasil);

        return $kelas;
    }

    private function dataTugas(array $ganti = []): array
    {
        $materi = $this->buatMateri();

        return $ganti + [
            'instruksi' => 'Pelajari materi nglegena.',
            'tenggat' => now()->addDay()->format('Y-m-d\TH:i'),
            'komponen' => [
                ['materi' => $materi->id_materi, 'jenis' => 'materi', 'wajib' => 1],
                ['materi' => $materi->id_materi, 'jenis' => 'bunyi', 'wajib' => 0],
            ],
        ];
    }

    public function test_pengajar_membuat_kelas_dengan_kode_valid_dan_status_huruf_kecil(): void
    {
        $pengajar = $this->buatPengajar();

        $this->actingAs($pengajar)->post(route('pengajar.kelas.buat'), ['nama' => 'Aksara Jawa Dasar'])
            ->assertRedirect(route('pengajar.kelas.index'))->assertSessionHas('sukses');

        $kelas = KelasBelajar::firstOrFail();
        $this->assertSame((int) $pengajar->id_akun, (int) $kelas->pemilik_id);
        $this->assertSame('aktif', $kelas->status);
        $this->assertFalse($kelas->status_scoreboard);
        $this->assertMatchesRegularExpression('/^[ABCDEFGHJKLMNPQRSTUVWXYZ2-9]{6}$/', $kelas->kode_bergabung);
    }

    public function test_nama_kelas_kosong_atau_terlalu_panjang_ditolak(): void
    {
        $pengajar = $this->buatPengajar();

        $this->actingAs($pengajar)->post(route('pengajar.kelas.buat'), ['nama' => ''])->assertSessionHasErrors('nama');
        $this->actingAs($pengajar)->post(route('pengajar.kelas.buat'), ['nama' => str_repeat('a', 101)])->assertSessionHasErrors('nama');
        $this->assertSame(0, KelasBelajar::count());
    }

    public function test_pelajar_tidak_boleh_membuat_atau_membuka_kelola_kelas(): void
    {
        $pelajar = $this->buatPelajar();

        $this->actingAs($pelajar)->post(route('pengajar.kelas.buat'), ['nama' => 'Kelas Gelap'])->assertForbidden();
        $this->actingAs($pelajar)->get(route('pengajar.kelas.index'))->assertForbidden();
        $this->assertSame(0, KelasBelajar::count());
    }

    public function test_tamu_diarahkan_masuk(): void
    {
        $this->get(route('pengajar.kelas.index'))->assertRedirect();
    }

    public function test_kode_bergabung_tidak_ambigu_dan_hampir_selalu_unik(): void
    {
        $kelas = new KelasBelajar;
        $dibuat = [];
        for ($i = 0; $i < 200; $i++) {
            $kode = $kelas->buatKodeBergabung();
            $this->assertMatchesRegularExpression('/^[^OI01]{6}$/', $kode);
            $dibuat[$kode] = true;
        }
        $this->assertGreaterThan(190, count($dibuat));
    }

    public function test_ganti_kode_membuat_kode_lama_tidak_berlaku(): void
    {
        $pengajar = $this->buatPengajar();
        $kelas = $this->kelasBaru($pengajar);
        $kodeLama = $kelas->kode_bergabung;

        $this->actingAs($pengajar)->post(route('pengajar.kelas.ganti-kode', $kelas->id_kelas))
            ->assertRedirect(route('pengajar.kelas.index'));

        $this->assertDatabaseMissing('kelas_belajar', ['kode_bergabung' => $kodeLama]);
    }

    public function test_pengajar_lain_tidak_boleh_mengelola_kelas_bukan_miliknya(): void
    {
        $kelas = $this->kelasBaru($this->buatPengajar('Bu Rina'));
        $kodeAsli = $kelas->kode_bergabung;
        $penyusup = $this->buatPengajar('Pak Dimas');
        $id = $kelas->id_kelas;

        $this->actingAs($penyusup)->post(route('pengajar.kelas.ganti-kode', $id))->assertForbidden();
        $this->actingAs($penyusup)->get(route('pengajar.kelas.anggota', $id))->assertForbidden();
        $this->actingAs($penyusup)->post(route('pengajar.kelas.scoreboard', $id), ['aktif' => 1])->assertForbidden();
        $this->actingAs($penyusup)->get(route('pengajar.kelas.tugas', $id))->assertForbidden();
        $this->actingAs($penyusup)->post(route('pengajar.kelas.terbitkan', $id), $this->dataTugas())->assertForbidden();

        $this->assertSame($kodeAsli, $kelas->fresh()->kode_bergabung);
        $this->assertFalse($kelas->fresh()->status_scoreboard);
        $this->assertSame(0, TugasKelas::count());
    }

    public function test_kelas_tidak_ada_atau_nonaktif_mengembalikan_404(): void
    {
        $pengajar = $this->buatPengajar();
        $this->actingAs($pengajar)->post(route('pengajar.kelas.ganti-kode', 9999))->assertNotFound();

        $kelas = $this->kelasBaru($pengajar);
        $kelas->update(['status' => 'nonaktif']);
        $this->actingAs($pengajar)->get(route('pengajar.kelas.anggota', $kelas->id_kelas))->assertNotFound();
    }

    public function test_scoreboard_dapat_diaktifkan_dan_dimatikan(): void
    {
        $pengajar = $this->buatPengajar();
        $kelas = $this->kelasBaru($pengajar);

        $this->actingAs($pengajar)->post(route('pengajar.kelas.scoreboard', $kelas->id_kelas), ['aktif' => 1]);
        $this->assertTrue($kelas->fresh()->status_scoreboard);
        $this->actingAs($pengajar)->post(route('pengajar.kelas.scoreboard', $kelas->id_kelas), ['aktif' => 0]);
        $this->assertFalse($kelas->fresh()->status_scoreboard);
    }

    public function test_halaman_kelola_kelas_hanya_menampilkan_kelas_milik_sendiri_dengan_tautan_pantau(): void
    {
        $saya = $this->buatPengajar('Saya');
        $kelas = $this->kelasBaru($saya, 'Kelas Saya');
        $this->kelasBaru($this->buatPengajar('Lain'), 'Kelas Orang Lain');

        $this->actingAs($saya)->get(route('pengajar.kelas.index'))->assertOk()
            ->assertSee('Kelas Saya')->assertDontSee('Kelas Orang Lain')
            ->assertSee(route('pemantauan.index', $kelas->id_kelas), false);
    }

    public function test_halaman_anggota_hanya_menampilkan_nama_dan_tidak_membocorkan_email(): void
    {
        $pengajar = $this->buatPengajar();
        $kelas = $this->kelasBaru($pengajar);
        $sari = $this->buatPelajar('Sari');
        $raka = $this->buatPelajar('Raka');
        $this->gabung($kelas->id_kelas, $sari);
        $this->gabung($kelas->id_kelas, $raka, 'keluar');

        $this->actingAs($pengajar)->get(route('pengajar.kelas.anggota', $kelas->id_kelas))->assertOk()
            ->assertSee('Sari')->assertDontSee('Raka')
            ->assertDontSee($sari->email)->assertDontSee($sari->username)->assertDontSee('password');
    }

    public function test_keluarkan_anggota_mengubah_status_nonaktif_tanpa_menghapus_riwayat(): void
    {
        $pengajar = $this->buatPengajar();
        $kelas = $this->kelasBaru($pengajar);
        $sari = $this->buatPelajar('Sari');
        $this->gabung($kelas->id_kelas, $sari);
        $this->riwayat($sari, $this->buatMateri(), 80);

        $this->actingAs($pengajar)->post(route('pengajar.kelas.keluarkan', [$kelas->id_kelas, $sari->id_akun]))
            ->assertRedirect(route('pengajar.kelas.anggota', $kelas->id_kelas));

        $this->assertSame('nonaktif', KeanggotaanKelas::cari($kelas->id_kelas, $sari->id_akun)->status);
        $this->assertSame(1, DB::table('riwayat_latihan')->where('id_pelajar', $sari->id_akun)->count());
        $this->assertCount(0, KeanggotaanKelas::daftarAnggotaAktif($kelas->id_kelas));
    }

    public function test_keluarkan_pelajar_yang_bukan_anggota_aktif_ditolak(): void
    {
        $pengajar = $this->buatPengajar();
        $kelas = $this->kelasBaru($pengajar);
        $asing = $this->buatPelajar('Asing');
        $sudahKeluar = $this->buatPelajar('Keluar');
        $this->gabung($kelas->id_kelas, $sudahKeluar, 'keluar');

        foreach ([$asing, $sudahKeluar] as $p) {
            $this->actingAs($pengajar)->post(route('pengajar.kelas.keluarkan', [$kelas->id_kelas, $p->id_akun]))
                ->assertSessionHasErrors('anggota');
        }
        $this->assertSame('keluar', KeanggotaanKelas::cari($kelas->id_kelas, $sudahKeluar->id_akun)->status);
    }

    public function test_papan_peringkat_memakai_poin_asli_dan_peringkat_sama_untuk_poin_sama(): void
    {
        $pengajar = $this->buatPengajar();
        $kelas = $this->kelasBaru($pengajar);
        $kelas->aturScoreboard(true);
        foreach (['Sari' => 50, 'Budi' => 30, 'Wulan' => 30] as $nama => $poin) {
            $p = $this->buatPelajar($nama);
            $this->gabung($kelas->id_kelas, $p);
            \App\Models\MotivasiBelajar::milikPelajar($p->id_akun)->tambahPoin(1, $poin);
        }

        $this->actingAs($pengajar)->get(route('pengajar.kelas.anggota', $kelas->id_kelas))->assertOk()
            ->assertSeeInOrder(['1. Sari', '50', '2. Budi', '30', '2. Wulan', '30']);
    }

    public function test_tenggat_di_masa_lalu_dan_tugas_tanpa_komponen_ditolak(): void
    {
        $pengajar = $this->buatPengajar();
        $kelas = $this->kelasBaru($pengajar);
        $url = route('pengajar.kelas.terbitkan', $kelas->id_kelas);

        $this->actingAs($pengajar)->post($url, $this->dataTugas(['tenggat' => now()->subDay()->format('Y-m-d\TH:i')]))
            ->assertSessionHasErrors('tenggat');
        $this->actingAs($pengajar)->post($url, ['instruksi' => 'x', 'tenggat' => now()->addDay()->format('Y-m-d\TH:i')])
            ->assertSessionHasErrors('komponen');
        $this->assertSame(0, TugasKelas::count());
    }

    public function test_materi_yang_tidak_terbit_tidak_boleh_menjadi_komponen(): void
    {
        $pengajar = $this->buatPengajar();
        $kelas = $this->kelasBaru($pengajar);
        $draf = $this->buatMateri('Draf', 'draf');

        $this->actingAs($pengajar)->post(route('pengajar.kelas.terbitkan', $kelas->id_kelas), $this->dataTugas([
            'komponen' => [['materi' => $draf->id_materi, 'jenis' => 'materi', 'wajib' => 1]],
        ]))->assertSessionHasErrors('komponen.0.materi');
    }

    public function test_tugas_valid_diterbitkan_dengan_komponen_tersimpan(): void
    {
        $pengajar = $this->buatPengajar();
        $kelas = $this->kelasBaru($pengajar);

        $this->actingAs($pengajar)->post(route('pengajar.kelas.terbitkan', $kelas->id_kelas), $this->dataTugas())
            ->assertRedirect(route('pengajar.kelas.index'))->assertSessionHas('sukses');

        $tugas = TugasKelas::firstOrFail();
        $this->assertSame('terbit', $tugas->status);
        $this->assertCount(1, TugasKelas::ambilTugasAktif($kelas->id_kelas));

        $komponen = DB::table('komponen_tugas')->where('id_tugas', $tugas->id_tugas)->orderBy('id_komponen')->get();
        $this->assertSame(['materi', null, 1], [$komponen[0]->jenis, $komponen[0]->mode, (int) $komponen[0]->wajib]);
        $this->assertSame(['latihan', 'bunyi', 0], [$komponen[1]->jenis, $komponen[1]->mode, (int) $komponen[1]->wajib]);
    }

    public function test_tugas_tanpa_komponen_tidak_bisa_diterbitkan_di_model(): void
    {
        $kelas = $this->kelasBaru($this->buatPengajar());
        $tugas = TugasKelas::create(['id_kelas' => $kelas->id_kelas, 'instruksi' => 'x', 'tenggat' => now()->addDay(), 'status' => 'draf']);

        $this->assertSame('KOMPONEN_KOSONG', $tugas->terbitkan()->kode);
        $this->assertSame('draf', $tugas->fresh()->status);
    }
}
