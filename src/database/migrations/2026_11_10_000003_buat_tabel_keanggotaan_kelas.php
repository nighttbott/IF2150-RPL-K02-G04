<?php

use App\Support\BatasanCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabel keanggotaan_kelas (C24 KeanggotaanKelas, pemilik modul: Abduh; migrasi disiapkan Natan). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keanggotaan_kelas', function (Blueprint $table) {
            $table->id('id_keanggotaan');
            $table->foreignId('id_kelas')->constrained('kelas_belajar', 'id_kelas')->cascadeOnDelete();
            $table->foreignId('id_pelajar')->constrained('akun_pengguna', 'id_akun');
            $table->string('status', 10)->default('aktif');
            $table->unique(['id_kelas', 'id_pelajar'], 'uniq_anggota_kelas');
            $table->index(['id_kelas', 'status'], 'idx_anggota_status');
        });

        BatasanCheck::tambah('keanggotaan_kelas', 'chk_anggota_status', BatasanCheck::salahSatu('status', ['aktif', 'keluar', 'nonaktif']));
    }

    public function down(): void
    {
        Schema::dropIfExists('keanggotaan_kelas');
    }
};
