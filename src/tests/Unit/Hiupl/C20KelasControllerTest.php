<?php

namespace Tests\Unit\Hiupl;

use App\Http\Controllers\PemantauanKelasController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\TestCase;

/**
 * HIUPL 4.2: pengujian kelas C20 KelasController (tingkat Sedang: alur CRUD kelas; normal + negatif per operasi, lewat HTTP).
 * Pada implementasi, C20 dipecah menjadi KelasPengajarController, KelasPelajarController, dan PemantauanKelasController.
 */
class C20KelasControllerTest extends TestCase
{
    use BantuanData, BantuanMateri, RefreshDatabase;

    private function kelasDenganKode(): array
    {
        $g = $this->buatPengajar();
        $id = $this->buatKelas($g);

        return [$g, $id, DB::table('kelas_belajar')->where('id_kelas', $id)->value('kode_bergabung')];
    }

    /** @uji C20-UT01 | Normal | buatKelas(data: DataKelas): HasilOperasi | Pengajar POST /pengajar/kelas nama "Aksara Jawa Dasar" | Redirect ke daftar kelas dengan pesan sukses; kelas tersimpan */
    #[TestDox('C20-UT01 Normal: Pengajar membuat kelas')]
    public function test_ut01_buat_kelas(): void
    {
        $this->actingAs($this->buatPengajar())->post(route('pengajar.kelas.buat'), ['nama' => 'Aksara Jawa Dasar'])
            ->assertRedirect(route('pengajar.kelas.index'))->assertSessionHas('sukses');
        $this->assertDatabaseHas('kelas_belajar', ['nama' => 'Aksara Jawa Dasar']);
    }

    /** @uji C20-UT02 | Negatif | buatKelas(data: DataKelas): HasilOperasi | Nama kosong; Pelajar membuat kelas | Galat isian nama; 403 */
    #[TestDox('C20-UT02 Negatif: nama kosong dan Pelajar ditolak')]
    public function test_ut02_buat_kelas_ditolak(): void
    {
        $this->actingAs($this->buatPengajar())->post(route('pengajar.kelas.buat'), ['nama' => ''])->assertSessionHasErrors('nama');
        $this->actingAs($this->buatPelajar())->post(route('pengajar.kelas.buat'), ['nama' => 'X'])->assertForbidden();
    }

    /** @uji C20-UT03 | Normal | validasiKodeBergabung(kode) + gabungkanPelajar(idKelas): HasilOperasi | Pelajar POST /pelajar/kelas/gabung dengan kode huruf kecil yang benar | Redirect ke halaman kelas; keanggotaan aktif */
    #[TestDox('C20-UT03 Normal: Pelajar bergabung dengan kode')]
    public function test_ut03_gabung(): void
    {
        [, $id, $kode] = $this->kelasDenganKode();
        $p = $this->buatPelajar();

        $this->actingAs($p)->post(route('kelas-pelajar.gabung'), ['kode' => strtolower($kode)])->assertRedirect(route('kelas-pelajar.kelas', $id));
        $this->assertDatabaseHas('keanggotaan_kelas', ['id_kelas' => $id, 'id_pelajar' => $p->id_akun, 'status' => 'aktif']);
    }

    /** @uji C20-UT04 | Negatif | validasiKodeBergabung(kode: String): HasilOperasi | Kode "ZZZZZZ" (tidak terdaftar) dan "AB-12" (format salah) | Galat isian kode; tidak ada keanggotaan (UC05 Alternatif 3) */
    #[TestDox('C20-UT04 Negatif: kode tidak valid ditolak')]
    public function test_ut04_kode_salah(): void
    {
        $this->kelasDenganKode();
        $p = $this->buatPelajar();

        $this->actingAs($p)->post(route('kelas-pelajar.gabung'), ['kode' => 'ZZZZZZ'])->assertSessionHasErrors('kode');
        $this->actingAs($p)->post(route('kelas-pelajar.gabung'), ['kode' => 'AB-12'])->assertSessionHasErrors('kode');
        $this->assertSame(0, DB::table('keanggotaan_kelas')->count());
    }

    /** @uji C20-UT05 | Normal | gantiKodeKelas(idKelas: Integer): HasilOperasi | Pemilik POST ganti-kode | Kode berubah; pesan sukses berisi kode baru */
    #[TestDox('C20-UT05 Normal: pemilik mengganti kode')]
    public function test_ut05_ganti_kode(): void
    {
        [$g, $id, $lama] = $this->kelasDenganKode();

        $this->actingAs($g)->post(route('pengajar.kelas.ganti-kode', $id))->assertSessionHas('sukses');
        $this->assertNotSame($lama, DB::table('kelas_belajar')->where('id_kelas', $id)->value('kode_bergabung'));
    }

