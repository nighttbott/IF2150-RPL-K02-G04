<?php

use App\Support\BatasanCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabel tugas_kelas (C25 TugasKelas, pemilik modul: Abduh; migrasi disiapkan Natan). Tugas dibuat sebagai draf lalu diterbitkan (KF27). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas_kelas', function (Blueprint $table) {
            $table->id('id_tugas');
            $table->foreignId('id_kelas')->constrained('kelas_belajar', 'id_kelas')->cascadeOnDelete();
            $table->text('instruksi');
            $table->dateTime('tenggat');
            $table->string('status', 10)->default('draf');
        });

        BatasanCheck::tambah('tugas_kelas', 'chk_tugas_status', BatasanCheck::salahSatu('status', ['draf', 'terbit']));
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_kelas');
    }
};
