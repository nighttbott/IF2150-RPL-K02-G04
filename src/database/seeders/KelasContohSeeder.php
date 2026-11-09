<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Satu kelas contoh milik Bu Rina: tiga anggota aktif dan satu anggota yang sudah keluar
 * (untuk menguji bahwa anggota tidak aktif tidak tampil di pemantauan), serta satu tugas
 * terbit berisi dua komponen. Ditulis lewat query langsung agar tidak bergantung pada model
 * modul kelas.
 */
class KelasContohSeeder extends Seeder
{
    public function run(): void
    {
        $akun = fn (string $email) => (int) DB::table('akun_pengguna')->where('email', $email)->value('id_akun');

        if (DB::table('kelas_belajar')->where('kode_bergabung', 'K7M2QX')->exists()) {
            return; // aman dijalankan ulang
        }

        $idKelas = DB::table('kelas_belajar')->insertGetId([
            'nama' => 'Aksara Jawa Dasar', 'pemilik_id' => $akun('pengajar@ngaksara.test'),
            'kode_bergabung' => 'K7M2QX', 'status' => 'aktif', 'status_scoreboard' => true,
        ], 'id_kelas');

        $anggota = ['pelajar@ngaksara.test' => 'aktif', 'budi@ngaksara.test' => 'aktif', 'wulan@ngaksara.test' => 'aktif', 'raka@ngaksara.test' => 'keluar'];
        foreach ($anggota as $email => $status) {
            DB::table('keanggotaan_kelas')->insert(['id_kelas' => $idKelas, 'id_pelajar' => $akun($email), 'status' => $status]);
        }

        $idMateri = (int) DB::table('materi_aksara')->where('nama_materi', 'Nglegena: ha na ca ra ka')->value('id_materi');
        $idTugas = DB::table('tugas_kelas')->insertGetId([
            'id_kelas' => $idKelas,
            'instruksi' => 'Pelajari materi nglegena baris pertama, lalu selesaikan latihan menulis.',
            'tenggat' => now()->addDays(5)->setTime(23, 59)->format('Y-m-d H:i:s'),
            'status' => 'terbit',
        ], 'id_tugas');

        DB::table('komponen_tugas')->insert([
            ['id_tugas' => $idTugas, 'jenis' => 'materi', 'id_materi' => $idMateri, 'mode' => null, 'wajib' => true],
            ['id_tugas' => $idTugas, 'jenis' => 'latihan', 'id_materi' => $idMateri, 'mode' => 'menulis', 'wajib' => true],
        ]);
    }
}
