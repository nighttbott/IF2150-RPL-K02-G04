<?php

use App\Support\BatasanCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabel data_feedback (C04 DataFeedback, pemilik modul: Ryuza; migrasi disiapkan Natan). Juga dipakai umpan balik Pengajar kepada anggota kelas (Algo-080). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_feedback', function (Blueprint $table) {
            $table->id('id_feedback');
            $table->foreignId('id_akun')->constrained('akun_pengguna', 'id_akun');
            $table->string('kategori', 10);
            $table->text('pesan');
            $table->dateTime('waktu_kirim');
            $table->string('status', 10)->default('baru');
        });

        BatasanCheck::tambah('data_feedback', 'chk_feedback_kategori', BatasanCheck::salahSatu('kategori', ['performa', 'tampilan', 'fitur', 'materi']));
        BatasanCheck::tambah('data_feedback', 'chk_feedback_status', BatasanCheck::salahSatu('status', ['baru', 'ditinjau', 'selesai']));
    }

    public function down(): void
    {
        Schema::dropIfExists('data_feedback');
    }
};
