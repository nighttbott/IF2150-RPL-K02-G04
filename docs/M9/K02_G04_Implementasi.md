<h1>
IF2150 REKAYASA PERANGKAT LUNAK
<br>
TUGAS 9
<br>
IMPLEMENTASI PERANGKAT LUNAK
</h1>
<br>

## Ngaksara

<p align="center">
<img alt="Logo Ngaksara" src="../M8/assets/diagram/LOGO.png" width="40%">
</p>

### Untuk: Amanda Aurellia Salsabilla

Dipersiapkan oleh:
| Informasi | Keterangan |
| --- | --- |
| Kelas | K2 |
| Kelompok | 4 |

| NIM      | Nama                           |
| -------- | ------------------------------ |
| 13525026 | Ryuza Nadif Aldebaran          |
| 13525029 | Muhammad Naufal Hilmi          |
| 13525077 | Muhammad Abduh                 |
| 13525107 | Nathaniel Marvelo              |
| 13525113 | Diandra Aria Yufana            |
| 13525143 | Natan Danuarta Ariel Wicaksana |

---

# BAGIAN B: Laporan Implementasi

## B.1 Perubahan dari DPPL

Tabel B.1. Perubahan dari DPPL

| No | Bagian DPPL | Rancangan Awal | Implementasi Akhir | Alasan |
| :--- | :--- | :--- | :--- | :--- |
| 1 | Kelas C20 KelasController | Satu controller untuk seluruh operasi kelas. | Dipecah menjadi `KelasPengajarController` (UC-07), `KelasPelajarController` (UC-05), dan `PemantauanKelasController` (UC-08). Logika pengumpulan (Algo-082/086) berada di `app/Services/PengumpulanTugas.php`. | Tiap bagian dikerjakan anggota berbeda (Marvel, Abduh, Natan), sehingga tidak saling menimpa berkas yang sama dan dapat dikerjakan paralel. Route juga dipisah per modulnya. |
| 2 | Kelas C12 HalamanKelolaKelas | Satu kelas View untuk I-14 sampai I-17. | Dipecah menjadi view `kelas-pengajar/index`, `anggota`, `tugas` (I-14, I-15, I-16) dan `pemantauan/daftar-kelas`, `pemantauan/index` (I-17). | Mengikuti pemecahan C20 seperti pada nomor 1. |
| 3 | Kelas C16 LatihanController | Satu controller dengan operasi privat `hitungAkurasiGoresan`, `cocokkanBunyi`, `validasiRangkaian`, `periksaBadge`. | `LatihanController` (mode menulis) dan `LatihanNonKanvasController` (bunyi, merangkai, transliterasi). Operasi privat diwujudkan sebagai kelas yang bisa diuji tanpa HTTP: `AkurasiGoresan`, `PemeriksaBadge`, `PenilaiJawaban::bunyi/merangkai/transliterasi`, `PenyusunSoal`, `SelesaikanLatihan`. | Agar mode kanvas (Naufal) dan non-kanvas (Marvel) dapat dikerjakan paralel. Pemisahan juga memudahkan testing. |
| 4 | Algo-022 nilaiJawaban, KF23 | Algo-022 hanya membahas mode menulis, bunyi, dan merangkai. | Ditambah mode **transliterasi**: jawaban dan kunci dibandingkan setelah `trim` dan huruf kecil. | Menyesuaikan dengan KF23 pada dokumen SKPL karena sebelumnya terlewat implementasinya di DPPL. |
| 5 | Algo-102 agregasiProgres | Riwayat dibaca per anggota (2 query per anggota). | Tiga query agregasi tetap (`WHERE id_pelajar IN (...) GROUP BY`), berapa pun jumlah anggotanya. | Menghindari masalah N+1. |
| 6 | Basis data `riwayat_latihan` | Tidak ada rancangan indeks. | Ditambah indeks `idx_riwayat_agregasi (id_pelajar, tanggal_pengerjaan, id_materi)` dan `idx_riwayat_pelajar_materi (id_pelajar, id_materi, skor)`. | Hasil profiling B.2.1. |
| 7 | Basis data (seluruh tabel) | DPPL tidak memuat DDL lengkap, dan nilai enum bercampur huruf besar/kecil (`'AKTIF'`, `'TERBIT'`, `'aktif'`). | 12 tabel dibuat dari atribut Tabel 4.1 dan query Q-xxx. Semua nilai enum memakai **huruf kecil** dan dikunci dengan `CHECK` (MySQL), serta `app/Enums`. | Konsistensi data dan mencegah salah tulis. Daftar tabel ada di `src/README.md`. |
| 8 | Algo-055 hapusMateri, Q-055 | Penghapusan lunak (`deleted_at`). | Penghapusan = mengubah `status_publikasi` menjadi `arsip`; baris tidak dihapus. | Agar riwayat latihan yang merujuk materi tetap utuh, dan status lebih mudah ditampilkan di I-18. |
| 9 | UC-06, C17 MateriController | Tidak ada operasi transisi draf ke terbit. | Ditambah tombol **Terbitkan** (`POST /kelola/materi/{id}/terbitkan`) yang memakai `perbaruiMateri`. Pelajar hanya melihat materi berstatus `terbit`. | Menambahkan ini agar materi baru dari Tim Materi dapat sampai ke Pelajar. |
| 10 | Algo-052 validasiFormatFile | Unggahan berupa arsip zip berisi `konten.json`. | Unggahan satu berkas `json`, `mp3`, `png`, atau `svg` (maks. 10 MB), divalidasi di `SimpanMateriRequest` dan diperiksa ulang di `MateriController::validasiFormatFile`. | Lebih sederhana dan cukup untuk kebutuhan konten saat ini. |
| 11 | Algo-072/073 buatTugas, terbitkanTugas | Draf disimpan, lalu diterbitkan lewat aksi terpisah. | Layar I-15 hanya punya satu tombol **Terbitkan**: draf disimpan (`TugasKelas::simpan`) lalu langsung diterbitkan (`terbitkan`) dalam satu aksi. | Mengikuti desain I-15 yang telah dibuat. |
| 12 | Algo-075 kelolaAnggota | Status anggota yang dikeluarkan tidak dibedakan. | Pelajar yang dikeluarkan Pengajar berstatus `nonaktif`, sedangkan yang keluar sendiri berstatus `keluar`. Keduanya bisa bergabung lagi dengan kode (`updateOrCreate`). Riwayat tidak dihapus. | Agar kedua alasan pelajar keluar dapat dibedakan. |
| 13 | C07 HalamanUtama (I-03) | Halaman utama untuk semua peran. | Beranda lengkap untuk Pelajar. Pengajar dan Tim Materi dialihkan ke menu pertamanya (Kelola Kelas / Kelola Materi). Menu sidebar per peran diatur di `config/navigation.php`. | Menyesuaikan dengan desain view yang telah dibuat. |
| 14 | Menu "Latihan" (I-03-01) | Tidak ada halaman indeks latihan. | Route `GET /latihan` mengarahkan ke I-06 untuk materi rekomendasi teratas (Algo-101), atau ke daftar materi bila belum ada. | Sebelumnya belum menunjuk ke materi tertentu. |
| 15 | Hak akses (Algo-002/004) | Pemeriksaan peran di tiap controller. | Middleware `peran:...` (`CekPeran`) dipasang per berkas route, ditambah pemeriksaan kepemilikan kelas (`KelasBelajar::periksaPemilik`) di controller. | Membuat pemeriksaan kepemilikan lebih robust. |
| 16 | Algo-023 hitungAkurasiGoresan, Algo-021 langkah 1 sampai 5 | Templat = kontur huruf dari font; 64 titik; `akurasi = 100 × (1 − jarakRata / dMaks)` dengan jarak rata-rata dua arah. | Templat = **garis tengah huruf** (field `tengah` di `resources/aksara/template_aksara.json`, dibuat oleh `scripts/tambah_garis_tengah.py`); 200 titik; jarak dalam setengah ketebalan huruf dianggap nol (toleransi dari kontur); 25% titik terjauh dari goresan Pelajar diabaikan (coretan nyasar), tetapi setiap bagian templat tetap wajib tertulis. Parameter di `config/latihan.php`: `d_maks` 0,05, `buang_outlier` 0,25, `faktor_toleransi` 0,5, ambang 70. | Setelah dilakukan uji coba perhitungan kelulusan yang sebelumnya terlalu ketat sehingga latihan terlalu sulit untuk diselesaikan |
| 17 | Tampilan (Bab 6) | Sesuai Figma. | Ditambah efek visual halus: kartu muncul bertahap, bilah progres terisi perlahan, skor naik bertahap dan taburan motif kawung saat latihan berhasil, toast badge baru, dan tanda centang saat tugas dikumpulkan (`resources/js/efek.js`). Semua efek nonaktif bila pengguna memilih *reduce motion*. Menu mobile memakai *offcanvas*. | Menambah estetika dan efek umpan balik |
| 18 | Algo-020 rekamGoresan, C08 | Titik goresan `x = offsetX / lebarKanvas`, `y = offsetY / tinggiKanvas`. | Kedua sumbu dibagi **tinggi** kanvas (`resources/js/latihan/kanvas-menulis.js`). | Kanvas I-07 tidak persegi; pembagi yang berbeda membuat bentuk goresan melar dan skor turun tanpa alasan. |
| 19 | Algo-021 siapkanSoal, C16 → C02 | Target diambil lewat `MateriController.ambilKontenLatihan`; satu butir dipilih acak; materi tanpa soal ditolak 404. | Target diambil langsung dari `MateriAksara::ambilTargetLatihan`, hanya untuk materi berstatus `terbit`. Pada mode menulis, butir tanpa templat dilewati. Soal dipilih acak dari butir yang belum diselesaikan di sesi. Materi tanpa soal kembali ke I-06 dengan pesan "Materi ini belum memiliki soal untuk mode yang dipilih". | Controller tidak dianjurkan memanggil controller lain; materi draf/arsip tidak boleh dilatih; butir tanpa templat tidak bisa dinilai Algo-023; tombol "Lanjut" tidak mengulang soal; DPPL 6.6 meminta pesan tampil tanpa berpindah layar. |
| 20 | I-07-01 bilah kemajuan | Kemajuan per materi. | Kemajuan per sesi latihan (session `latihan.sesi`). | Tidak membutuhkan tabel baru. |
| 21 | Algo-027 periksaBadge, C22 | Kode badge `PEMULA_AKSARA`, `RAJIN_7_HARI`, `SERATUS_POIN`. | Kode `pemula`, `konsisten_7`, `seratus_poin` sesuai `MotivasiBelajar::BADGE`; syarat tetap Algo-027. | Mengikuti konvensi nilai huruf kecil (no. 7), dan halaman progres (I-11) membaca daftar kode yang sama. Kesamaan kedua daftar dijaga `PemeriksaBadgeTest`. |
| 22 | Q-008 simpanHasil, C03 | Tanpa `id_tugas` dan `id_komponen`. | Hasil `berhasil` dari latihan yang dibuka lewat tugas kelas menyimpan `id_tugas`, `id_komponen`, dan `waktu_selesai`; percobaan `perlu_diulang` tidak menyimpan keduanya. | Bukti penyelesaian komponen tugas (Q-015) yang dibaca `PengumpulanTugas`. |
| 23 | C16 atribut `konteks` | Soal aktif dan hasil penilaian terakhir disimpan di session. | Disimpan sebagai array (`toArray`/`fromArray` pada tipe data di `app/Latihan/Data`). | `config/session.php` memakai serialisasi JSON, sehingga objek kembali sebagai array pada request berikutnya. |
| 24 | I-07, I-10 (Bab 6) | Figma hanya menggambar bilah bawah I-07 dalam keadaan "Belum tepat". | Ditambah keadaan netral dan "Bagus!" pada bilah bawah I-07, keadaan galat 409 dengan tombol "Muat ulang", serta keterangan "Soal ini sudah pernah kamu selesaikan." di bawah kartu +0 poin pada I-10. | Keadaan ini dibutuhkan alur DPPL tetapi belum digambar; gayanya diturunkan dari komponen yang ada di Figma. |

