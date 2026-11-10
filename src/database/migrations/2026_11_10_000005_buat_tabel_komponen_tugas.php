<?php

use App\Support\BatasanCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel komponen_tugas (pendukung C25 TugasKelas, pemilik modul: Abduh; migrasi disiapkan Natan).
 * Jenis komponen: materi atau latihan. Mode hanya diisi untuk komponen latihan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komponen_tugas', function (Blueprint $table) {
            $table->id('id_komponen');
            $table->foreignId('id_tugas')->constrained('tugas_kelas', 'id_tugas')->cascadeOnDelete();
            $table->string('jenis', 10);
            $table->foreignId('id_materi')->constrained('materi_aksara', 'id_materi');
            $table->string('mode', 15)->nullable();
            $table->boolean('wajib')->default(true);
        });

        BatasanCheck::tambah('komponen_tugas', 'chk_komponen_jenis', BatasanCheck::salahSatu('jenis', ['materi', 'latihan']));
        BatasanCheck::tambah('komponen_tugas', 'chk_komponen_mode', BatasanCheck::salahSatu('mode', ['menulis', 'bunyi', 'merangkai', 'transliterasi'], true));
    }

    public function down(): void
    {
        Schema::dropIfExists('komponen_tugas');
    }
};
