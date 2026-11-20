<?php

namespace Tests\Unit\Hiupl;

use App\Models\TugasKelas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\TestCase;

/**
 * HIUPL 4.2: pengujian unit kelas C25 TugasKelas (tingkat Mendalam: validasi tugas, komponen, dan penerbitan).
 */
class C25TugasKelasTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    private int $idKelas;

    private int $idMateri;

    protected function setUp(): void
    {
        parent::setUp();
        $this->idKelas = $this->buatKelas($this->buatPengajar());
        $this->idMateri = $this->buatMateri()->id_materi;
    }

    private function data(array $ganti = []): array
    {
        return $ganti + ['id_kelas' => $this->idKelas, 'instruksi' => 'Kerjakan latihan menulis ꦲ', 'tenggat' => now()->addDays(3)->toDateTimeString(),
            'komponen_tugas' => [['jenis' => 'latihan', 'id_materi' => $this->idMateri, 'mode' => 'menulis', 'wajib' => true]]];
    }

    private function draf(array $ganti = []): TugasKelas
    {
        $t = new TugasKelas;
        $h = $t->simpan($this->data($ganti));

        return TugasKelas::find($h->data['id_tugas']);
    }

    /** @uji C25-UT01 | Normal | simpan(data: DataTugas): HasilOperasi | Instruksi, tenggat 3 hari lagi, satu komponen latihan wajib | drafDisimpan; tugas berstatus draf dengan 1 komponen */
    #[TestDox('C25-UT01 Normal: draf tugas tersimpan')]
    public function test_ut01_simpan_draf(): void
    {
        $t = $this->draf();

        $this->assertSame('draf', $t->status);
        $this->assertCount(1, $t->komponen);
    }

    /** @uji C25-UT02 | Batas | simpan(data: DataTugas): HasilOperasi | Komponen materi (mode null) + latihan tidak wajib, satu komponen wajib; tenggat 1 menit lagi | drafDisimpan dengan 2 komponen */
    #[TestDox('C25-UT02 Batas: kombinasi komponen dan tenggat dekat diterima')]
    public function test_ut02_simpan_batas(): void
    {
        $h = (new TugasKelas)->simpan($this->data(['tenggat' => now()->addMinute()->toDateTimeString(), 'komponen_tugas' => [
            ['jenis' => 'materi', 'id_materi' => $this->idMateri, 'mode' => null, 'wajib' => true],
            ['jenis' => 'latihan', 'id_materi' => $this->idMateri, 'mode' => 'bunyi', 'wajib' => false],
        ]]));

        $this->assertSame('drafDisimpan', $h->kode);
        $this->assertSame(2, DB::table('komponen_tugas')->count());
    }

    /** @uji C25-UT03 | Negatif | simpan(data: DataTugas): HasilOperasi | (a) tenggat sudah lewat; (b) tanpa komponen wajib; (c) mode latihan tidak dikenal; (d) materi masih draf; (e) kelas diarsipkan | dataTidakValid untuk (a)–(d), kelasTidakAktif untuk (e); tidak ada tugas tersimpan */
    #[TestDox('C25-UT03 Negatif: data tugas tidak valid ditolak')]
    public function test_ut03_simpan_ditolak(): void
    {
        $komp = fn (array $g) => ['komponen_tugas' => [$g + ['jenis' => 'latihan', 'id_materi' => $this->idMateri, 'mode' => 'menulis', 'wajib' => true]]];
        $t = new TugasKelas;
        $this->assertSame('dataTidakValid', $t->simpan($this->data(['tenggat' => now()->subMinute()->toDateTimeString()]))->kode);
        $this->assertSame('dataTidakValid', $t->simpan($this->data($komp(['wajib' => false])))->kode);
        $this->assertSame('dataTidakValid', $t->simpan($this->data($komp(['mode' => 'menggambar'])))->kode);
        $this->assertSame('dataTidakValid', $t->simpan($this->data($komp(['id_materi' => $this->buatMateri('Draf', 'draf')->id_materi])))->kode);
        DB::table('kelas_belajar')->where('id_kelas', $this->idKelas)->update(['status' => 'arsip']);
        $this->assertSame('kelasTidakAktif', $t->simpan($this->data())->kode);
        $this->assertSame(0, TugasKelas::count());
    }

    /** @uji C25-UT04 | Normal | terbitkan(): HasilOperasi | Draf valid diterbitkan | tugasDiterbitkan; status terbit; muncul di ambilTugasAktif */
    #[TestDox('C25-UT04 Normal: draf valid diterbitkan')]
    public function test_ut04_terbitkan(): void
    {
        $t = $this->draf();

        $this->assertSame('tugasDiterbitkan', $t->terbitkan()->kode);
        $this->assertSame([$t->id_tugas], TugasKelas::ambilTugasAktif($this->idKelas)->pluck('id_tugas')->all());
    }

    /** @uji C25-UT05 | Batas | terbitkan(): HasilOperasi | Tugas yang sudah terbit diterbitkan lagi | sudahTerbit (idempoten, tidak galat) */
    #[TestDox('C25-UT05 Batas: penerbitan ulang idempoten')]
    public function test_ut05_terbit_ulang(): void
    {
        $t = $this->draf();
        $t->terbitkan();

        $this->assertSame('sudahTerbit', $t->terbitkan()->kode);
    }

    /** @uji C25-UT06 | Negatif | terbitkan(): HasilOperasi | (a) draf tanpa komponen; (b) draf yang tenggatnya sudah lewat; (c) objek tugas belum tersimpan | KOMPONEN_KOSONG, dataTidakValid, tugasTidakDitemukan; status tetap draf */
    #[TestDox('C25-UT06 Negatif: draf tidak memenuhi syarat tidak diterbitkan')]
    public function test_ut06_terbit_ditolak(): void
    {
        $a = $this->draf();
        DB::table('komponen_tugas')->where('id_tugas', $a->id_tugas)->delete();
        $b = $this->draf();
        DB::table('tugas_kelas')->where('id_tugas', $b->id_tugas)->update(['tenggat' => now()->subDay()]);

        $this->assertSame('KOMPONEN_KOSONG', $a->terbitkan()->kode);
        $this->assertSame('dataTidakValid', $b->fresh()->terbitkan()->kode);
        $this->assertSame('tugasTidakDitemukan', (new TugasKelas)->terbitkan()->kode);
        $this->assertSame(0, TugasKelas::where('status', 'terbit')->count());
    }

    /** @uji C25-UT07 | Normal | ambilTugasAktif(idKelas: Integer): List | Dua tugas terbit dengan tenggat berbeda | Urut tenggat terdekat */
    #[TestDox('C25-UT07 Normal: tugas aktif urut tenggat')]
    public function test_ut07_tugas_aktif_urut(): void
    {
        $jauh = $this->draf(['tenggat' => now()->addDays(5)->toDateTimeString()]);
        $dekat = $this->draf(['tenggat' => now()->addDay()->toDateTimeString()]);
        $jauh->terbitkan();
        $dekat->terbitkan();

        $this->assertSame([$dekat->id_tugas, $jauh->id_tugas], TugasKelas::ambilTugasAktif($this->idKelas)->pluck('id_tugas')->all());
    }

    /** @uji C25-UT08 | Batas | ambilTugasAktif(idKelas: Integer): List | Kelas hanya punya draf | Daftar kosong (draf tidak terlihat Pelajar) */
    #[TestDox('C25-UT08 Batas: draf tidak termasuk tugas aktif')]
    public function test_ut08_draf_tidak_aktif(): void
    {
        $this->draf();

        $this->assertCount(0, TugasKelas::ambilTugasAktif($this->idKelas));
    }

    /** @uji C25-UT09 | Negatif | ambilDetail(idKelas: Integer, idTugas: Integer): TugasKelas? | ID tugas dari kelas lain | null */
    #[TestDox('C25-UT09 Negatif: detail tugas kelas lain tidak dapat diambil')]
    public function test_ut09_detail_kelas_lain(): void
    {
        $t = $this->draf();
        $t->terbitkan();
        $lain = $this->buatKelas($this->buatPengajar('Pak Lain'));

        $this->assertNotNull(TugasKelas::ambilDetail($this->idKelas, $t->id_tugas));
        $this->assertNull(TugasKelas::ambilDetail($lain, $t->id_tugas));
    }

    /** @uji C25-UT10 | Normal | ambilDetail(idKelas: Integer, idTugas: Integer): TugasKelas? | Tugas terbit pada kelas yang benar | Tugas beserta komponennya */
    #[TestDox('C25-UT10 Normal: detail tugas terbit beserta komponen')]
    public function test_ut10_detail(): void
    {
        $t = $this->draf();
        $t->terbitkan();

        $this->assertCount(1, TugasKelas::ambilDetail($this->idKelas, $t->id_tugas)->komponen);
    }

    /** @uji C25-UT11 | Batas | ambilDetail(idKelas: Integer, idTugas: Integer): TugasKelas? | Tugas masih draf | null (draf belum boleh dibuka Pelajar) */
    #[TestDox('C25-UT11 Batas: draf tidak dapat dibuka')]
    public function test_ut11_detail_draf(): void
    {
        $this->assertNull(TugasKelas::ambilDetail($this->idKelas, $this->draf()->id_tugas));
    }

    /** @uji C25-UT12 | Negatif | ambilTugasAktif(idKelas: Integer): List | Tugas terbit milik kelas lain | Tidak ikut dalam daftar kelas ini */
    #[TestDox('C25-UT12 Negatif: tugas kelas lain tidak ikut')]
    public function test_ut12_tugas_kelas_lain(): void
    {
        $lain = $this->buatKelas($this->buatPengajar('Pak Lain'));
        $t = $this->draf(['id_kelas' => $lain]);
        $t->terbitkan();

        $this->assertCount(0, TugasKelas::ambilTugasAktif($this->idKelas));
    }
}
