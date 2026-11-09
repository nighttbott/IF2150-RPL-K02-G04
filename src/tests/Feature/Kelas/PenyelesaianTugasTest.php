<?php

namespace Tests\Feature\Kelas;

use App\Models\PenyelesaianTugas;
use App\Models\TugasKelas;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\Support\SkemaKelas;

class PenyelesaianTugasTest extends SkemaKelas
{
    public function test_status_dibaca_sekaligus_dan_tidak_mengambil_milik_pelajar_lain(): void
    {
        $id1 = (new TugasKelas)->simpan($this->dataTugas())->data['id_tugas'];
        $id2 = (new TugasKelas)->simpan($this->dataTugas())->data['id_tugas'];
        DB::table('penyelesaian_tugas')->insert([
            ['id_tugas' => $id1, 'id_pelajar' => 1, 'waktu_kumpul' => '2026-11-09 11:00:00', 'status' => 'tepat_waktu'],
            ['id_tugas' => $id2, 'id_pelajar' => 2, 'waktu_kumpul' => '2026-11-10 11:00:00', 'status' => 'terlambat'],
        ]);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $hasil = PenyelesaianTugas::ambilStatus(1, [$id2, $id1]);
        $this->assertCount(1, DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertSame([$id2, $id1], $hasil->pluck('id_tugas')->all());
        $this->assertSame(['belum_selesai', 'tepat_waktu'], $hasil->pluck('status')->all());
        $this->assertNull($hasil[0]['waktu_kumpul']);
        $this->assertInstanceOf(CarbonImmutable::class, $hasil[1]['waktu_kumpul']);
    }

    public function test_daftar_kosong_tidak_melakukan_query(): void
    {
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->assertCount(0, PenyelesaianTugas::ambilStatus(1, []));
        $this->assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_id_tugas_bukan_integer_positif_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PenyelesaianTugas::ambilStatus(1, [1, 'bukan-id']);
    }

    public function test_mapping_primary_key_tabel_dan_waktu(): void
    {
        $model = new PenyelesaianTugas;
        $this->assertSame('penyelesaian_tugas', $model->getTable());
        $this->assertSame('id_penyelesaian', $model->getKeyName());
        $this->assertFalse($model->usesTimestamps());
    }
}
