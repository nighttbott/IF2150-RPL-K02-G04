<?php

namespace Database\Seeders;

use App\Enums\JenisAksara;
use App\Enums\StatusPublikasi;
use App\Models\KontenMateri;
use App\Models\MateriAksara;
use App\Support\Materi\DataKonten;
use App\Support\Materi\PengolahKonten;
use Illuminate\Database\Seeder;

/**
 * Materi awal (terbit). Jalankan: php artisan db:seed --class=MateriSeeder
 * Isi aturan dan contoh perlu diperiksa ejaannya oleh yang paham bahasa sebelum rilis.
 * Aksara pada tingkat 1 berasal dari template_aksara.json (kode latin -> karakter).
 */
class MateriSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->daftar() as $m) {
            if (MateriAksara::where('nama_materi', $m['nama'])->where('jenis_aksara', $m['jenis']->value)->exists()) {
                continue; // aman dijalankan ulang
            }

            $bentuk = $m['bentuk'] ?? PengolahKonten::bentukDariNama($m['jenis'], $m['nama']);
            $data = new DataKonten(
                namaMateri: $m['nama'],
                jenisAksara: $m['jenis'],
                tingkatKesulitan: $m['tingkat'],
                statusPublikasi: StatusPublikasi::Terbit,
                bentukAksara: $bentuk,
                aturan: $m['aturan'],
                contoh: $m['contoh'],
                versiAplikasiMinimum: (string) config('ngaksara.versi_aplikasi'),
            );

            $materi = new MateriAksara;
            $materi->simpan($data->metadata());
            (new KontenMateri(['id_materi' => $materi->id_materi]))->simpanKonten($data);
        }
    }

    /** @return list<array<string, mixed>> */
    private function daftar(): array
    {
        $jawaDasar = 'Aksara nglegena adalah aksara dasar Jawa. Setiap aksara mewakili satu suku kata berbunyi "a". Tulis dari kiri ke kanan tanpa spasi antarkata.';
        $sundaDasar = 'Aksara ngalagena adalah aksara dasar Sunda. Setiap aksara mewakili satu suku kata berbunyi "a". Tulis dari kiri ke kanan.';

        return [
            ['nama' => 'Nglegena: ha na ca ra ka', 'jenis' => JenisAksara::Jawa, 'tingkat' => 1, 'aturan' => $jawaDasar.' Aksara "ha" di awal kata dapat berfungsi sebagai vokal "a" (ꦲꦤ dibaca "ana").', 'contoh' => 'ꦲꦤ = "ana"; ꦕꦫ = "cara"'],
            ['nama' => 'Nglegena: da ta sa wa la', 'jenis' => JenisAksara::Jawa, 'tingkat' => 1, 'aturan' => $jawaDasar, 'contoh' => 'ꦢꦱ = "dasa"; ꦱꦮ = "sawa"; ꦠꦭ = "tala"'],
            ['nama' => 'Nglegena: pa dha ja ya nya', 'jenis' => JenisAksara::Jawa, 'tingkat' => 1, 'aturan' => $jawaDasar, 'contoh' => 'ꦥꦝ = "padha"; ꦗꦪ = "jaya"'],
            ['nama' => 'Nglegena: ma ga ba tha nga', 'jenis' => JenisAksara::Jawa, 'tingkat' => 1, 'aturan' => $jawaDasar, 'contoh' => 'ꦩꦒ = "maga"; ꦧꦔ = "banga"'],
            [
                'nama' => 'Sandhangan swara', 'jenis' => JenisAksara::Jawa, 'tingkat' => 2,
                'bentuk' => 'ꦲꦶ ꦲꦸ ꦲꦺ ꦲꦺꦴ ꦲꦼ',
                'aturan' => 'Sandhangan swara mengubah bunyi vokal aksara: wulu (i), suku (u), taling (é), taling tarung (o), dan pepet (ê). Contoh di bawah memakai aksara "ha".',
                'contoh' => 'ꦲꦶ = "hi"; ꦲꦸ = "hu"; ꦲꦺ = "hé"; ꦲꦺꦴ = "ho"; ꦲꦼ = "hê"',
            ],
            ['nama' => 'Ngalagena: ka ga nga ca ja', 'jenis' => JenisAksara::Sunda, 'tingkat' => 1, 'aturan' => $sundaDasar, 'contoh' => 'ᮊᮌ = "kaga"'],
            ['nama' => 'Ngalagena: nya ta da na pa', 'jenis' => JenisAksara::Sunda, 'tingkat' => 1, 'aturan' => $sundaDasar, 'contoh' => 'ᮒᮓ = "tada"; ᮔᮕ = "napa"'],
            ['nama' => 'Ngalagena: ba ma ya ra la', 'jenis' => JenisAksara::Sunda, 'tingkat' => 1, 'aturan' => $sundaDasar, 'contoh' => 'ᮘᮙ = "bama"; ᮛᮜ = "rala"'],
            ['nama' => 'Ngalagena: wa sa ha', 'jenis' => JenisAksara::Sunda, 'tingkat' => 1, 'aturan' => $sundaDasar, 'contoh' => 'ᮝᮞ = "wasa"; ᮞᮠ = "saha"'],
        ];
    }
}
