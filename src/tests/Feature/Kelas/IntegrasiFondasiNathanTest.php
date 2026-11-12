<?php

namespace Tests\Feature\Kelas;

use App\Models\AkunPengguna;
use App\Models\RiwayatLatihan;
use App\Models\TugasKelas;
use App\Services\PengumpulanTugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Menguji migrasi/seeder Nathan asli, tanpa SkemaKelas sementara. */
class IntegrasiFondasiNathanTest extends TestCase
{
    use RefreshDatabase;

    private function dataTugas(): array
    {
        $this->seed();
        $pelajar = AkunPengguna::where('email', 'pelajar@ngaksara.test')->firstOrFail();
        $tugas = TugasKelas::with('komponen')->firstOrFail();

        return [$pelajar, $tugas];
    }

    private function selesaikan(AkunPengguna $pelajar, TugasKelas $tugas): void
    {
        foreach ($tugas->komponen as $c) {
            $data = ['id_pelajar' => $pelajar->id_akun, 'id_tugas' => $tugas->id_tugas,
                'id_komponen' => $c->id_komponen, 'id_materi' => $c->id_materi];
            $hasil = $c->jenis === 'materi'
                ? RiwayatLatihan::catatAktivitas($data + ['mode_aktivitas' => 'materi'])
                : RiwayatLatihan::simpanHasil($data + ['target_soal' => 'ha', 'mode' => $c->mode, 'skor' => 90, 'status' => 'berhasil']);
            $this->assertTrue($hasil->berhasil);
        }
    }

    public function test_akun_seeder_asli_dapat_membuka_kelas_dan_materi(): void
    {
        [$p, $t] = $this->dataTugas();
        $this->actingAs($p)->get('/pelajar/kelas')->assertOk()->assertSee('Aksara Jawa Dasar');
        $this->get(route('materi.index'))->assertOk()->assertSee(route('kelas-pelajar.index'), false);
        $this->get('/pelajar/kelas/'.$t->id_kelas.'/tugas/'.$t->id_tugas)->assertOk();
        $c = $t->komponen->firstWhere('jenis', 'materi');
        $url = '/pelajar/kelas/'.$t->id_kelas.'/tugas/'.$t->id_tugas.'/komponen/'.$c->id_komponen;
        $jumlah = RiwayatLatihan::count();
        $this->get($url)->assertOk()->assertSee('Selesai membaca')->assertSee($url.'/selesai', false);
        $this->assertSame($jumlah, RiwayatLatihan::count());
    }

    public function test_konfirmasi_materi_memakai_identitas_dan_waktu_server_serta_idempoten(): void
    {
        [$p, $t] = $this->dataTugas();
        $c = $t->komponen->firstWhere('jenis', 'materi');
        RiwayatLatihan::where('id_pelajar', $p->id_akun)->where('id_tugas', $t->id_tugas)->delete();
        $this->travelTo($t->tenggat->subMinute());
        try {
            $url = '/pelajar/kelas/'.$t->id_kelas.'/tugas/'.$t->id_tugas.'/komponen/'.$c->id_komponen.'/selesai';
            $this->actingAs($p)->post($url, ['id_pelajar' => 999, 'waktu_selesai' => '2000-01-01', 'id_materi' => 999])
                ->assertRedirect(route('kelas-pelajar.detail', [$t->id_kelas, $t->id_tugas]))->assertSessionHas('sukses');
            $this->post($url)->assertSessionHas('sukses');
            $bukti = RiwayatLatihan::where('id_pelajar', $p->id_akun)->where('id_komponen', $c->id_komponen)->get();
            $this->assertCount(1, $bukti);
            $this->assertSame($c->id_materi, (int) $bukti->first()->id_materi);
            $this->assertSame('selesai', $bukti->first()->status);
            $this->assertSame($t->tenggat->subMinute()->format('Y-m-d H:i:s'), $bukti->first()->waktu_selesai->format('Y-m-d H:i:s'));
            $this->assertFalse(app(PengumpulanTugas::class)->progres($t, $p->id_akun)['lengkap']);
        } finally {
            $this->travelBack();
        }
    }

    public function test_konfirmasi_materi_menolak_tamu_pengajar_dan_anggota_keluar(): void
    {
        [$p, $t] = $this->dataTugas();
        $c = $t->komponen->firstWhere('jenis', 'materi');
        $url = '/pelajar/kelas/'.$t->id_kelas.'/tugas/'.$t->id_tugas.'/komponen/'.$c->id_komponen.'/selesai';
        $jumlah = RiwayatLatihan::count();
        $this->post($url)->assertUnauthorized();
        $guru = AkunPengguna::where('peran', 'pengajar')->firstOrFail();
        $this->actingAs($guru)->post($url)->assertForbidden();
        DB::table('keanggotaan_kelas')->where('id_pelajar', $p->id_akun)->update(['status' => 'keluar']);
        $this->actingAs($p)->post($url)->assertNotFound();
        $this->assertSame($jumlah, RiwayatLatihan::count());
    }