    /** @uji C20-UT06 | Negatif | gantiKodeKelas(idKelas: Integer): HasilOperasi | Pengajar lain mengganti kode | 403; kode tetap */
    #[TestDox('C20-UT06 Negatif: bukan pemilik tidak dapat mengganti kode')]
    public function test_ut06_ganti_kode_ditolak(): void
    {
        [, $id, $lama] = $this->kelasDenganKode();

        $this->actingAs($this->buatPengajar('Pak Lain'))->post(route('pengajar.kelas.ganti-kode', $id))->assertForbidden();
        $this->assertSame($lama, DB::table('kelas_belajar')->where('id_kelas', $id)->value('kode_bergabung'));
    }

    /** @uji C20-UT07 | Normal | kelolaAnggota(idKelas, idPelajar, status): HasilOperasi | Pemilik mengeluarkan anggota aktif | Status keanggotaan nonaktif; anggota kehilangan akses halaman kelas (404, kelas disembunyikan) */
    #[TestDox('C20-UT07 Normal: anggota dikeluarkan')]
    public function test_ut07_keluarkan(): void
    {
        [$g, $id] = $this->kelasDenganKode();
        $p = $this->buatPelajar();
        $this->gabung($id, $p);

        $this->actingAs($g)->post(route('pengajar.kelas.keluarkan', [$id, $p->id_akun]))->assertSessionHas('sukses');
        $this->assertDatabaseHas('keanggotaan_kelas', ['id_pelajar' => $p->id_akun, 'status' => 'nonaktif']);
        $this->actingAs($p)->get(route('kelas-pelajar.kelas', $id))->assertNotFound();
    }

    /** @uji C20-UT08 | Negatif | kelolaAnggota(idKelas, idPelajar, status): HasilOperasi | Mengeluarkan Pelajar yang bukan anggota | Galat "bukan anggota kelas ini" */
    #[TestDox('C20-UT08 Negatif: mengeluarkan bukan anggota ditolak')]
    public function test_ut08_keluarkan_bukan_anggota(): void
    {
        [$g, $id] = $this->kelasDenganKode();

        $this->actingAs($g)->post(route('pengajar.kelas.keluarkan', [$id, $this->buatPelajar()->id_akun]))->assertSessionHasErrors('anggota');
    }

    /** @uji C20-UT09 | Normal | aturScoreboard(idKelas: Integer, aktif: Boolean): HasilOperasi | Pemilik mengaktifkan scoreboard | status_scoreboard true */
    #[TestDox('C20-UT09 Normal: scoreboard diaktifkan')]
    public function test_ut09_scoreboard(): void
    {
        [$g, $id] = $this->kelasDenganKode();
        DB::table('kelas_belajar')->where('id_kelas', $id)->update(['status_scoreboard' => false]);

        $this->actingAs($g)->post(route('pengajar.kelas.scoreboard', $id), ['aktif' => '1']);
        $this->assertTrue((bool) DB::table('kelas_belajar')->where('id_kelas', $id)->value('status_scoreboard'));
    }

    /** @uji C20-UT10 | Negatif | aturScoreboard(idKelas: Integer, aktif: Boolean): HasilOperasi | Nilai aktif "mungkin" (bukan boolean) | Galat validasi isian aktif */
    #[TestDox('C20-UT10 Negatif: nilai scoreboard bukan boolean ditolak')]
    public function test_ut10_scoreboard_tidak_valid(): void
    {
        [$g, $id] = $this->kelasDenganKode();

        $this->actingAs($g)->post(route('pengajar.kelas.scoreboard', $id), ['aktif' => 'mungkin'])->assertSessionHasErrors('aktif');
    }

    /** @uji C20-UT11 | Normal | buatTugas + terbitkanTugas(idKelas, data: DataTugas): HasilOperasi | Pemilik mengirim instruksi, tenggat besok, satu komponen latihan menulis wajib | Tugas berstatus terbit */
    #[TestDox('C20-UT11 Normal: tugas diterbitkan')]
    public function test_ut11_terbitkan_tugas(): void
    {
        [$g, $id] = $this->kelasDenganKode();
        $m = $this->buatMateri();

        $this->actingAs($g)->post(route('pengajar.kelas.terbitkan', $id), ['instruksi' => 'Latihan ꦲ', 'tenggat' => now()->addDay()->format('Y-m-d H:i'),
            'komponen' => [['materi' => $m->id_materi, 'jenis' => 'menulis', 'wajib' => '1']]])->assertSessionHas('sukses');
        $this->assertDatabaseHas('tugas_kelas', ['id_kelas' => $id, 'status' => 'terbit']);
    }

