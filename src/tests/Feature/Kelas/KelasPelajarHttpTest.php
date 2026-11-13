<?php

namespace Tests\Feature\Kelas;

use App\Models\KeanggotaanKelas;
use Illuminate\Auth\GenericUser;
use Tests\Support\SkemaKelas;

class KelasPelajarHttpTest extends SkemaKelas
{
    public function test_tamu_ditolak(): void
    {
        $this->get('/pelajar/kelas')->assertRedirect(route('masuk'));
    }

    public function test_pengajar_ditolak(): void
    {
        $this->actingAs(new GenericUser(['id' => 3, 'peran' => 'PENGAJAR']))->get('/pelajar/kelas')->assertForbidden();
    }

    public function test_kelas_lain_ditolak(): void
    {
        $this->actingAs(new GenericUser(['id' => 1, 'peran' => 'PELAJAR']))->get('/pelajar/kelas/2')->assertNotFound();
    }

    public function test_gabung_memakai_identitas_sesi_bukan_request(): void
    {
        $this->actingAs(new GenericUser(['id' => 1, 'peran' => 'PELAJAR']))->post('/pelajar/kelas/gabung', ['kode' => 'abc234', 'id_pelajar' => 2])->assertRedirect('/pelajar/kelas/1');
        $this->assertTrue(KeanggotaanKelas::periksaAkses(1, 1));
        $this->assertFalse(KeanggotaanKelas::periksaAkses(2, 1));
    }

    public function test_kode_salah_ditolak(): void
    {
        $this->actingAs(new GenericUser(['id' => 1, 'peran' => 'PELAJAR']))->post('/pelajar/kelas/gabung', ['kode' => 'ZZZ999'])->assertSessionHasErrors('kode');
    }
}
