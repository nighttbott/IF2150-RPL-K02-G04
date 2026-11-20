<?php

namespace Tests\Unit\Hiupl;

use App\Enums\JenisAksara;
use App\Enums\StatusPublikasi;
use App\Models\MateriAksara;
use App\Support\Materi\FilterMateri;
use App\Support\Materi\MetadataMateri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * HIUPL 4.2: pengujian unit kelas C02 MateriAksara (tingkat Mendalam: validasi metadata, filter publikasi, rekomendasi materi).
 */
class C02MateriAksaraTest extends TestCase
{
    use BantuanMateri, RefreshDatabase;

    private function meta(string $nama = 'Nglegena 1', int $tingkat = 1): MetadataMateri
    {
        return new MetadataMateri(null, $nama, JenisAksara::Jawa, $tingkat, StatusPublikasi::Draf);
    }

    /** @uji C02-UT01 | Normal | simpan(data: MetadataMateri): HasilOperasi | Nama "Nglegena 1", jawa, tingkat 1, draf | Berhasil; metadata tersimpan */
    #[TestDox('C02-UT01 Normal: metadata materi tersimpan')]
    public function test_ut01_simpan(): void
    {
        $this->assertTrue((new MateriAksara)->simpan($this->meta())->berhasil);
        $this->assertDatabaseHas('materi_aksara', ['nama_materi' => 'Nglegena 1', 'tingkat_kesulitan' => 1, 'status_publikasi' => 'draf']);
    }

    /** @uji C02-UT02 | Batas | simpan(data: MetadataMateri): HasilOperasi | Tingkat 3 (maksimum), tingkat 0 dan 4 (di luar rentang), nama tepat 100 karakter | Tingkat 3 dan nama 100 karakter diterima; tingkat 0 dan 4 DATA_TIDAK_VALID */
    #[TestDox('C02-UT02 Batas: tingkat 1..3 dan nama 100 karakter')]
    public function test_ut02_simpan_batas(): void
    {
        $this->assertTrue((new MateriAksara)->simpan($this->meta('Tingkat Tiga', 3))->berhasil);
        $this->assertTrue((new MateriAksara)->simpan($this->meta(str_repeat('n', 100)))->berhasil);
        $this->assertSame('DATA_TIDAK_VALID', (new MateriAksara)->simpan($this->meta('Nol', 0))->kode);
        $this->assertSame('DATA_TIDAK_VALID', (new MateriAksara)->simpan($this->meta('Empat', 4))->kode);
    }

    /** @uji C02-UT03 | Negatif | simpan(data: MetadataMateri): HasilOperasi | Nama kosong; nama 101 karakter; nama/jenis/tingkat sama dengan materi yang ada | NAMA_TIDAK_VALID, NAMA_TIDAK_VALID, DUPLIKAT */
    #[TestDox('C02-UT03 Negatif: nama tidak valid dan duplikat ditolak')]
    public function test_ut03_simpan_ditolak(): void
    {
        (new MateriAksara)->simpan($this->meta());

        $this->assertSame('NAMA_TIDAK_VALID', (new MateriAksara)->simpan($this->meta('  '))->kode);
        $this->assertSame('NAMA_TIDAK_VALID', (new MateriAksara)->simpan($this->meta(str_repeat('n', 101)))->kode);
        $this->assertSame('DUPLIKAT', (new MateriAksara)->simpan($this->meta())->kode);
        $this->assertSame(1, MateriAksara::count());
    }

    /** @uji C02-UT04 | Normal | ambilDaftarMateri(filter: FilterMateri): List | Filter jawa tingkat 1; ada materi terbit, draf, arsip | Hanya materi terbit */
    #[TestDox('C02-UT04 Normal: daftar hanya materi terbit')]
    public function test_ut04_daftar_terbit(): void
    {
        $this->materiLengkap('Terbit');
        $this->materiLengkap('Draf', 1, StatusPublikasi::Draf);
        $this->materiLengkap('Arsip', 1, StatusPublikasi::Arsip);
        $daftar = MateriAksara::ambilDaftarMateri(new FilterMateri(JenisAksara::Jawa, 1));

        $this->assertSame(['Terbit'], array_map(fn ($m) => $m->nama_materi, $daftar));
    }