    public function test_konfirmasi_materi_tidak_dapat_menyelesaikan_latihan_atau_komponen_asing(): void
    {
        [$p, $t] = $this->dataTugas();
        $c = $t->komponen->firstWhere('jenis', 'latihan');
        $jumlah = RiwayatLatihan::count();
        $this->actingAs($p)->post('/pelajar/kelas/'.$t->id_kelas.'/tugas/'.$t->id_tugas.'/komponen/'.$c->id_komponen.'/selesai')->assertNotFound();
        $this->post('/pelajar/kelas/'.$t->id_kelas.'/tugas/'.$t->id_tugas.'/komponen/99999/selesai')->assertNotFound();
        $this->assertSame($jumlah, RiwayatLatihan::count());
    }

    public function test_materi_arsip_tidak_dapat_dikonfirmasi_selesai(): void
    {
        [$p, $t] = $this->dataTugas();
        $c = $t->komponen->firstWhere('jenis', 'materi');
        DB::table('materi_aksara')->where('id_materi', $c->id_materi)->update(['status_publikasi' => 'arsip']);
        $jumlah = RiwayatLatihan::count();
        $this->actingAs($p)->post('/pelajar/kelas/'.$t->id_kelas.'/tugas/'.$t->id_tugas.'/komponen/'.$c->id_komponen.'/selesai')->assertSessionHasErrors('materi');
        $this->assertSame($jumlah, RiwayatLatihan::count());
    }

    public function test_pencatatan_materi_dibatalkan_tidak_melaporkan_sukses(): void
    {
        [$p, $t] = $this->dataTugas();
        $c = $t->komponen->firstWhere('jenis', 'materi');
        RiwayatLatihan::where('id_pelajar', $p->id_akun)->where('id_tugas', $t->id_tugas)->delete();
        RiwayatLatihan::creating(fn () => false);
        try {
            $this->actingAs($p)->post('/pelajar/kelas/'.$t->id_kelas.'/tugas/'.$t->id_tugas.'/komponen/'.$c->id_komponen.'/selesai')->assertSessionHasErrors('materi');
            $this->assertDatabaseMissing('riwayat_latihan', ['id_pelajar' => $p->id_akun, 'id_komponen' => $c->id_komponen]);
        } finally {
            RiwayatLatihan::flushEventListeners();
        }
    }

    public function test_materi_tanpa_konten_tidak_menawarkan_atau_mencatat_penyelesaian(): void
    {
        [$p, $t] = $this->dataTugas();
        $c = $t->komponen->firstWhere('jenis', 'materi');
        DB::table('konten_materi')->where('id_materi', $c->id_materi)->delete();
        $url = '/pelajar/kelas/'.$t->id_kelas.'/tugas/'.$t->id_tugas.'/komponen/'.$c->id_komponen;
        $jumlah = RiwayatLatihan::count();
        $this->actingAs($p)->get($url)->assertOk()->assertDontSee('Selesai membaca');
        $this->post($url.'/selesai')->assertSessionHasErrors('materi');
        $this->assertSame($jumlah, RiwayatLatihan::count());
    }

    public function test_kelas_dinonaktifkan_setelah_materi_dibuka_menolak_konfirmasi(): void
    {
        [$p, $t] = $this->dataTugas();
        $c = $t->komponen->firstWhere('jenis', 'materi');
        $url = '/pelajar/kelas/'.$t->id_kelas.'/tugas/'.$t->id_tugas.'/komponen/'.$c->id_komponen;
        $this->actingAs($p)->get($url)->assertOk();
        DB::table('kelas_belajar')->where('id_kelas', $t->id_kelas)->update(['status' => 'nonaktif']);
        $jumlah = RiwayatLatihan::count();
        $this->post($url.'/selesai')->assertNotFound();
        $this->assertSame($jumlah, RiwayatLatihan::count());
    }

    public function test_bukti_dari_api_nathan_dan_pengumpulan_ulang_memakai_keberhasilan_pertama(): void
    {
        [$p, $t] = $this->dataTugas();
        try {
            $this->travelTo($t->tenggat);
            $this->selesaikan($p, $t);
            $this->travelTo($t->tenggat->addHour());
            $this->selesaikan($p, $t);
            $s = app(PengumpulanTugas::class);
            $this->assertTrue($s->simpanPengumpulan($t->id_kelas, $t->id_tugas, $p->id_akun)->berhasil);
            $this->assertTrue($s->simpanPengumpulan($t->id_kelas, $t->id_tugas, $p->id_akun)->berhasil);
            $this->assertDatabaseHas('penyelesaian_tugas', ['id_tugas' => $t->id_tugas, 'id_pelajar' => $p->id_akun, 'status' => 'tepat_waktu']);
            $this->assertSame(1, RiwayatLatihan::where('mode_aktivitas', 'pengumpulan')->count());
        } finally {
            $this->travelBack();
        }
    }

    public function test_gagal_pencatatan_model_riwayat_membatalkan_pengumpulan(): void
    {
        [$p, $t] = $this->dataTugas();
        $this->selesaikan($p, $t);
        RiwayatLatihan::creating(fn ($r) => $r->mode_aktivitas === 'pengumpulan' ? false : null);
        try {
            $hasil = app(PengumpulanTugas::class)->simpanPengumpulan($t->id_kelas, $t->id_tugas, $p->id_akun);
            $this->assertFalse($hasil->berhasil);
            $this->assertDatabaseMissing('penyelesaian_tugas', ['id_tugas' => $t->id_tugas, 'id_pelajar' => $p->id_akun]);
        } finally {
            RiwayatLatihan::flushEventListeners();
        }
    }
}
