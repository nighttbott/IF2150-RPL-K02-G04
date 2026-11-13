<?php

use App\Support\BatasanCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tabel riwayat_latihan (C03 RiwayatLatihan, pemilik modul: Natan). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_latihan', function (Blueprint $table) {
            $table->id('id_riwayat');
            $table->foreignId('id_pelajar')->constrained('akun_pengguna', 'id_akun');
            $table->foreignId('id_materi')->nullable()->constrained('materi_aksara', 'id_materi');
            $table->string('target_soal', 50)->nullable();
            $table->string('mode', 15)->nullable();
            $table->decimal('skor', 5, 2)->nullable();
            $table->dateTime('tanggal_pengerjaan');
            $table->string('status', 15);
            $table->foreignId('id_tugas')->nullable()->constrained('tugas_kelas', 'id_tugas')->nullOnDelete();
            $table->foreignId('id_komponen')->nullable()->constrained('komponen_tugas', 'id_komponen')->nullOnDelete();
            $table->string('mode_aktivitas', 12)->default('latihan');
            $table->dateTime('waktu_selesai')->nullable();
        });

        BatasanCheck::tambah('riwayat_latihan', 'chk_riwayat_skor', '(skor IS NULL OR skor BETWEEN 0 AND 100)');
        BatasanCheck::tambah('riwayat_latihan', 'chk_riwayat_status', BatasanCheck::salahSatu('status', ['berhasil', 'perlu_diulang', 'selesai']));
        BatasanCheck::tambah('riwayat_latihan', 'chk_riwayat_mode', BatasanCheck::salahSatu('mode', ['menulis', 'bunyi', 'merangkai', 'transliterasi'], true));
        BatasanCheck::tambah('riwayat_latihan', 'chk_riwayat_aktivitas', BatasanCheck::salahSatu('mode_aktivitas', ['latihan', 'materi', 'tugas', 'pengumpulan']));
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_latihan');
    }
};