    /** @uji C02-UT05 | Batas | ambilDaftarMateri(filter: FilterMateri): List | termasukDraf = true (mode Tim Materi) | Terbit dan draf tampil; arsip tetap tidak tampil */
    #[TestDox('C02-UT05 Batas: mode kelola menampilkan draf tetapi tidak arsip')]
    public function test_ut05_daftar_dengan_draf(): void
    {
        $this->materiLengkap('Terbit');
        $this->materiLengkap('Draf', 1, StatusPublikasi::Draf);
        $this->materiLengkap('Arsip', 1, StatusPublikasi::Arsip);
        $nama = array_map(fn ($m) => $m->nama_materi, MateriAksara::ambilDaftarMateri(new FilterMateri(null, null, true)));

        $this->assertEqualsCanonicalizing(['Terbit', 'Draf'], $nama);
    }

    /** @uji C02-UT06 | Negatif | ambilDaftarMateri(filter: FilterMateri): List | Filter jenis sunda padahal hanya ada materi jawa | Daftar kosong (memicu UC02 Alternatif 1 "materi belum tersedia") */
    #[TestDox('C02-UT06 Negatif: filter tanpa hasil mengembalikan daftar kosong')]
    public function test_ut06_daftar_kosong(): void
    {
        $this->materiLengkap();

        $this->assertSame([], MateriAksara::ambilDaftarMateri(new FilterMateri(JenisAksara::Sunda)));
    }

    /** @uji C02-UT07 | Normal | ambilTargetLatihan(mode: ModeLatihan): TargetLatihan | Mode menulis untuk materi berisi ꦲ ꦤ | Dua butir target (ꦲ dan ꦤ) beserta templat goresan */
    #[TestDox('C02-UT07 Normal: target latihan menulis dari bentuk aksara')]
    public function test_ut07_target_menulis(): void
    {
        $t = $this->materiLengkap()->ambilTargetLatihan('menulis');

        $this->assertSame(['ꦲ', 'ꦤ'], array_column($t->butir, 'karakter'));
        $this->assertNotNull($t->butir[0]['template']);
    }

    /** @uji C02-UT08 | Batas | ambilTargetLatihan(mode: ModeLatihan): TargetLatihan | Mode merangkai (memakai pasangan contoh, bukan bentuk) | Butir berasal dari contoh "ꦲꦤ = ana" */
    #[TestDox('C02-UT08 Batas: mode merangkai memakai pasangan contoh')]
    public function test_ut08_target_merangkai(): void
    {
        $t = $this->materiLengkap()->ambilTargetLatihan('merangkai');

        $this->assertFalse($t->kosongkah());
        $this->assertSame('merangkai', $t->mode);
    }

    /** @uji C02-UT09 | Negatif | ambilTargetLatihan(mode: ModeLatihan): TargetLatihan | Materi tanpa konten | TargetLatihan kosong */
    #[TestDox('C02-UT09 Negatif: materi tanpa konten menghasilkan target kosong')]
    public function test_ut09_target_tanpa_konten(): void
    {
        $m = new MateriAksara;
        $m->simpan($this->meta());

        $this->assertTrue($m->ambilTargetLatihan('menulis')->kosongkah());
    }

    /** @uji C02-UT10 | Normal | ambilMateriRekomendasi(penguasaan: Map): List | Materi tingkat 1 belum tuntas (40) dan tingkat 2 | Rekomendasi materi tingkat 1 (tingkat 2 masih terkunci) */
    #[TestDox('C02-UT10 Normal: rekomendasi materi yang belum tuntas pada tingkat terbuka')]
    public function test_ut10_rekomendasi(): void
    {
        $t1 = $this->materiLengkap('T1');
        $this->materiLengkap('T2', 2);
        $rek = MateriAksara::ambilMateriRekomendasi([$t1->id_materi => 40.0]);

        $this->assertSame(['T1'], array_map(fn ($m) => $m->nama_materi, $rek));
    }

    /** @uji C02-UT11 | Batas | ambilMateriRekomendasi(penguasaan: Map): List | Penguasaan tingkat 1 tepat 70 (ambang tuntas); lima materi tingkat 2 | Tingkat 1 dianggap tuntas; tingkat 2 terbuka; maksimal 3 rekomendasi */
    #[TestDox('C02-UT11 Batas: penguasaan 70 membuka tingkat berikutnya, maksimal tiga')]
    public function test_ut11_rekomendasi_ambang(): void
    {
        $t1 = $this->materiLengkap('T1');
        foreach (range(1, 5) as $i) {
            $this->materiLengkap("T2-$i", 2);
        }
        $rek = MateriAksara::ambilMateriRekomendasi([$t1->id_materi => 70.0]);

        $this->assertCount(3, $rek);
        $this->assertSame([2, 2, 2], array_map(fn ($m) => $m->tingkat_kesulitan, $rek));
    }

