<?php

namespace Tests\Feature\Kelas;

use App\Models\TugasKelas;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\SkemaKelas;

class TugasKelasTest extends SkemaKelas
{
    public function test_draf_dan_komponen_disimpan_bersama_status_request_diabaikan(): void
    {
        $hasil = (new TugasKelas)->simpan($this->dataTugas(['status' => 'terbit']));
        $this->assertTrue($hasil->berhasil);
        $this->assertDatabaseHas('tugas_kelas', ['id_tugas' => $hasil->data['id_tugas'], 'status' => 'draf']);
        $this->assertDatabaseCount('komponen_tugas', 2);
        $this->assertCount(2, TugasKelas::findOrFail($hasil->data['id_tugas'])->komponen);
    }

    public static function dataTidakValid(): array
    {
        return [
            'instruksi kosong' => [['instruksi' => '   ']],
            'tenggat lampau' => [['tenggat' => '2026-11-08 23:59:00']],
            'tepat sekarang' => [['tenggat' => '2026-11-09 10:00:00']],
            'tanggal rusak' => [['tenggat' => 'bukan tanggal']],
            'kelas hilang' => [['id_kelas' => 999]],
            'kelas nonaktif' => [['id_kelas' => 2]],
            'komponen kosong' => [['komponen_tugas' => []]],
            'tanpa wajib' => [['komponen_tugas' => [['jenis' => 'materi', 'id_materi' => 1, 'wajib' => false]]]],
            'materi hilang' => [['komponen_tugas' => [['jenis' => 'materi', 'id_materi' => 999, 'wajib' => true]]]],
            'materi arsip' => [['komponen_tugas' => [['jenis' => 'materi', 'id_materi' => 2, 'wajib' => true]]]],
            'jenis asing' => [['komponen_tugas' => [['jenis' => 'kuis', 'id_materi' => 1, 'wajib' => true]]]],
            'mode asing' => [['komponen_tugas' => [['jenis' => 'latihan', 'id_materi' => 1, 'mode' => 'asing', 'wajib' => true]]]],
            'latihan tanpa mode' => [['komponen_tugas' => [['jenis' => 'latihan', 'id_materi' => 1, 'wajib' => true]]]],
            'materi dengan mode' => [['komponen_tugas' => [['jenis' => 'materi', 'id_materi' => 1, 'mode' => 'menulis', 'wajib' => true]]]],
            'wajib bukan boolean' => [['komponen_tugas' => [['jenis' => 'materi', 'id_materi' => 1, 'wajib' => 'false']]]],
            'komponen bukan objek' => [['komponen_tugas' => [true]]],
            'bukan daftar' => [['komponen_tugas' => ['abc' => ['jenis' => 'materi', 'id_materi' => 1, 'wajib' => true]]]],
        ];
    }

    #[DataProvider('dataTidakValid')]
    public function test_data_tidak_valid_tidak_menyimpan_apapun(array $override): void
    {
        $hasil = (new TugasKelas)->simpan($this->dataTugas($override));
        $this->assertFalse($hasil->berhasil);
        $this->assertDatabaseCount('tugas_kelas', 0);
        $this->assertDatabaseCount('komponen_tugas', 0);
    }

    public function test_draf_bisa_direvisi_dengan_komponen_baru(): void
    {
        $id = (new TugasKelas)->simpan($this->dataTugas())->data['id_tugas'];
        $hasil = (new TugasKelas)->simpan($this->dataTugas([
            'id_tugas' => $id, 'instruksi' => 'Instruksi revisi',
            'komponen_tugas' => [['jenis' => 'materi', 'id_materi' => 1, 'wajib' => true]],
        ]));
        $this->assertTrue($hasil->berhasil);
        $this->assertDatabaseCount('tugas_kelas', 1);
        $this->assertDatabaseCount('komponen_tugas', 1);
        $this->assertDatabaseHas('tugas_kelas', ['id_tugas' => $id, 'instruksi' => 'Instruksi revisi']);
    }

    public function test_gagal_insert_komponen_membatalkan_metadata(): void
    {
        DB::unprepared("CREATE TRIGGER gagal_komponen BEFORE INSERT ON komponen_tugas WHEN NEW.jenis = 'latihan' BEGIN SELECT RAISE(ABORT, 'kegagalan uji'); END");
        $hasil = (new TugasKelas)->simpan($this->dataTugas());
        $this->assertFalse($hasil->berhasil);
        $this->assertSame('gagalSimpan', $hasil->kode);
        $this->assertDatabaseCount('tugas_kelas', 0);
        $this->assertDatabaseCount('komponen_tugas', 0);
    }

