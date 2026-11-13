<?php

use App\Support\BatasanCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabel penyelesaian_tugas (C26 PenyelesaianTugas, pemilik modul: Abduh; migrasi disiapkan Natan). Satu Pelajar mengumpulkan satu tugas satu kali. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyelesaian_tugas', function (Blueprint $table) {
            $table->id('id_penyelesaian');
            $table->foreignId('id_tugas')->constrained('tugas_kelas', 'id_tugas')->cascadeOnDelete();
            $table->foreignId('id_pelajar')->constrained('akun_pengguna', 'id_akun');
            $table->dateTime('waktu_kumpul');
            $table->string('status', 12);
            $table->unique(['id_tugas', 'id_pelajar'], 'uniq_pengumpulan');
        });

        BatasanCheck::tambah('penyelesaian_tugas', 'chk_penyelesaian_status', BatasanCheck::salahSatu('status', ['tepat_waktu', 'terlambat']));
    }

    public function down(): void
    {
        Schema::dropIfExists('penyelesaian_tugas');
    }
};
