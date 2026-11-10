<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Skema fiktif khusus SQLite :memory:, bukan migrasi proyek. */
abstract class SkemaKelas extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite'
            || DB::connection()->getDatabaseName() !== ':memory:') {
            throw new \RuntimeException('Fixture kelas hanya boleh berjalan pada SQLite :memory:.');
        }
        Schema::create('akun_pengguna', function (Blueprint $table): void {
            $table->id('id_akun');
            $table->string('peran');
        });
        Schema::create('kelas_belajar', function (Blueprint $table): void {
            $table->id('id_kelas');
            $table->foreignId('pemilik_id')->constrained('akun_pengguna', 'id_akun');
            $table->string('nama');
            $table->string('kode_bergabung', 6)->unique();
            $table->string('status')->default('AKTIF');
        });
        Schema::create('materi_aksara', function (Blueprint $table): void {
            $table->id('id_materi');
            $table->string('status_publikasi')->default('terbit');
        });
        Schema::create('keanggotaan_kelas', function (Blueprint $table): void {
            $table->id('id_keanggotaan');
            $table->foreignId('id_kelas')->constrained('kelas_belajar', 'id_kelas');
            $table->foreignId('id_pelajar')->constrained('akun_pengguna', 'id_akun');
            $table->string('status');
            $table->unique(['id_kelas', 'id_pelajar']);
        });
        Schema::create('tugas_kelas', function (Blueprint $table): void {
            $table->id('id_tugas');
            $table->foreignId('id_kelas')->constrained('kelas_belajar', 'id_kelas');
            $table->text('instruksi');
            $table->dateTime('tenggat');
            $table->string('status');
        });
        Schema::create('komponen_tugas', function (Blueprint $table): void {
            $table->id('id_komponen');
            $table->foreignId('id_tugas')->constrained('tugas_kelas', 'id_tugas');
            $table->string('jenis');
            $table->foreignId('id_materi')->constrained('materi_aksara', 'id_materi');
            $table->string('mode')->nullable();
            $table->boolean('wajib');
        });
        Schema::create('penyelesaian_tugas', function (Blueprint $table): void {
            $table->id('id_penyelesaian');
            $table->foreignId('id_tugas')->constrained('tugas_kelas', 'id_tugas');
            $table->foreignId('id_pelajar')->constrained('akun_pengguna', 'id_akun');
            $table->dateTime('waktu_kumpul');
            $table->string('status');
            $table->unique(['id_tugas', 'id_pelajar']);
        });
        DB::table('akun_pengguna')->insert([
            ['id_akun' => 1, 'peran' => 'PELAJAR'],
            ['id_akun' => 2, 'peran' => 'PELAJAR'],
            ['id_akun' => 3, 'peran' => 'PENGAJAR'],
        ]);
        DB::table('kelas_belajar')->insert([
            ['id_kelas' => 1, 'pemilik_id' => 3, 'nama' => 'Jawa Uji', 'kode_bergabung' => 'ABC234', 'status' => 'AKTIF'],
            ['id_kelas' => 2, 'pemilik_id' => 3, 'nama' => 'Sunda Uji', 'kode_bergabung' => 'DEF567', 'status' => 'NONAKTIF'],
        ]);
        DB::table('materi_aksara')->insert([
            ['id_materi' => 1, 'status_publikasi' => 'terbit'],
            ['id_materi' => 2, 'status_publikasi' => 'arsip'],
        ]);
        $this->travelTo(now()->setDate(2026, 11, 9)->setTime(10, 0));
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    protected function dataTugas(array $override = []): array
    {
        return array_replace([
            'id_kelas' => 1,
            'instruksi' => 'Pelajari nglegena lalu latihan menulis.',
            'tenggat' => '2026-11-10 23:59:00',
            'komponen_tugas' => [
                ['jenis' => 'materi', 'id_materi' => 1, 'mode' => null, 'wajib' => true],
                ['jenis' => 'latihan', 'id_materi' => 1, 'mode' => 'menulis', 'wajib' => true],
            ],
        ], $override);
    }
}