    /** @uji C20-UT12 | Negatif | buatTugas + terbitkanTugas(idKelas, data: DataTugas): HasilOperasi | Tenggat kemarin dan tanpa komponen | Galat tenggat dan komponen; tidak ada tugas */
    #[TestDox('C20-UT12 Negatif: tugas tidak valid tidak diterbitkan')]
    public function test_ut12_terbitkan_ditolak(): void
    {
        [$g, $id] = $this->kelasDenganKode();

        $this->actingAs($g)->post(route('pengajar.kelas.terbitkan', $id), ['instruksi' => 'X', 'tenggat' => now()->subDay()->format('Y-m-d H:i'), 'komponen' => []])
            ->assertSessionHasErrors(['tenggat', 'komponen']);
        $this->assertSame(0, DB::table('tugas_kelas')->count());
    }

    /** @uji C20-UT13 | Normal | ambilProgresAnggota(idKelas, idPelajar): Data | Pemilik meminta progres anggota aktif dengan satu riwayat | Berhasil; ringkasan jumlahLatihan 1 */
    #[TestDox('C20-UT13 Normal: progres anggota aktif')]
    public function test_ut13_progres_anggota(): void
    {
        [$g, $id] = $this->kelasDenganKode();
        $p = $this->buatPelajar();
        $this->gabung($id, $p);
        $this->riwayat($p, $this->buatMateri(), 80);
        $this->actingAs($g);
        $h = app(PemantauanKelasController::class)->ambilProgresAnggota($id, $p->id_akun);

        $this->assertSame(1, $h->data['ringkasan']['jumlahLatihan']);
    }

    /** @uji C20-UT14 | Negatif | ambilProgresAnggota(idKelas, idPelajar): Data | Pelajar bukan anggota aktif | BUKAN_ANGGOTA_AKTIF (UC08 Alternatif 2) */
    #[TestDox('C20-UT14 Negatif: progres bukan anggota tidak ditampilkan')]
    public function test_ut14_progres_bukan_anggota(): void
    {
        [$g, $id] = $this->kelasDenganKode();
        $this->actingAs($g);

        $this->assertSame('BUKAN_ANGGOTA_AKTIF', app(PemantauanKelasController::class)->ambilProgresAnggota($id, $this->buatPelajar()->id_akun)->kode);
    }

    /** @uji C20-UT15 | Normal | kirimUmpanBalik(idKelas, data: DataUmpanBalik): HasilOperasi | Pemilik mengirim pesan ke anggota aktif | Pesan tersimpan di data_feedback untuk anggota tersebut */
    #[TestDox('C20-UT15 Normal: umpan balik terkirim ke anggota')]
    public function test_ut15_umpan_balik(): void
    {
        [$g, $id] = $this->kelasDenganKode();
        $p = $this->buatPelajar();
        $this->gabung($id, $p);

        $this->actingAs($g)->post(route('pemantauan.umpan-balik', $id), ['id_pelajar' => $p->id_akun, 'pesan' => 'Latih lagi ꦤ ya.'])->assertSessionHas('status');
        $this->assertSame(1, DB::table('data_feedback')->where('id_akun', $p->id_akun)->count());
    }

    /** @uji C20-UT16 | Negatif | kirimUmpanBalik(idKelas, data: DataUmpanBalik): HasilOperasi | Pesan kosong; penerima bukan anggota | Galat isian pesan; tidak ada data tersimpan */
    #[TestDox('C20-UT16 Negatif: umpan balik kosong atau bukan anggota ditolak')]
    public function test_ut16_umpan_balik_ditolak(): void
    {
        [$g, $id] = $this->kelasDenganKode();
        $p = $this->buatPelajar();

        $this->actingAs($g)->post(route('pemantauan.umpan-balik', $id), ['id_pelajar' => $p->id_akun, 'pesan' => ''])->assertSessionHasErrors('pesan');
        $this->actingAs($g)->post(route('pemantauan.umpan-balik', $id), ['id_pelajar' => $p->id_akun, 'pesan' => 'Halo'])->assertSessionHasErrors('pesan');
        $this->assertSame(0, DB::table('data_feedback')->count());
    }

    /** @uji C20-UT17 | Normal | ambilKelasPengajar() + ambilDaftarAnggota(idKelas) | Pemilik membuka /pengajar/kelas dan /pengajar/kelas/{id}/anggota | 200; nama kelas dan nama anggota aktif tampil */
    #[TestDox('C20-UT17 Normal: daftar kelas dan anggota Pengajar')]
    public function test_ut17_daftar_kelas_anggota(): void
    {
        [$g, $id] = $this->kelasDenganKode();
        $this->gabung($id, $this->buatPelajar('Sari Uji'));

        $this->actingAs($g)->get(route('pengajar.kelas.index'))->assertOk()->assertSee('Kelas Uji');
        $this->actingAs($g)->get(route('pengajar.kelas.anggota', $id))->assertOk()->assertSee('Sari Uji');
    }