    public function test_gagal_revisi_mempertahankan_draf_dan_komponen_lama(): void
    {
        $id = (new TugasKelas)->simpan($this->dataTugas())->data['id_tugas'];
        $lama = DB::table('komponen_tugas')->orderBy('id_komponen')->get()->toArray();
        DB::unprepared("CREATE TRIGGER gagal_revisi BEFORE INSERT ON komponen_tugas BEGIN SELECT RAISE(ABORT, 'kegagalan uji'); END");
        $hasil = (new TugasKelas)->simpan($this->dataTugas(['id_tugas' => $id, 'instruksi' => 'Jangan tersimpan']));
        $this->assertFalse($hasil->berhasil);
        $this->assertDatabaseHas('tugas_kelas', ['id_tugas' => $id, 'instruksi' => $this->dataTugas()['instruksi']]);
        $this->assertEquals($lama, DB::table('komponen_tugas')->orderBy('id_komponen')->get()->toArray());
    }

    public function test_terbitkan_idempoten_dan_tugas_terbit_tidak_bisa_diubah(): void
    {
        $id = (new TugasKelas)->simpan($this->dataTugas())->data['id_tugas'];
        $tugas = TugasKelas::findOrFail($id);
        $this->assertTrue($tugas->terbitkan()->berhasil);
        $this->assertSame('sudahTerbit', $tugas->terbitkan()->kode);
        $this->assertSame('terbit', $tugas->fresh()->status);
        $hasil = (new TugasKelas)->simpan($this->dataTugas(['id_tugas' => $id, 'instruksi' => 'Jangan ubah']));
        $this->assertFalse($hasil->berhasil);
        $this->assertSame($this->dataTugas()['instruksi'], $tugas->fresh()->instruksi);
    }

    public function test_draf_yang_sudah_lewat_tenggat_tidak_bisa_diterbitkan(): void
    {
        $id = (new TugasKelas)->simpan($this->dataTugas())->data['id_tugas'];
        $this->travelTo(now()->addDays(3));
        $this->assertFalse(TugasKelas::findOrFail($id)->terbitkan()->berhasil);
        $this->assertDatabaseHas('tugas_kelas', ['id_tugas' => $id, 'status' => 'draf']);
    }

    public function test_penerbitan_memeriksa_ulang_materi_dan_kelas(): void
    {
        $id = (new TugasKelas)->simpan($this->dataTugas())->data['id_tugas'];
        DB::table('materi_aksara')->where('id_materi', 1)->update(['status_publikasi' => 'arsip']);
        $this->assertFalse(TugasKelas::findOrFail($id)->terbitkan()->berhasil);
        DB::table('materi_aksara')->where('id_materi', 1)->update(['status_publikasi' => 'terbit']);
        DB::table('kelas_belajar')->where('id_kelas', 1)->update(['status' => 'NONAKTIF']);
        $this->assertFalse(TugasKelas::findOrFail($id)->terbitkan()->berhasil);
    }

    public function test_revisi_tidak_bisa_memindahkan_tugas_ke_kelas_lain(): void
    {
        $id = (new TugasKelas)->simpan($this->dataTugas())->data['id_tugas'];
        DB::table('kelas_belajar')->where('id_kelas', 2)->update(['status' => 'AKTIF']);
        $hasil = (new TugasKelas)->simpan($this->dataTugas(['id_kelas' => 2, 'id_tugas' => $id]));
        $this->assertFalse($hasil->berhasil);
        $this->assertDatabaseHas('tugas_kelas', ['id_tugas' => $id, 'id_kelas' => 1]);
    }

    public function test_daftar_dan_detail_hanya_tugas_terbit_milik_kelas_termasuk_lewat_tenggat(): void
    {
        foreach ([['id_tugas' => 1, 'id_kelas' => 1, 'status' => 'terbit', 'tenggat' => '2026-11-08 23:59:00'],
            ['id_tugas' => 2, 'id_kelas' => 1, 'status' => 'draf', 'tenggat' => '2026-11-12 23:59:00'],
            ['id_tugas' => 3, 'id_kelas' => 2, 'status' => 'terbit', 'tenggat' => '2026-11-12 23:59:00'],
            ['id_tugas' => 4, 'id_kelas' => 1, 'status' => 'terbit', 'tenggat' => '2026-11-12 23:59:00']] as $data) {
            DB::table('tugas_kelas')->insert($data + ['instruksi' => 'Uji']);
        }
        $this->assertSame([1, 4], TugasKelas::ambilTugasAktif(1)->pluck('id_tugas')->all());
        $this->assertNotNull(TugasKelas::ambilDetail(1, 1));
        $this->assertNull(TugasKelas::ambilDetail(1, 2));
        $this->assertNull(TugasKelas::ambilDetail(1, 3));
        $this->assertNull(TugasKelas::ambilDetail(1, 999));
    }

    public function test_menerbitkan_objek_baru_ditolak(): void
    {
        $this->assertFalse((new TugasKelas)->terbitkan()->berhasil);
    }
}
