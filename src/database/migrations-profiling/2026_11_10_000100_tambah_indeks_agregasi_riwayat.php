<?php

use App\Support\BatasanCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi khusus profiling (B.2.1), dijalankan SETELAH pengukuran "sebelum".
 *
 *   php artisan migrate --path=database/migrations-profiling
 *
 * Indeks gabungan ini membantu query agregasi progres kelas yang menyaring
 * id_pelajar dan rentang tanggal, lalu mengelompokkan per Pelajar dan materi.
 * Jika hasil pengukuran membuktikan indeks bermanfaat, pindahkan berkas ini ke
 * database/migrations dan catat hasilnya di laporan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_latihan', function (Blueprint $table) {
            $table->index(['id_pelajar', 'tanggal_pengerjaan', 'id_materi'], 'idx_riwayat_agregasi');
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_latihan', function (Blueprint $table) {
            $table->dropIndex('idx_riwayat_agregasi');
        });
    }
};