    /** @uji C02-UT12 | Negatif | ambilMateriRekomendasi(penguasaan: Map): List | Semua materi sudah tuntas (≥ 70) | Daftar kosong */
    #[TestDox('C02-UT12 Negatif: semua tuntas tanpa rekomendasi')]
    public function test_ut12_rekomendasi_kosong(): void
    {
        $t1 = $this->materiLengkap('T1');

        $this->assertSame([], MateriAksara::ambilMateriRekomendasi([$t1->id_materi => 100.0]));
    }

    /** @uji C02-UT13 | Normal | perbaruiData(data: MetadataMateri): HasilOperasi | Ganti nama dan status menjadi terbit | Berhasil; data berubah */
    #[TestDox('C02-UT13 Normal: metadata dapat diperbarui')]
    public function test_ut13_perbarui(): void
    {
        $m = $this->materiLengkap('Lama', 1, StatusPublikasi::Draf);

        $this->assertTrue($m->perbaruiData(new MetadataMateri($m->id_materi, 'Baru', JenisAksara::Jawa, 1, StatusPublikasi::Terbit))->berhasil);
        $this->assertDatabaseHas('materi_aksara', ['id_materi' => $m->id_materi, 'nama_materi' => 'Baru', 'status_publikasi' => 'terbit']);
    }

    /** @uji C02-UT14 | Batas | perbaruiData(data: MetadataMateri): HasilOperasi | Data yang sama persis dengan dirinya sendiri | Berhasil (tidak dianggap duplikat dirinya) */
    #[TestDox('C02-UT14 Batas: menyimpan data yang sama tidak dianggap duplikat')]
    public function test_ut14_perbarui_sama(): void
    {
        $m = $this->materiLengkap();

        $this->assertTrue($m->perbaruiData(new MetadataMateri($m->id_materi, 'Nglegena 1', JenisAksara::Jawa, 1, StatusPublikasi::Terbit))->berhasil);
    }

    /** @uji C02-UT15 | Negatif | perbaruiData(data: MetadataMateri): HasilOperasi | Nama diganti menjadi nama materi lain (jenis dan tingkat sama); objek belum tersimpan | DUPLIKAT; TIDAK_DITEMUKAN */
    #[TestDox('C02-UT15 Negatif: duplikat dan materi tidak ada ditolak')]
    public function test_ut15_perbarui_ditolak(): void
    {
        $this->materiLengkap('A');
        $b = $this->materiLengkap('B');

        $this->assertSame('DUPLIKAT', $b->perbaruiData(new MetadataMateri($b->id_materi, 'A', JenisAksara::Jawa, 1, StatusPublikasi::Terbit))->kode);
        $this->assertSame('TIDAK_DITEMUKAN', (new MateriAksara)->perbaruiData($this->meta('Z'))->kode);
    }

    /** @uji C02-UT16 | Normal | ambilMetadata(): MetadataMateri | Materi terbit dengan konten versi 1.0 | Metadata berisi nama, jenis, tingkat, status, versiKonten 1.0 */
    #[TestDox('C02-UT16 Normal: ambilMetadata lengkap')]
    public function test_ut16_metadata(): void
    {
        $m = $this->materiLengkap()->ambilMetadata();

        $this->assertSame(['Nglegena 1', 1, '1.0'], [$m->namaMateri, $m->tingkatKesulitan, $m->versiKonten]);
    }

    /** @uji C02-UT17 | Batas | ambilMetadata(): MetadataMateri | Konten sudah diperbarui sekali | versiKonten mengikuti versi terbaru (1.1) */
    #[TestDox('C02-UT17 Batas: metadata mengikuti versi konten terbaru')]
    public function test_ut17_metadata_versi(): void
    {
        $m = $this->materiLengkap();
        $d = $this->dataKonten();
        $d->aturan = 'Baru.';
        $m->konten->perbaruiKonten($d);

        $this->assertSame('1.1', $m->fresh()->ambilMetadata()->versiKonten);
    }

    /** @uji C02-UT18 | Negatif | ambilMetadata(): MetadataMateri | Materi tanpa konten | versiKonten null tanpa galat */
    #[TestDox('C02-UT18 Negatif: materi tanpa konten tidak punya versi')]
    public function test_ut18_metadata_tanpa_konten(): void
    {
        $m = new MateriAksara;
        $m->simpan($this->meta('Kosong'));

        $this->assertNull($m->ambilMetadata()->versiKonten);
    }
}
