<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Indeks hasil profiling B.2.1: agregasi progres kelas (Algo-102) dan penguasaan per materi (Q-010, Algo-100).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_latihan', function (Blueprint $table) {
            $table->index(['id_pelajar', 'tanggal_pengerjaan', 'id_materi'], 'idx_riwayat_agregasi');
            $table->index(['id_pelajar', 'id_materi', 'skor'], 'idx_riwayat_pelajar_materi');
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_latihan', function (Blueprint $table) {
            $table->dropIndex('idx_riwayat_agregasi');
            $table->dropIndex('idx_riwayat_pelajar_materi');
        });
    }
};