## B.2 Bukti Penerapan Best Practices

Tabel B.2. Bukti Best Practices

| Praktik | Bukti | Keterangan |
| :--- | :--- | :--- |
| Branching | [Daftar branch](https://github.com/nighttbott/IF2150-RPL-K02-G04/branches), [graf commit](https://github.com/nighttbott/IF2150-RPL-K02-G04/network) | Skema Gitflow sederhana: `feat/<fitur>` → PR → `develop` → `main`. Contoh: `feat/auth`, `feat/materi`, `feat/latihan-menulis`, `feat/kelola-kelas-pengajar`, `feat/kelas-pelajar`, `fondasi-database`, `progres-pemantauan`. |
| Semantic commit | [Riwayat commit `develop`](https://github.com/nighttbott/IF2150-RPL-K02-G04/commits/develop) | Format `<tipe>(<scope>): <deskripsi>`, contoh `feat(latihan): simpan, hasil, streak, poin, dan badge (Algo-026)`, `fix(auth): change persetujuan_tnc -> setuju_tnc`, `test(kelas): uji akses pengumpulan dan halaman Pelajar`. |
| Track tasks | [Issues](https://github.com/nighttbott/IF2150-RPL-K02-G04/issues?q=is%3Aissue) | 12 issue fitur per modul dengan penanggung jawab, misalnya #7 modul Materi (Diandra), #9 dan #11 kelas Pelajar (Abduh), #14 sampai #16 fondasi DB, progres, pemantauan (Natan), #26 dan #28 sampai #32 latihan (Naufal). |
| Pull request dan review | [Daftar PR](https://github.com/nighttbott/IF2150-RPL-K02-G04/pulls?q=is%3Apr), contoh [#13](https://github.com/nighttbott/IF2150-RPL-K02-G04/pull/13), [#24](https://github.com/nighttbott/IF2150-RPL-K02-G04/pull/24), [#38](https://github.com/nighttbott/IF2150-RPL-K02-G04/pull/38) | 22 PR digabung ke `develop`. Contoh review: Abduh dan Ryuza menyetujui #13 (Natan); Natan menyetujui #23 dan #24 (Marvel), #25, #27, #33 (Naufal); Marvel menyetujui #21, #35, #37 (Ryuza); Diandra menyetujui #12 (Abduh) dan #17 (Natan); Abduh menyetujui #34 (Naufal); Marvel, Natan, dan Diandra menyetujui #38 (Ryuza). |
| Pengujian | [`src/tests/`](../../src/tests) | **378 test, 378 lolos** (`php artisan test`, SQLite di memori). Mencakup akses per peran, pendaftaran dan login, penilaian goresan dan non-kanvas, poin/streak/badge tanpa ganda, pembatalan transaksi, ketepatan pengumpulan (tepat di tenggat = tepat waktu), kepemilikan kelas, materi dan unggahan, progres dan pemantauan, serta layout per peran. |

### B.2.1 Profiling dan Optimasi

| Informasi | Keterangan |
| :--- | :--- |
| Fitur / proses yang diprofil | Agregasi progres kelas pada I-17 Pantau Progres Anggota (Algo-102 `agregasiProgres`) dan penguasaan per materi (Algo-100, Q-010 `hitungPenguasaan`). |
| Alat | Perintah `php artisan ngaksara:profil-progres --ulang=10 --periode=semua` (jumlah query lewat *query log* Laravel, waktu lewat `hrtime`, median dari 10 putaran), `EXPLAIN` MySQL 8.4 (Docker), dataset `ProfilingSeeder`: 1 kelas, **100 Pelajar aktif, 10.000 baris riwayat**. |
| Temuan (sebelum) | Versi DPPL (riwayat dibaca per anggota): **200 query, median 1.111,27 ms**. Versi agregasi `GROUP BY` tanpa indeks khusus: **3 query, median 42,39 ms** (periode 30 hari: 42,91 ms). `EXPLAIN` memakai indeks FK `riwayat_latihan_id_pelajar_foreign` (`type=index`, 10.122 baris, *filtered* 10,98%). Q-010 per Pelajar: `type=ref`, 97 baris, **`Using temporary`**, median 1,88 ms. |
| Perbaikan | (1) Algo-102 diganti dari pembacaan per anggota (N+1) menjadi 3 query agregasi. (2) Indeks `idx_riwayat_agregasi (id_pelajar, tanggal_pengerjaan, id_materi)`. (3) Indeks *covering* `idx_riwayat_pelajar_materi (id_pelajar, id_materi, skor)` untuk Q-010. Migrasi: `src/database/migrations/2026_11_13_000001_tambah_indeks_riwayat_latihan.php`. |
| Hasil (sesudah) | Agregasi: **3 query, median 27,25 ms** (periode 30 hari: 26,37 ms), sekitar **36% lebih cepat** dibanding tanpa indeks dan sekitar **40 kali lebih cepat** dibanding versi DPPL (200 query, 1.081,71 ms). `EXPLAIN` memakai `idx_riwayat_agregasi` dengan *filtered* 97,6%. Q-010: `EXPLAIN` berubah menjadi **`Using index`** (tanpa tabel sementara), median 1,78 ms. Penghematan Q-010 kecil karena tiap Pelajar hanya ±100 baris, tetapi indeks ini mencegah tabel sementara saat riwayat bertambah. Kedua versi Algo-102 menghasilkan angka yang **sama** (diperiksa otomatis oleh perintah profiling). |

Catatan: `EXPLAIN` agregasi tetap `type=index` karena kelas uji berisi seluruh 100 Pelajar, sehingga daftar `IN (...)` mencakup hampir seluruh tabel. Pada kelas yang lebih kecil, indeks gabungan menyaring baris lebih awal. Angka di atas diukur pada laptop pengembang (Windows 11, PHP 8.4, MySQL 8.4 di Docker), sehingga nilai absolutnya dapat berbeda di mesin lain.

## B.3 Kendala dan Catatan Penting

### Kendala selama implementasi

| Kendala | Dampak saat integrasi | Penyelesaian dan bukti |
| :--- | :--- | :--- |
| Rute dan tampilan dari beberapa modul bertabrakan. | Rute pengelolaan materi mengambil alih Beranda. Pilihan latihan non-kanvas dan halaman hasilnya juga mengarah ke alur yang keliru. | Tim memulihkan awalan `kelola/materi`, mengarahkan pilihan mode ke rute masing-masing, dan memisahkan tampilan hasil latihan menulis dari hasil non-kanvas. [Perbaikan integrasi](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/b1448a545b50a29bd30474e0c6b20b05753955d4) · [Uji pilihan mode](../../src/tests/Feature/Latihan/PilihModeTest.php) |
| Modul kelas dan latihan memakai nama data serta rute yang berbeda. | Saat Pengajar menerbitkan tugas, daftar komponen dan ID tugas tidak terbaca sesuai kontrak. Dari sisi Pelajar, tombol **Kerjakan** dan **Kembali ke tugas** dapat mengarah ke tujuan yang keliru. | Tim menyelaraskan kunci `komponen_tugas` dan `id_tugas` serta memperbaiki tujuan kedua tombol. [Perbaikan alur tugas](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/b1448a545b50a29bd30474e0c6b20b05753955d4) · [Uji kelas Pengajar](../../src/tests/Feature/KelasPengajarTest.php) · [Uji konteks tugas](../../src/tests/Feature/Latihan/KonteksTugasTest.php) |
| Penilaian awal membandingkan goresan Pelajar dengan tepi huruf. | Pelajar yang mengikuti bagian tengah huruf tebal dapat memperoleh skor rendah meskipun bentuk tulisannya sesuai. | Penilaian menggunakan garis tengah dan toleransi ketebalan huruf. Hasil kalibrasi tercantum di B.1 butir 17. [Perubahan algoritma](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/d980f35199b56af0e303e40d372b0778e58ca597) |

### Masalah yang masih terbuka

| Fitur | Cara memeriksa | Perilaku dan dampak |
| :--- | :--- | :--- |
| Latihan bunyi (I-08) | Buka **Mencocokkan bunyi** pada materi dengan beberapa soal dan satu MP3. Bandingkan dua tombol **Putar suara**. Ulangi pada materi tanpa MP3. | Semua tombol memakai audio yang sama untuk satu materi. Jika MP3 tidak tersedia, tombol dinonaktifkan. Bacaan Latin tampil pada kedua kondisi sehingga Pelajar dapat menjawab tanpa membedakan bunyi. [Tampilan soal bunyi](../../src/resources/views/latihan/bunyi.blade.php) |
| Gambar pendukung materi (I-19 dan I-05) | Sebagai Tim Materi, unggah materi dengan PNG atau SVG lalu terbitkan. Masuk sebagai Pelajar dan buka detail materi tersebut. | Berkas gambar tersimpan, tetapi halaman detail tidak menampilkan gambar unggahan. Pelajar tidak memperoleh ilustrasi yang disertakan Tim Materi. [Penyimpanan berkas](../../src/app/Http/Controllers/MateriController.php) · [Tampilan detail](../../src/resources/views/materi/show.blade.php) |
| Umpan balik Pengajar (I-17) | Sebagai pemilik kelas, kirim pesan kepada anggota aktif dari **Pantau Progres**. Masuk sebagai Pelajar penerima dan periksa menu **Feedback**. | Pengajar memperoleh konfirmasi dan pesan tercatat di `data_feedback` dengan `id_akun` penerima. Menu Feedback Pelajar hanya menyediakan formulir pengiriman, sehingga penerima belum dapat membaca pesan melalui aplikasi. [Penyimpanan pesan](../../src/app/Http/Controllers/PemantauanKelasController.php) · [Formulir Feedback](../../src/app/Http/Controllers/FeedbackController.php) |
| Menulis suku kata gabungan (I-06 dan I-07) | Pilih materi Jawa tingkat 2 **Sandhangan swara**, lalu pilih **Menulis**. | Butir seperti ꦲꦶ (*hi*) tidak memiliki templat untuk bentuk gabungannya. Pelajar kembali ke pilihan mode dengan pesan “Materi ini belum memiliki soal untuk mode yang dipilih”. [Data materi](../../src/database/seeders/MateriSeeder.php) · [Kasus uji khusus](../../src/tests/Feature/Latihan/PilihModeTest.php) |
| Penilaian aksara yang mirip (I-07) | Pada soal Jawa *ha*, coba tulis *ka* dan amati skornya. Hasil bergantung pada bentuk goresan. | Bentuk yang mirip masih dapat mencapai ambang lulus 70. Tim mencatat 24% sampel aksara pembanding lulus dalam simulasi internal. Angka ini menggambarkan sampel simulasi, sehingga frekuensi kesalahan pada tulisan Pelajar sungguhan masih belum diketahui. [Parameter penilaian](../../src/config/latihan.php) |

### Catatan untuk asisten

1. **Menjalankan aplikasi.** Dari akar repositori, jalankan `docker compose up -d` untuk MySQL. Setelah itu masuk ke `src/` untuk instalasi dependensi, konfigurasi `.env`, migrasi beserta data contoh, pembangunan aset Vite, dan `php artisan serve`. Urutan perintah dan akun untuk ketiga peran tersedia di [panduan instalasi dan akun uji](../../src/README.md). Jika manifest Vite belum tersedia, jalankan `npm run build` dari `src/` setelah dependensi npm terpasang.
2. **Memeriksa alur lintas peran.** Gunakan akun Pengajar Bu Rina untuk menerbitkan tugas berkomponen latihan di kelas contoh `K7M2QX`. Beralih ke akun Pelajar Sari, buka tugas dari kelas, kerjakan komponennya, lalu kembali ke tugas. Alur ini memeriksa sambungan antara penerbitan tugas, latihan, dan pencatatan penyelesaian komponen.
3. **Membaca hasil pengujian.** Dari `src/`, jalankan `php artisan test` untuk pengujian otomatis dengan SQLite di memori. Pengukuran performa pada B.2.1 memakai MySQL 8.4 dan data profiling tersendiri. Pemeriksaan visual akhir terhadap Figma mencakup sidebar serta halaman daftar dan detail tugas Pelajar (I-12 dan I-13). Kesesuaian visual layar lain belum diperiksa ulang pada tahap integrasi akhir.

## B.4 Kontribusi Anggota

Tabel B.4. Kontribusi Anggota

| NIM | Nama | Modul / Fitur yang Dikerjakan | Persentase Kontribusi |
| :--- | :--- | :--- | :--- |
| 13525026 | Ryuza Nadif Aldebaran | Menyiapkan proyek Laravel dan konfigurasi Docker, membangun layout dan navigasi per peran, serta mengimplementasikan akun, autentikasi (UC-01), Beranda (I-03), dan Feedback (UC-09). [Uji akses per peran](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/ad518af772d48ea96b68b6d7432f8575bdfa3636) · [Beranda](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/363b40ca242ce91c2c5bf0155c10e2eb51f7d82d) · [Feedback](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/19cc0dc3114ea06f2ca95b096eb580e99158f9c7) | 18% |
| 13525029 | Muhammad Naufal Hilmi | Mengimplementasikan latihan menulis (UC-03), dari pilihan mode dan kanvas hingga penilaian goresan, hasil, poin, streak, dan badge. Menghubungkan hasil latihan dengan komponen tugas kelas serta melengkapi dokumen M9. [Penilaian goresan awal](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/d85050a42cf892c002e7f5917921ec34c77ec9b6) · [Konteks tugas](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/4d939ba7f179b7ae4925bd590f2a63834e62447f) · [Dokumen M9](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/ef1d5b47e0fb1adb6fee7182d10083f9c75fe74f) | 16% |
| 13525077 | Muhammad Abduh | Mengimplementasikan keanggotaan dan tugas dari sisi Pelajar (UC-05), termasuk bergabung dengan kode, membuka daftar dan detail tugas, memeriksa komponen wajib, serta mencatat pengumpulan dan ketepatan waktu dalam satu transaksi. [Pengumpulan tugas](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/c91b8ae447959d58a04461b364e1666016fc311d) · [Uji akses dan halaman Pelajar](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/7b427b8791ba10b71e8d82273fe5f5f292bd832c) | 15% |
| 13525107 | Nathaniel Marvelo | Mengimplementasikan pengelolaan kelas Pengajar (UC-07), termasuk kode bergabung, anggota, papan skor, dan penerbitan tugas. Pada UC-03, mengerjakan mode bunyi, merangkai aksara, dan transliterasi. [Pengelolaan kelas](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/57d18355926d84f82c304fe10e772a7375cc8eb3) · [Tiga mode non-kanvas](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/bae9de5bc2521109f4a1cce55e53e5e27b910cee) | 16% |
| 13525113 | Diandra Aria Yufana | Mengimplementasikan daftar dan detail materi Pelajar (UC-02), pengelolaan materi oleh Tim Materi (UC-06), unggahan dan versi konten, templat aksara Jawa dan Sunda, serta data awal materi. Melengkapi formulir asistensi M9. [Modul materi dan empat layar](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/36dbac32cbf543ad121ef6100b1d6f73adf8b3a7) · [Templat aksara](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/203816bbe23f32df40b24601f61a3daf59ae9ca8) · [Formulir asistensi](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/139b9fe995d8eb52ce75f1629614bcb06fbc3e35) | 16% |
| 13525143 | Natan Danuarta Ariel Wicaksana | Menyiapkan migrasi dan data uji, model riwayat dan motivasi, progres Pelajar (UC-04), serta pemantauan kelas (UC-08). Menangani profiling dan indeks, integrasi lintas modul, kalibrasi akhir penilaian goresan, laporan M9, dan logbook. [Profiling](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/02ed23a14723107b63083be6557cf43acb56f14f) · [Integrasi](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/b1448a545b50a29bd30474e0c6b20b05753955d4) · [Kalibrasi](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/d980f35199b56af0e303e40d372b0778e58ca597) · [Laporan M9](https://github.com/nighttbott/IF2150-RPL-K02-G04/commit/afe8a5387db09dbe799265dac2d5743a25f24fee) | 19% |
| | **Total** | | **100%** |

## B.5 Traceability Kelas Perancangan ke Implementasi

Semua lokasi relatif terhadap `src/`.

Tabel B.5. Traceability Kelas Perancangan ke Implementasi

| ID Kelas | Nama Kelas Perancangan | Bentuk Implementasi | Lokasi (File dan Nama) | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| C01 | AkunPengguna | Kelas Model + Tabel | `app/Models/AkunPengguna.php` - `AkunPengguna`; tabel `akun_pengguna` | Atribut menjadi kolom. `buatAkunBaru` (Algo-001), `verifikasiHakAkses` (Algo-002), `cariBerdasarkanEmail`, `cocokkanPassword`, `getProfil`, `getDetailAkun` menjadi method. |
| C02 | MateriAksara | Kelas Model + Tabel | `app/Models/MateriAksara.php` - `MateriAksara`; tabel `materi_aksara` | `ambilDaftarMateri` (Algo-040), `ambilMateriRekomendasi` (Algo-041), `ambilTargetLatihan`, `simpan`, `perbaruiData`. Status `draf`/`terbit`/`arsip` di `app/Enums/StatusPublikasi.php`. |
| C03 | RiwayatLatihan | Kelas Model + Tabel | `app/Models/RiwayatLatihan.php` - `RiwayatLatihan`; tabel `riwayat_latihan` | `simpanHasil` (Q-008), `catatAktivitas` (Q-016), `ambilRiwayat` (Q-011), `ambilBuktiPenyelesaian` (Q-015), `hitungPenguasaan` (Q-010), `hitungPolaKesalahan` (Q-013), `cekPernahSelesai` (Q-009), dan lainnya sebagai method statis. |
| C04 | DataFeedback | Kelas Model + Tabel | `app/Models/DataFeedback.php` - `DataFeedback`; tabel `data_feedback` | `simpan` (Algo-003), `perbaruiStatus` (Algo-004). |
| C05 | HalamanPendaftaran | Komponen View (Blade) + FormRequest | `resources/views/auth/daftar.blade.php`; `app/Http/Requests/DaftarRequest.php` | `tampilkanOpsiPeran`, `tampilkanTnC` menjadi bagian form; `kirimPendaftaran` = `POST /daftar`. |
| C06 | HalamanLogin | Komponen View (Blade) | `resources/views/auth/masuk.blade.php`; `resources/views/layouts/tamu.blade.php` | `kirimKredensial` = `POST /masuk`; `tampilkanStatusLogin` = pesan galat sesi. |
| C07 | HalamanUtama | Komponen View + Controller + Konfigurasi | `resources/views/beranda.blade.php`; `resources/views/layouts/app.blade.php`, `layouts/sidebar.blade.php`; `config/navigation.php`; `app/Http/Controllers/BerandaController.php` | `tampilkanMenu` = sidebar per peran; `pilihMenu` = tautan menu. Pengalihan per peran di `BerandaController::tampilkan`. |
| C08 | HalamanLatihan | Komponen View + JavaScript | `resources/views/latihan/pilih-mode`, `menulis`, `bunyi`, `merangkai`, `transliterasi`, `hasil`, `hasil-soal`; `resources/js/latihan/kanvas-menulis.js`; `resources/js/efek.js` | `rekamGoresan` (Algo-020) memakai Pointer Events di kanvas; `putarAudio`, `kirimJawaban`, `tampilkanHasil`, `ulangiLatihan` menjadi handler dan tombol. |
| C09 | HalamanKelolaMateri | Komponen View (Blade) | `resources/views/materi/kelola.blade.php`, `materi/form.blade.php` | `unggahFileMateri` (Algo-045), `suntingMateri` (Algo-046), `tampilkanStatus`, `tampilkanDaftarMateri`. |
| C10 | HalamanFeedback | Komponen View (Blade) | `resources/views/feedback/index.blade.php` | `tampilkanFormFeedback`, `kirimFeedback` (Algo-008), `tampilkanKonfirmasi`. |
| C11 | HalamanKelasPelajar | Komponen View (Blade) | `resources/views/kelas-pelajar/index.blade.php` (I-12), `kelas-pelajar/detail.blade.php` (I-13) | `kirimKodeBergabung`, `bukaKelas`, `bukaTugas`, `kerjakanTugas`, `periksaPenyelesaian`, `finalisasiTugas` menjadi form dan tombol. |
| C12 | HalamanKelolaKelas | Komponen View (Blade), dipecah | `resources/views/kelas-pengajar/index`, `anggota`, `tugas` (I-14 sampai I-16); `resources/views/pemantauan/daftar-kelas`, `pemantauan/index` (I-17) | Dipecah (lihat B.1 no. 2). `kirimUmpanBalik` ada di I-17. |
| C13 | HalamanMateri | Komponen View (Blade) | `resources/views/materi/index.blade.php` (I-04), `materi/show.blade.php` (I-05) | `pilihMateri`, `tampilkanIsiMateri` (Algo-047), `putarAudioPengucapan` (Algo-048). |
| C14 | HalamanProgres | Komponen View (Blade) | `resources/views/progres/index.blade.php`, `progres/label-status.blade.php` | `pilihPeriode`, `tampilkanRekomendasi`, `tampilkanScoreboard`, `tampilkanKeadaanKosong`, dan operasi tampilan lain menjadi bagian halaman. |
| C15 | OtentikasiController | Kelas Controller + FormRequest + Middleware | `app/Http/Controllers/OtentikasiController.php`; `app/Http/Requests/DaftarRequest.php`; `app/Http/Middleware/CekPeran.php`; `routes/auth.php` | `verifikasiFormatData` (Algo-009) diwujudkan sebagai aturan validasi `DaftarRequest`; `hashPassword` (Algo-010) bcrypt; `simpanAkun` (Algo-011) dalam transaksi; `validasiLogin` (Algo-012) dengan `RateLimiter` (atribut `batasPercobaanLogin`). |
| C16 | LatihanController | Kelas Controller (dipecah) + kelas domain | `app/Http/Controllers/LatihanController.php`, `LatihanNonKanvasController.php`; `app/Latihan/AkurasiGoresan.php`, `PemeriksaBadge.php`; `app/Services/Latihan/PenilaiJawaban.php`, `PenyusunSoal.php`, `SelesaikanLatihan.php`; `routes/latihan.php` | `siapkanSoal` (Algo-021), `nilaiJawaban` (Algo-022), `hitungAkurasiGoresan` (Algo-023) = `AkurasiGoresan` (garis tengah + toleransi ketebalan, lihat B.1 no. 17), `cocokkanBunyi` (Algo-024) dan `validasiRangkaian` (Algo-025) = `PenilaiJawaban`, `simpanHasil` (Algo-026), `periksaBadge` (Algo-027). Atribut `ambangAkurasi` di `config/latihan.php`. |
| C17 | MateriController | Kelas Controller + FormRequest + kelas pendukung | `app/Http/Controllers/MateriController.php`; `app/Http/Requests/SimpanMateriRequest.php`; `app/Support/Materi/*`; `routes/materi.php` | `ambilDaftarMateri` (Algo-049), `muatPaketMateri` (Algo-050), `ambilAudioPengucapan` (Algo-051), `validasiFormatFile` (Algo-052), `tambahMateri` (Algo-053), `perbaruiMateri` (Algo-054), `hapusMateri` (Algo-055). |
| C18 | FeedbackController | Kelas Controller | `app/Http/Controllers/FeedbackController.php`; `routes/feedback.php` | `validasiFeedback` (Algo-013), `simpanFeedback` (Algo-014), `kirimKonfirmasi`. |
| C19 | ProgresController | Kelas Controller + Perintah Artisan | `app/Http/Controllers/ProgresController.php`; `app/Console/Commands/ProfilProgresKelas.php`; `routes/progres.php` | `hitungProgres` (Algo-100), `siapkanRekomendasi` (Algo-101), `agregasiProgres` (Algo-102, versi 3 query), `ambilScoreboard`, `ambilRiwayat`, `hitungPolaKesalahan`, `ambilDetailHasil`, `ambilIndikatorMotivasi`. |
| C20 | KelasController | Kelas Controller (dipecah tiga) + Service | `app/Http/Controllers/KelasPengajarController.php`, `KelasPelajarController.php`, `PemantauanKelasController.php`; `app/Services/PengumpulanTugas.php`; `routes/kelas-pengajar.php`, `kelas-pelajar.php`, `progres.php` | Pengajar: `buatKelas` (Algo-071) sampai `ambilKelasPengajar` (Algo-078). Pelajar: `validasiKodeBergabung` (Algo-082), `gabungkanPelajar` (Algo-083), `ambilTugasAktif` (Algo-084), `bukaTugas` (Algo-085), `simpanPengumpulan` (Algo-086). Pemantauan: `ambilProgresAnggota` (Algo-081), `kirimUmpanBalik` (Algo-080). |
| C21 | KontenMateri | Kelas Model + Tabel | `app/Models/KontenMateri.php` - `KontenMateri`; tabel `konten_materi`, `konten_materi_arsip` | `ambilKonten` (Algo-056, `version_compare`), `simpanKonten` (Algo-057), `perbaruiKonten` (Algo-058), `arsipkanVersi` (Algo-059), `ambilAudio`. |
| C22 | MotivasiBelajar | Kelas Model + Tabel | `app/Models/MotivasiBelajar.php` - `MotivasiBelajar`; tabel `motivasi_belajar` | `perbaruiStreak` (Algo-028), `tambahPoin` (Algo-029, `lockForUpdate`), `berikanBadge` (Algo-030), `susunScoreboard` (Algo-031), `ambilMotivasi` (Algo-032), `ambilPoinKelas` (Algo-033). |
| C23 | KelasBelajar | Kelas Model + Tabel | `app/Models/KelasBelajar.php` - `KelasBelajar`; tabel `kelas_belajar` | `simpan` (Algo-087), `buatKodeBergabung` (Algo-088, `random_int`, tanpa O/0/I/1), `gantiKodeBergabung` (Algo-089), `periksaPemilik` (Algo-090), `aturScoreboard` (Algo-091), `daftarKelasPengajar` (Algo-092). |
| C24 | KeanggotaanKelas | Kelas Model + Tabel | `app/Models/KeanggotaanKelas.php` - `KeanggotaanKelas`; tabel `keanggotaan_kelas` | `simpanKeanggotaan` (Algo-109, termasuk mengaktifkan kembali), `periksaAkses` (Algo-110), `ubahStatus` (Algo-111), `daftarAnggotaAktif` (Algo-112). |
| C25 | TugasKelas | Kelas Model + Tabel | `app/Models/TugasKelas.php` - `TugasKelas`, `app/Models/KomponenTugas.php`; tabel `tugas_kelas`, `komponen_tugas` | `simpan` (Algo-113), `terbitkan` (Algo-114), `ambilTugasAktif` (Algo-117), `ambilDetail`. `verifikasiDanSimpan` (Algo-115) dan `periksaKomponenWajib` (Algo-116) diwujudkan di `PengumpulanTugas::progres` dan `simpanPengumpulan` (memeriksa semua komponen wajib sebelum menyimpan). |
| C26 | PenyelesaianTugas | Kelas Model + Service + Tabel | `app/Models/PenyelesaianTugas.php` - `PenyelesaianTugas`; `app/Services/PengumpulanTugas.php`; tabel `penyelesaian_tugas` | `simpanPengumpulan` (Algo-118) di `PengumpulanTugas`, dengan `simpanAtomik` (Algo-120) berupa `DB::transaction` dan penguncian baris; `PenyelesaianTugas::tentukanStatusKetepatan` (Algo-119): waktu komponen wajib terakhir ≤ tenggat = `tepat_waktu`; `PenyelesaianTugas::ambilStatus` (Algo-121). `UNIQUE (id_tugas, id_pelajar)`. |
