<?php

use App\Support\BatasanCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel kelas_belajar (C23 KelasBelajar, pemilik modul: Marvel; migrasi disiapkan Natan).
 * Kode bergabung harus unik (KF25).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas_belajar', function (Blueprint $table) {
            $table->id('id_kelas');
            $table->string('nama', 100);
            $table->foreignId('pemilik_id')->constrained('akun_pengguna', 'id_akun');
            $table->string('kode_bergabung', 10)->unique();
            $table->string('status', 10)->default('aktif');
            $table->boolean('status_scoreboard')->default(false);
        });

        BatasanCheck::tambah('kelas_belajar', 'chk_kelas_status', BatasanCheck::salahSatu('status', ['aktif', 'nonaktif']));
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_belajar');
    }
};