    /** @uji C20-UT18 | Negatif | ambilKelasPengajar() + ambilDaftarAnggota(idKelas) | Pengajar lain membuka daftar anggota; Pelajar membuka daftar kelas Pengajar | 403 untuk keduanya */
    #[TestDox('C20-UT18 Negatif: daftar anggota hanya untuk pemilik')]
    public function test_ut18_anggota_ditolak(): void
    {
        [, $id] = $this->kelasDenganKode();

        $this->actingAs($this->buatPengajar('Pak Lain'))->get(route('pengajar.kelas.anggota', $id))->assertForbidden();
        $this->actingAs($this->buatPelajar())->get(route('pengajar.kelas.index'))->assertForbidden();
    }

    /** @uji C20-UT19 | Normal | ambilTugasAktif(idKelas) + bukaTugas(idKelas, idTugas) | Anggota membuka halaman kelas dan detail tugas terbit | 200; instruksi tugas tampil */
    #[TestDox('C20-UT19 Normal: anggota melihat tugas aktif')]
    public function test_ut19_tugas_aktif(): void
    {
        [, $id] = $this->kelasDenganKode();
        $p = $this->buatPelajar();
        $this->gabung($id, $p);
        $t = DB::table('tugas_kelas')->insertGetId(['id_kelas' => $id, 'instruksi' => 'Instruksi uji C20', 'tenggat' => now()->addDay(), 'status' => 'terbit'], 'id_tugas');
        DB::table('komponen_tugas')->insert(['id_tugas' => $t, 'jenis' => 'materi', 'id_materi' => $this->buatMateri()->id_materi, 'mode' => null, 'wajib' => true]);

        $this->actingAs($p)->get(route('kelas-pelajar.kelas', $id))->assertOk()->assertSee('Instruksi uji C20');
        $this->actingAs($p)->get(route('kelas-pelajar.detail', [$id, $t]))->assertOk()->assertSee('Instruksi uji C20');
    }

    /** @uji C20-UT20 | Negatif | ambilTugasAktif(idKelas) + bukaTugas(idKelas, idTugas) | Bukan anggota membuka kelas; anggota membuka ID tugas yang tidak ada | 404 untuk keduanya */
    #[TestDox('C20-UT20 Negatif: bukan anggota dan tugas tidak ada ditolak')]
    public function test_ut20_tugas_ditolak(): void
    {
        [, $id] = $this->kelasDenganKode();
        $p = $this->buatPelajar();

        $this->actingAs($this->buatPelajar('Orang Lain'))->get(route('kelas-pelajar.kelas', $id))->assertNotFound();
        $this->gabung($id, $p);
        $this->actingAs($p)->get(route('kelas-pelajar.detail', [$id, 999999]))->assertNotFound();
    }

    /** @uji C20-UT21 | Normal | simpanPengumpulan(idKelas, idTugas) | Anggota menyelesaikan komponen materi lalu POST kumpulkan | Pesan sukses; penyelesaian_tugas tepat_waktu */
    #[TestDox('C20-UT21 Normal: pengumpulan lewat HTTP')]
    public function test_ut21_kumpul(): void
    {
        [, $id] = $this->kelasDenganKode();
        $p = $this->buatPelajar();
        $this->gabung($id, $p);
        $t = DB::table('tugas_kelas')->insertGetId(['id_kelas' => $id, 'instruksi' => 'X', 'tenggat' => now()->addDay(), 'status' => 'terbit'], 'id_tugas');
        $c = DB::table('komponen_tugas')->insertGetId(['id_tugas' => $t, 'jenis' => 'materi', 'id_materi' => $this->materiLengkap()->id_materi, 'mode' => null, 'wajib' => true], 'id_komponen');
        $this->actingAs($p)->post(route('kelas-pelajar.selesai-materi', [$id, $t, $c]));

        $this->actingAs($p)->post(route('kelas-pelajar.kumpulkan', [$id, $t]))->assertSessionHas('sukses');
        $this->assertDatabaseHas('penyelesaian_tugas', ['id_tugas' => $t, 'status' => 'tepat_waktu']);
    }

    /** @uji C20-UT22 | Negatif | simpanPengumpulan(idKelas, idTugas) | Pengajar mencoba POST kumpulkan | 403; tidak ada penyelesaian */
    #[TestDox('C20-UT22 Negatif: Pengajar tidak dapat mengumpulkan tugas')]
    public function test_ut22_kumpul_pengajar(): void
    {
        [$g, $id] = $this->kelasDenganKode();

        $this->actingAs($g)->post(route('kelas-pelajar.kumpulkan', [$id, 1]))->assertForbidden();
        $this->assertSame(0, DB::table('penyelesaian_tugas')->count());
    }
}
