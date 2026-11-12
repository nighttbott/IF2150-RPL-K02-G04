<?php

namespace Tests\Feature\Kelas;

use App\Models\KeanggotaanKelas;
use App\Models\TugasKelas;
use App\Services\PengumpulanTugas;
use Illuminate\Auth\GenericUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\SkemaKelas;

class PengumpulanTest extends SkemaKelas
{
    protected int $tugas;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('riwayat_latihan', function (Blueprint $t) {
            $t->id('id_riwayat');
            $t->integer('id_pelajar');
            $t->integer('id_tugas');
            $t->integer('id_komponen')->nullable();
            $t->integer('id_materi')->nullable();
            $t->string('mode')->nullable();
            $t->string('mode_aktivitas');
            $t->string('status');
            $t->dateTime('waktu_selesai')->nullable();
            $t->dateTime('tanggal_pengerjaan');
        });
        (new KeanggotaanKelas)->simpanKeanggotaan(1, 1);
        $this->tugas = (new TugasKelas)->simpan($this->dataTugas())->data['id_tugas'];
        TugasKelas::find($this->tugas)->terbitkan();
    }

    protected function bukti(): void
    {
        foreach (TugasKelas::find($this->tugas)->komponen as $c) {
            DB::table('riwayat_latihan')->insert(['id_pelajar' => 1, 'id_tugas' => $this->tugas, 'id_komponen' => $c->id_komponen, 'id_materi' => 1, 'mode' => $c->mode, 'mode_aktivitas' => $c->jenis, 'status' => $c->jenis === 'materi' ? 'selesai' : 'berhasil', 'waktu_selesai' => '2026-11-10 23:59:00', 'tanggal_pengerjaan' => '2026-11-10 23:59:00']);
        } $this->travelTo(now()->setDate(2026, 11, 11));
    }

    public function test_tanpa_bukti_ditolak(): void
    {
        $this->assertFalse(app(PengumpulanTugas::class)->simpanPengumpulan(1, $this->tugas, 1)->berhasil);
        $this->assertDatabaseCount('penyelesaian_tugas', 0);
    }

    public function test_tepat_tenggat_dan_pengiriman_ulang_idempoten(): void
    {
        $this->bukti();
        $s = app(PengumpulanTugas::class);
        $this->assertTrue($s->simpanPengumpulan(1, $this->tugas, 1)->berhasil);
        $this->assertTrue($s->simpanPengumpulan(1, $this->tugas, 1)->berhasil);
        $this->assertDatabaseCount('penyelesaian_tugas', 1);
        $this->assertDatabaseHas('penyelesaian_tugas', ['status' => 'tepat_waktu']);
        $this->assertSame(1, DB::table('riwayat_latihan')->where('mode_aktivitas', 'pengumpulan')->count());
    }

    public function test_terlambat_dan_ulang_latihan_tidak_menggeser_keberhasilan_pertama(): void
    {
        $this->bukti();
        DB::table('riwayat_latihan')->update(['waktu_selesai' => '2026-11-11 00:00:01']);
        $this->assertTrue(app(PengumpulanTugas::class)->simpanPengumpulan(1, $this->tugas, 1)->berhasil);
        $this->assertDatabaseHas('penyelesaian_tugas', ['status' => 'terlambat']);
    }

    public function test_bukti_salah_mode_materi_dan_pelajar_ditolak(): void
    {
        $this->bukti();
        DB::table('riwayat_latihan')->where('mode_aktivitas', 'latihan')->update(['mode' => 'bunyi']);
        $this->assertFalse(app(PengumpulanTugas::class)->simpanPengumpulan(1, $this->tugas, 1)->berhasil);
        $this->assertFalse(app(PengumpulanTugas::class)->simpanPengumpulan(1, $this->tugas, 2)->berhasil);
    }

    public function test_gagal_catatan_aktivitas_rollback_pengumpulan(): void
    {
        $this->bukti();
        DB::unprepared("CREATE TRIGGER gagal_aktivitas BEFORE INSERT ON riwayat_latihan WHEN NEW.mode_aktivitas = 'pengumpulan' BEGIN SELECT RAISE(ABORT, 'uji'); END");
        $this->assertFalse(app(PengumpulanTugas::class)->simpanPengumpulan(1, $this->tugas, 1)->berhasil);
        $this->assertDatabaseCount('penyelesaian_tugas', 0);
    }

    public function test_halaman_detail_dan_kelas_dirender_dengan_progres_server(): void
    {
        $this->actingAs(new GenericUser(['id' => 1, 'peran' => 'PELAJAR']));
        $this->get('/pelajar/kelas')->assertOk()->assertSee('Kelas Saya');
        $this->get('/pelajar/kelas/1/tugas/'.$this->tugas)->assertOk()->assertSee('Kumpulkan tugas')->assertSee('disabled', false);
        $this->bukti();
        $this->get('/pelajar/kelas/1/tugas/'.$this->tugas)->assertOk()->assertSee('Siap dikumpulkan!');
    }

    public function test_keberhasilan_pertama_tidak_digeser_latihan_ulang(): void
    {
        $this->bukti();
        $lama = DB::table('riwayat_latihan')->get();
        foreach ($lama as $b) {
            $data = (array) $b;
            unset($data['id_riwayat']);
            $data['waktu_selesai'] = '2026-11-11 00:00:01';
            DB::table('riwayat_latihan')->insert($data);
        }$hasil = app(PengumpulanTugas::class)->simpanPengumpulan(1, $this->tugas, 1);
        $this->assertTrue($hasil->berhasil);
        $this->assertDatabaseHas('penyelesaian_tugas', ['status' => 'tepat_waktu', 'waktu_kumpul' => '2026-11-10 23:59:00']);
    }

    public function test_payload_browser_tidak_menggantikan_bukti(): void
    {
        $this->actingAs(new GenericUser(['id' => 1, 'peran' => 'PELAJAR']))->post('/pelajar/kelas/1/tugas/'.$this->tugas.'/kumpulkan', ['lengkap' => true, 'waktu_selesai' => '2026-11-09 00:00:00', 'id_pelajar' => 2])->assertSessionHasErrors('pengumpulan');
        $this->assertDatabaseCount('penyelesaian_tugas',0);
    }
}
