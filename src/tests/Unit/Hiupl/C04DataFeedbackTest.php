<?php

namespace Tests\Unit\Hiupl;

use App\Enums\StatusFeedback;
use App\Models\DataFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\BantuanData;
use Tests\TestCase;

/**
 * HIUPL 4.2: pengujian unit kelas C04 DataFeedback (tingkat Sedang: penyimpanan sederhana; skenario normal dan negatif).
 */
class C04DataFeedbackTest extends TestCase
{
    use BantuanData, RefreshDatabase;

    private function feedback(): DataFeedback
    {
        $f = new DataFeedback;
        $f->simpan(['id_akun' => $this->buatPelajar()->id_akun, 'kategori' => 'fitur', 'pesan' => 'Tolong tambahkan aksara Sunda.']);

        return $f;
    }

    /** @uji C04-UT01 | Normal | simpan(data: MasukanFeedback): HasilOperasi | Feedback kategori fitur dari Pelajar | Berhasil; baris data_feedback berstatus baru */
    #[TestDox('C04-UT01 Normal: feedback tersimpan berstatus baru')]
    public function test_ut01_simpan(): void
    {
        $f = $this->feedback();

        $this->assertDatabaseHas('data_feedback', ['id_feedback' => $f->id_feedback, 'status' => 'baru', 'kategori' => 'fitur']);
    }

    /** @uji C04-UT02 | Negatif | simpan(data: MasukanFeedback): HasilOperasi | id_akun yang tidak ada di akun_pengguna (melanggar foreign key) | Gagal dengan SIMPAN_GAGAL; tidak ada baris tersimpan */
    #[TestDox('C04-UT02 Negatif: penyimpanan gagal mengembalikan SIMPAN_GAGAL')]
    public function test_ut02_simpan_gagal(): void
    {
        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON');
        $h = (new DataFeedback)->simpan(['id_akun' => 999999, 'kategori' => 'fitur', 'pesan' => 'Pesan uji yang cukup panjang.']);

        $this->assertSame('SIMPAN_GAGAL', $h->kode);
        $this->assertSame(0, DataFeedback::count());
    }

    /** @uji C04-UT03 | Normal | perbaruiStatus(status: StatusFeedback): HasilOperasi | Status baru dimajukan ke ditinjau lalu selesai | Keduanya berhasil; status akhir selesai */
    #[TestDox('C04-UT03 Normal: status feedback dapat dimajukan')]
    public function test_ut03_majukan_status(): void
    {
        $f = $this->feedback();

        $this->assertTrue($f->perbaruiStatus(StatusFeedback::Ditinjau)->berhasil);
        $this->assertTrue($f->perbaruiStatus(StatusFeedback::Selesai)->berhasil);
        $this->assertSame('selesai', $f->fresh()->status->value);
    }

    /** @uji C04-UT04 | Negatif | perbaruiStatus(status: StatusFeedback): HasilOperasi | Status selesai dimundurkan ke baru; status diubah pihak lain sebelum disimpan | STATUS_TIDAK_VALID dan STATUS_BERUBAH */
    #[TestDox('C04-UT04 Negatif: status tidak dapat dimundurkan atau ditimpa')]
    public function test_ut04_status_ditolak(): void
    {
        $f = $this->feedback();
        $salinan = DataFeedback::find($f->id_feedback);
        $f->perbaruiStatus(StatusFeedback::Selesai);

        $this->assertSame('STATUS_TIDAK_VALID', $f->perbaruiStatus(StatusFeedback::Baru)->kode);
        $this->assertSame('STATUS_BERUBAH', $salinan->perbaruiStatus(StatusFeedback::Ditinjau)->kode);
    }
}
