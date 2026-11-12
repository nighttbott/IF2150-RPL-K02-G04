<?php

namespace Tests\Support;

use App\Latihan\Data\SoalLatihan;
use App\Models\AkunPengguna;

/**
 * Langkah alur latihan menulis yang dipakai beberapa feature test.
 */
trait AlurLatihan
{
    /** Membuka I-07 dan mengembalikan soal aktif dari session. */
    protected function bukaSoal(AkunPengguna $pelajar, string $url = '/latihan/1/menulis'): SoalLatihan
    {
        $this->actingAs($pelajar)->get($url)->assertOk();
        // Session memakai serialisasi JSON; isinya harus tetap sama setelah lewat JSON.
        $this->assertEquals(json_decode(json_encode(session('latihan')), true), session('latihan'));

        return SoalLatihan::fromArray(session('latihan.soalAktif')['soal']);
    }

    /** Goresan templat soal (kotak 0–100) dipindah ke satuan kanvas (dibagi tinggi kanvas, 0–1). */
    protected function goresanBaik(SoalLatihan $soal): array
    {
        return array_map(fn ($g) => array_map(fn ($p) => ['x' => $p['x'] / 100, 'y' => $p['y'] / 100], $g), $soal->templat);
    }

    protected function goresanBuruk(): array
    {
        return [[['x' => 0.1, 'y' => 0.1], ['x' => 0.2, 'y' => 0.1]]];
    }

    /** Membuka soal, mengirim jawaban baik, lalu mengembalikan soalnya. */
    protected function jawabBenar(AkunPengguna $pelajar, string $url = '/latihan/1/menulis'): SoalLatihan
    {
        $soal = $this->bukaSoal($pelajar, $url);
        $this->postJson('/latihan/jawaban', ['idSoal' => $soal->idSoal, 'goresan' => $this->goresanBaik($soal)])
            ->assertOk()->assertJson(['status' => 'berhasil']);

        return $soal;
    }
}
