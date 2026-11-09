<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * SEMENTARA (Diandra): tabel milik modul materi agar modul ini bisa dijalankan sendiri.
 * Bila migrasi Natan sudah memuat materi_aksara, konten_materi, dan konten_materi_arsip,
 * hapus berkas ini dan samakan nama kolom dengan yang dipakai app/Models/MateriAksara.php
 * dan app/Models/KontenMateri.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materi_aksara', function (Blueprint $table) {
            $table->id('id_materi');
            $table->string('nama_materi', 100);
            $table->string('jenis_aksara', 10);                       // jawa | sunda
            $table->unsignedTinyInteger('tingkat_kesulitan');         // 1..3
            $table->string('status_publikasi', 10)->default('draf');  // draf | terbit | arsip
            $table->timestamps();
            $table->index(['jenis_aksara', 'tingkat_kesulitan']);
            $table->index('status_publikasi');
        });

        Schema::create('konten_materi', function (Blueprint $table) {
            $table->id('id_konten');
            $table->foreignId('id_materi')->unique()->constrained('materi_aksara', 'id_materi')->restrictOnDelete();
            $table->text('bentuk_aksara');
            $table->text('aturan');
            $table->text('contoh');
            $table->string('path_audio')->nullable();
            $table->string('versi_konten', 20)->default('1.0');
            $table->string('versi_aplikasi_minimum', 20)->default('1.0');
            $table->timestamps();
        });

        Schema::create('konten_materi_arsip', function (Blueprint $table) {
            $table->id('id_arsip');
            $table->unsignedBigInteger('id_konten');
            $table->foreignId('id_materi')->index()->constrained('materi_aksara', 'id_materi')->restrictOnDelete();
            $table->text('bentuk_aksara');
            $table->text('aturan');
            $table->text('contoh');
            $table->string('path_audio')->nullable();
            $table->string('versi_konten', 20);
            $table->string('versi_aplikasi_minimum', 20);
            $table->timestamp('diarsipkan_pada')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konten_materi_arsip');
        Schema::dropIfExists('konten_materi');
        Schema::dropIfExists('materi_aksara');
    }
};
