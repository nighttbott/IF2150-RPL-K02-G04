<?php

namespace Tests\Feature\Kelas;

use App\Models\KeanggotaanKelas;
use Illuminate\Support\Facades\DB;
use Tests\Support\SkemaKelas;

class KeanggotaanKelasTest extends SkemaKelas
{
    public function test_anggota_baru_dan_gabung_dua_kali_tidak_ganda(): void
    {
        $model = new KeanggotaanKelas;
        $pertama = $model->simpanKeanggotaan(1, 1);
        $ulang = $model->simpanKeanggotaan(1, 1);
        $this->assertTrue($pertama->berhasil);
        $this->assertSame('berhasilBergabung', $pertama->kode);
        $this->assertSame('sudahAnggota', $ulang->kode);
        $this->assertSame($pertama->data['id_keanggotaan'], $ulang->data['id_keanggotaan']);
        $this->assertDatabaseCount('keanggotaan_kelas', 1);
    }

    public function test_anggota_keluar_diaktifkan_tanpa_baris_baru(): void
    {
        DB::table('keanggotaan_kelas')->insert(['id_kelas' => 1, 'id_pelajar' => 1, 'status' => 'keluar']);
        $id = DB::table('keanggotaan_kelas')->value('id_keanggotaan');
        $hasil = (new KeanggotaanKelas)->simpanKeanggotaan(1, 1);
        $this->assertTrue($hasil->berhasil);
        $this->assertSame('anggotaDiaktifkan', $hasil->kode);
        $this->assertSame($id, $hasil->data['id_keanggotaan']);
        $this->assertDatabaseCount('keanggotaan_kelas', 1);
        $this->assertDatabaseHas('keanggotaan_kelas', ['id_keanggotaan' => $id, 'status' => 'aktif']);
    }

    public function test_kelas_tidak_aktif_dan_tidak_ada_ditolak(): void
    {
        foreach ([2, 999] as $idKelas) {
            $this->assertFalse((new KeanggotaanKelas)->simpanKeanggotaan(1, $idKelas)->berhasil);
        }
        $this->assertDatabaseCount('keanggotaan_kelas', 0);
    }

    public function test_pelajar_hilang_atau_bukan_pelajar_ditolak(): void
    {
        foreach ([999, 3, 0] as $idPelajar) {
            $this->assertFalse((new KeanggotaanKelas)->simpanKeanggotaan($idPelajar, 1)->berhasil);
        }
        $this->assertDatabaseCount('keanggotaan_kelas', 0);
    }

    public function test_hanya_anggota_aktif_yang_memiliki_akses(): void
    {
        $model = new KeanggotaanKelas;
        $this->assertFalse($model->periksaAkses(1, 1));
        $model->simpanKeanggotaan(1, 1);
        $this->assertTrue($model->periksaAkses(1, 1));
        $this->assertFalse($model->periksaAkses(2, 1));
        $anggota = KeanggotaanKelas::firstOrFail();
        $this->assertTrue($anggota->ubahStatus('keluar')->berhasil);
        $this->assertFalse($model->periksaAkses(1, 1));
    }

    public function test_daftar_anggota_terbatas_kelas_dan_status(): void
    {
        DB::table('keanggotaan_kelas')->insert([
            ['id_kelas' => 1, 'id_pelajar' => 1, 'status' => 'aktif'],
            ['id_kelas' => 1, 'id_pelajar' => 2, 'status' => 'keluar'],
            ['id_kelas' => 2, 'id_pelajar' => 1, 'status' => 'aktif'],
        ]);
        $this->assertSame([1], KeanggotaanKelas::daftarAnggotaAktif(1)->pluck('id_pelajar')->all());
        $this->assertCount(2, KeanggotaanKelas::daftarAnggota(1));
    }

    public function test_ubah_status_tidak_valid_atau_objek_belum_tersimpan_ditolak(): void
    {
        $this->assertFalse((new KeanggotaanKelas)->ubahStatus('aktif')->berhasil);
        (new KeanggotaanKelas)->simpanKeanggotaan(1, 1);
        $anggota = KeanggotaanKelas::firstOrFail();
        $this->assertFalse($anggota->ubahStatus('apa_saja')->berhasil);
        $this->assertSame('aktif', $anggota->fresh()->status);
    }
}
