<h1>
IF2150 REKAYASA PERANGKAT LUNAK
<br>
TUGAS 10
<br>
HASIL IMPLEMENTASI DAN UJI PERANGKAT LUNAK (HIUPL)
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

## Daftar Isi

- [BAB 1: Pendahuluan](#bab-1-pendahuluan)
  - [1.1 Tujuan Penulisan Dokumen](#11-tujuan-penulisan-dokumen)
  - [1.2 Lingkup Masalah](#12-lingkup-masalah)
  - [1.3 Definisi, Istilah, dan Singkatan](#13-definisi-istilah-dan-singkatan)
  - [1.4 Aturan Penomoran](#14-aturan-penomoran)
  - [1.5 Referensi](#15-referensi)
  - [1.6 Deskripsi Umum Dokumen (Ikhtisar)](#16-deskripsi-umum-dokumen-ikhtisar)
- [BAB 2: Deskripsi Umum Perangkat Lunak](#bab-2-deskripsi-umum-perangkat-lunak)
- [BAB 3: Implementasi](#bab-3-implementasi)
  - [3.1 Lingkungan Implementasi](#31-lingkungan-implementasi)
  - [3.2 Implementasi Modul](#32-implementasi-modul)
  - [3.3 Implementasi Antarmuka](#33-implementasi-antarmuka)
  - [3.4 File Lain](#34-file-lain)
- [BAB 4: Hasil Pengujian](#bab-4-hasil-pengujian)
  - [4.1 Strategi dan Kedalaman Pengujian Kelas](#41-strategi-dan-kedalaman-pengujian-kelas)
  - [4.2 Pengujian Unit Kelas](#42-pengujian-unit-kelas)
  - [4.3 Pengujian Integrasi](#43-pengujian-integrasi)
  - [4.4 Pengujian Use Case](#44-pengujian-use-case)
  - [4.5 Pengujian Kebutuhan Non-Fungsional (KNF)](#45-pengujian-kebutuhan-non-fungsional-knf)
  - [4.6 Tangkapan Layar dan Bukti Hasil Pengujian](#46-tangkapan-layar-dan-bukti-hasil-pengujian)
- [BAB 5: Traceability](#bab-5-traceability)

---

## Daftar Perubahan

| Revisi | Deskripsi |
| :--- | :--- |
| *A* | *Deskripsikan perubahan yang dilakukan dari dokumen sebelumnya pada dokumen ini. Jika tidak terdapat perubahan, harap kosongkan tabel.* |
| *B* |  |
| *C* |  |
| ... |  |

---

# BAB 1: Pendahuluan

## 1.1 Tujuan Penulisan Dokumen
Dokumen Hasil Implementasi dan Uji Perangkat Lunak (HIUPL) ini mencatat hasil implementasi Ngaksara terhadap rancangan pada DPPL dan seluruh hasil pengujiannya. Isinya mencakup lingkungan implementasi, daftar modul dan antarmuka yang sudah dibuat, hasil pengujian unit, integrasi, use case, dan kebutuhan non-fungsional, serta matriks kerunutan dari KF dan KNF ke test case. Dokumen ditujukan untuk asisten dan dosen mata kuliah IF2150 sebagai bahan penilaian, serta untuk anggota Kelompok 4 K2 sebagai acuan perbaikan dan pemeliharaan.

## 1.2 Lingkup Masalah
Ngaksara adalah perangkat lunak berbasis web untuk membantu pengguna mengenal kembali aksara Jawa dan aksara Sunda. Pelajar dapat mempelajari materi, mengerjakan latihan menggambar, mencocokkan bunyi, merangkai aksara, dan transliterasi, serta meninjau progres dan motivasi belajar. Pengajar dapat membuat kelas, menerbitkan tugas, dan memantau progres anggota kelas. Tim Materi mengelola materi pembelajaran. Pelajar dan Pengajar juga dapat menyampaikan feedback kepada sistem.

## 1.3 Definisi, Istilah, dan Singkatan

Tabel 1.3. Definisi Istilah dan Singkatan

| Singkatan, Akronim, atau Istilah | Penjelasan |
| :--- | :--- |
| P/L | Perangkat Lunak, yaitu aplikasi yang memberikan perintah kepada komputer untuk menjalankan tugas tertentu. |
| SKPL | Spesifikasi Kebutuhan Perangkat Lunak, yaitu dokumen yang merangkum kebutuhan yang harus dipenuhi P/L. |
| APL | Arsitektur Perangkat Lunak, yaitu dokumen yang menetapkan pola arsitektur, komponen, dan model arsitektur P/L. |
| DPPL | Deskripsi Perancangan Perangkat Lunak, yaitu dokumen ini, yang menjelaskan rancangan P/L berdasarkan SKPL dan APL. |
| R | Kebutuhan hasil tahap *Requirement Gathering* yang menjadi sumber KF dan KNF. |
| KF | Kebutuhan Fungsional, yaitu perilaku sistem yang dapat diamati oleh pengguna. |
| KNF | Kebutuhan Non-Fungsional, yaitu batasan terhadap perilaku sistem atau cara sistem memberikannya, seperti waktu tanggap dan keamanan. |
| UC | Use Case, yaitu rangkaian interaksi antara aktor dan sistem untuk mencapai suatu tujuan. |
| Skenario | Urutan aksi aktor dan reaksi sistem pada suatu use case. Skenario normal adalah alur utama, sedangkan skenario alternatif adalah alur yang menyimpang dari alur utama. |
| Client-server | Arsitektur ketika pengguna mengakses P/L melalui browser (*client*), sedangkan pengolahan dan penyimpanan data dilakukan oleh server. |
| MVC | *Model-View-Controller*, yaitu pola arsitektur yang membagi aplikasi menjadi Model (data dan aturan aplikasi), View (tampilan), dan Controller (pengatur permintaan dan alur). |
| Logical View | Model arsitektur yang menunjukkan pembagian tanggung jawab dan hubungan antarkomponen. |
| Boundary, Controller, Entity | Tiga jenis kelas analisis, yaitu kelas antarmuka dengan aktor, kelas pengatur alur logika, dan kelas penyimpan data. |
| Kelas analisis | Kelas hasil tahap analisis pada SKPL (C01 sampai C26). |
| Kelas perancangan | Kelas pada tahap perancangan, ditulis lengkap dengan atribut, operasi, tipe data, dan visibilitas. |
| Sequence diagram | Diagram yang menggambarkan urutan pesan antarobjek pada satu skenario. |
| Diagram kelas | Diagram yang menggambarkan kelas beserta atribut, operasi, dan hubungan antarkelas. |
| Visibilitas | Tanda akses atribut atau operasi. Tanda `+` berarti publik, tanda `-` berarti privat. |
| Multiplisitas | Jumlah objek yang dapat terhubung dalam suatu relasi, misalnya `1` atau `0..*`. |
| Matriks kerunutan | Tabel yang memetakan kelas ke use case yang merealisasikannya. |
| Laravel | *Framework* PHP yang digunakan untuk membangun Model, Controller, dan *routing* Ngaksara. |
| Blade | Mesin templat Laravel yang digunakan untuk merender View. |
| Eloquent ORM | Fitur Laravel yang digunakan Model untuk mengakses basis data. |
| Canvas API | Antarmuka browser untuk menggambar di layar, digunakan pada latihan menggambar aksara. |
| Aksara | Sistem tulisan dengan bentuk karakter tersendiri. Pada dokumen ini merujuk pada aksara Jawa dan aksara Sunda. |
| Pelajar | Aktor yang menggunakan Ngaksara untuk mempelajari materi dan mengerjakan latihan aksara. |
| Pengajar | Aktor yang membuat kelas, memberikan tugas, dan memantau progres Pelajar. |
| Tim Materi | Aktor yang mengelola konten materi dan latihan. |
| Kelas belajar | Kelompok belajar milik seorang Pengajar yang beranggotakan sejumlah Pelajar. |
| Kode bergabung | Kode unik dan aktif yang digunakan Pelajar untuk bergabung ke kelas belajar. |
| Tenggat | Batas waktu penyelesaian tugas. Tugas yang diselesaikan setelah tenggat berstatus "terlambat". |
| Streak | Jumlah hari berturut-turut Pelajar menyelesaikan aktivitas belajar. |
| Poin | Nilai yang diperoleh Pelajar setelah menyelesaikan latihan atau tugas. |
| Badge | Penanda pencapaian yang diberikan kepada Pelajar ketika memenuhi syarat tertentu. |
| Papan peringkat | Urutan poin anggota dalam satu kelas belajar. |
| Transliterasi | Pengalihan tulisan dari aksara ke huruf Latin atau sebaliknya. |
| Feedback | Masukan dari Pelajar atau Pengajar mengenai performa, tampilan, fitur, atau materi aplikasi. |
| T&C | *Terms & Conditions*, yaitu syarat dan ketentuan yang harus disetujui pengguna saat membuat akun. |
| Hash | Hasil fungsi satu arah yang digunakan untuk menyimpan kata sandi tanpa menyimpan teks aslinya. |

## 1.4 Aturan Penomoran

Tabel 1.4. Aturan Penomoran

| Hal/Bagian | Penomoran | Keterangan |
| :--- | :--- | :--- |
| Kebutuhan (hasil *Requirement Gathering*) | RXX | Contoh: R01. |
| Kebutuhan Fungsional | KFXX | Contoh: KF01. Nomor dua digit berurutan mulai dari 01. |
| Kebutuhan Non-Fungsional | KNFXX | Contoh: KNF01. Nomor dua digit berurutan mulai dari 01. |
| Aktor | Nama aktor | Dirujuk dengan nama (Pelajar, Pengajar, Tim Materi). |
| Use Case | UC-XX | Contoh: UC-01. Menggunakan tanda hubung. |
| Skenario | Skenario Normal, Skenario Alternatif N | Nomor alternatif mengikuti urutan pada subbab 4.4 dokumen SKPL. |
| Kelas | CXX | Contoh: C01. C01 sampai C26 sama dengan kelas analisis pada SKPL. |
| Operasi | + atau - di depan nama | Tanda `+` berarti publik dan tanda `-` berarti privat, yaitu hanya dipanggil oleh kelas itu sendiri. |
| Tabel | Tabel X.Y | X adalah nomor bab dan Y adalah nomor urut tabel dalam bab tersebut. |
| Gambar | Gambar N | Nomor urut gambar di seluruh dokumen. 

## 1.5 Referensi
1. Kelompok 4 K2, *Spesifikasi Kebutuhan Perangkat Lunak (SKPL) – Ngaksara*, Tugas 5 IF2150 Rekayasa Perangkat Lunak.
2. Kelompok 4 K2, *Arsitektur Perangkat Lunak (APL) – Ngaksara*, Tugas 6 IF2150 Rekayasa Perangkat Lunak.
3. Sommerville, I. (2016). *Software Engineering* (10th ed.). Pearson. Chapter 6: *Architectural Design*. https://software-engineering-book.com/slides/
4. Perangkat pembuat diagram: draw.io (https://www.drawio.com/), StarUML (https://staruml.io/), dan Mermaid (https://mermaid.js.org/).

## 1.6 Deskripsi Umum Dokumen (Ikhtisar)
Bab 1 menjelaskan tujuan, lingkup, istilah, aturan penomoran, dan referensi dokumen. Bab 2 memberi gambaran umum Ngaksara beserta diagram use case. Bab 3 mencatat lingkungan implementasi serta daftar modul, antarmuka, dan file lain yang telah dibuat. Bab 4 melaporkan strategi kedalaman pengujian kelas dan hasil pengujian unit, integrasi, use case, dan KNF lengkap dengan buktinya. Bab 5 memetakan setiap KF dan KNF ke use case, kelas, file implementasi, dan test case untuk memverifikasi bahwa seluruh kebutuhan terpenuhi.

<br>

---

# BAB 2: Deskripsi Umum Perangkat Lunak

Ngaksara menyediakan pengelolaan akun, materi, latihan, progres, motivasi belajar, pembelajaran kelas, dan penerimaan feedback. Pengguna mengakses layanan melalui browser dengan hak akses sesuai peran. Data yang dikelola mencakup profil pengguna, materi, hasil latihan, indikator motivasi, kelas, keanggotaan, tugas, penyelesaian tugas, dan feedback sistem.

Latihan mencakup menggambar dengan atau tanpa outline, mencocokkan bunyi, merangkai aksara, dan transliterasi Latin ke aksara Jawa/Sunda atau sebaliknya. Transliterasi tidak menerjemahkan makna. Sistem mengevaluasi latihan dan menyediakan hasil untuk peninjauan progres serta rekomendasi materi. Papan peringkat hanya berlaku per kelas, menggunakan nama tampilan, dan dapat diaktifkan atau dinonaktifkan Pengajar.

Pelajar dan Pengajar menyampaikan feedback aplikasi melalui UC-09. Ngaksara tidak menyediakan pengiriman pesan antar pengguna. Pengajar memantau progres anggota kelas melalui UC-08 tanpa mengirim pesan melalui sistem.

Untuk implementasi awal, Ngaksara tidak menggunakan API atau layanan eksternal. Materi, audio, gambar, font, dan komponen antarmuka disediakan sebagai aset aplikasi pada server. Basis data dan penyimpanan berkas merupakan komponen internal. Distribusi audio dan gambar dari server kepada browser merupakan bagian operasi Ngaksara.

<p align="center">
<img alt="System Overview / Use Case Diagram" src="./assets/Diagram_Use_Case_Ngaksara.png" width="90%">
</p>
<p align="center">
<i>Gambar 2.1. Use Case Diagram Ngaksara</i>
</p>

<br>

---

# BAB 3: Implementasi

Tabel 3.1. Lingkungan Implementasi

| Aspek | Keterangan |
| :--- | :--- |
| *Hardware* | Pengembangan: laptop anggota kelompok (x86-64, RAM ≥ 8 GB). Pengujian HIUPL: mesin Linux x86-64 2 vCPU. |
| *Operating System* | Pengembangan: Windows 11 dan Linux. Pengujian HIUPL: Ubuntu 24.04.5 LTS. |
| *Bahasa Pemrograman* | PHP 8.4 (pengembangan, sesuai DPPL); PHP 8.3.6 (eksekusi pengujian, `composer.json` mengunci platform PHP 8.3.0 sehingga kompatibel); JavaScript (ES2022) untuk kanvas; HTML/CSS. |
| *Framework/Library* | Laravel 13.40.0 (Eloquent ORM, Blade, Filesystem, Log), Bootstrap 5.3.8, Vite 8 + laravel-vite-plugin 3.1, Node.js 22. |
| *DBMS* | MySQL 8.4 (utf8mb4) untuk pengembangan/produksi melalui Docker; SQLite 3.45.1 untuk pengujian otomatis (in-memory) dan server uji browser (berkas `database/e2e.sqlite`). |
| *Development Tools* | Visual Studio Code, Composer 2.8, npm, Git, GitHub (*branch* fitur, *pull request*, tag rilis), Docker. |
| *Testing Tools* | PHPUnit 12.5.43 (unit, integrasi, KNF), PCOV 1.0 (coverage baris seluruh suite), Xdebug 3.4.1 (`--path-coverage`, coverage baris dan cabang per kelas), Playwright 1.63 + Chromium 157 (uji use case, tangkapan layar, KNF03 dan KNF04), Navigation Timing API dan `performance.now()` (alat ukur waktu). |

## 3.2 Implementasi Modul

Seluruh 26 kelas pada DPPL telah diimplementasikan. Nama dan ID kelas sama dengan DPPL dan Tabel B.5 dokumen Implementasi (Tugas 9).

Tabel 3.2. Implementasi Modul

| No | ID Kelas | Nama Modul/Kelas | Nama File Fisik | Nama File Executable |
| :--- | :--- | :--- | :--- | :--- |
| 1 | C01 | AkunPengguna | `src/app/Models/AkunPengguna.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 2 | C02 | MateriAksara | `src/app/Models/MateriAksara.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 3 | C03 | RiwayatLatihan | `src/app/Models/RiwayatLatihan.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 4 | C04 | DataFeedback | `src/app/Models/DataFeedback.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 5 | C05 | HalamanPendaftaran | `src/resources/views/auth/daftar.blade.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 6 | C06 | HalamanLogin | `src/resources/views/auth/masuk.blade.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 7 | C07 | HalamanUtama | `src/resources/views/beranda.blade.php`<br>`src/resources/views/layouts/sidebar.blade.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 8 | C08 | HalamanLatihan | `src/resources/views/latihan/*.blade.php`<br>`src/resources/js/latihan/kanvas-menulis.js` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 9 | C09 | HalamanKelolaMateri | `src/resources/views/materi/kelola.blade.php`<br>`src/resources/views/materi/form.blade.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 10 | C10 | HalamanFeedback | `src/resources/views/feedback/index.blade.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 11 | C11 | HalamanKelasPelajar | `src/resources/views/kelas-pelajar/index.blade.php`<br>`src/resources/views/kelas-pelajar/detail.blade.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 12 | C12 | HalamanKelolaKelas | `src/resources/views/kelas-pengajar/*.blade.php`<br>`src/resources/views/pemantauan/*.blade.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 13 | C13 | HalamanMateri | `src/resources/views/materi/index.blade.php`<br>`src/resources/views/materi/show.blade.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 14 | C14 | HalamanProgres | `src/resources/views/progres/index.blade.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 15 | C15 | OtentikasiController | `src/app/Http/Controllers/OtentikasiController.php`<br>`src/app/Http/Requests/DaftarRequest.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 16 | C16 | LatihanController | `src/app/Http/Controllers/LatihanController.php`<br>`src/app/Http/Controllers/LatihanNonKanvasController.php`<br>`src/app/Latihan/AkurasiGoresan.php`<br>`src/app/Latihan/PemeriksaBadge.php`<br>`src/app/Services/Latihan/PenilaiJawaban.php`<br>`src/app/Services/Latihan/PenyusunSoal.php`<br>`src/app/Services/Latihan/SelesaikanLatihan.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 17 | C17 | MateriController | `src/app/Http/Controllers/MateriController.php`<br>`src/app/Http/Requests/SimpanMateriRequest.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 18 | C18 | FeedbackController | `src/app/Http/Controllers/FeedbackController.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 19 | C19 | ProgresController | `src/app/Http/Controllers/ProgresController.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 20 | C20 | KelasController | `src/app/Http/Controllers/KelasPengajarController.php`<br>`src/app/Http/Controllers/KelasPelajarController.php`<br>`src/app/Http/Controllers/PemantauanKelasController.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 21 | C21 | KontenMateri | `src/app/Models/KontenMateri.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 22 | C22 | MotivasiBelajar | `src/app/Models/MotivasiBelajar.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 23 | C23 | KelasBelajar | `src/app/Models/KelasBelajar.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 24 | C24 | KeanggotaanKelas | `src/app/Models/KeanggotaanKelas.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 25 | C25 | TugasKelas | `src/app/Models/TugasKelas.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |
| 26 | C26 | PenyelesaianTugas | `src/app/Models/PenyelesaianTugas.php`<br>`src/app/Services/PengumpulanTugas.php` | - (dijalankan interpreter PHP melalui `src/public/index.php`) |

Tabel 3.3. Implementasi Antarmuka

| No | ID Antarmuka | Nama Antarmuka | Nama File Fisik | Nama File Executable |
| :--- | :--- | :--- | :--- | :--- |
| 1 | I-01 | Pendaftaran | `src/resources/views/auth/daftar.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 2 | I-02 | Login | `src/resources/views/auth/masuk.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 3 | I-03 | Halaman Utama | `src/resources/views/beranda.blade.php`<br>`src/resources/views/layouts/app.blade.php`<br>`src/resources/views/layouts/sidebar.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 4 | I-04 | Daftar Materi | `src/resources/views/materi/index.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 5 | I-05 | Detail Materi | `src/resources/views/materi/show.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 6 | I-06 | Pilih Mode Latihan | `src/resources/views/latihan/pilih-mode.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 7 | I-07 | Latihan Menulis | `src/resources/views/latihan/menulis.blade.php`<br>`src/resources/views/latihan/layout-fokus.blade.php`<br>`src/resources/js/latihan/kanvas-menulis.js` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 8 | I-08 | Latihan Mencocokkan Bunyi | `src/resources/views/latihan/bunyi.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 9 | I-09 | Latihan Merangkai Aksara | `src/resources/views/latihan/merangkai.blade.php`<br>`src/resources/views/latihan/transliterasi.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 10 | I-10 | Hasil Latihan | `src/resources/views/latihan/hasil.blade.php`<br>`src/resources/views/latihan/hasil-soal.blade.php`<br>`src/resources/views/latihan/kosong.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 11 | I-11 | Progres Pelajar | `src/resources/views/progres/index.blade.php`<br>`src/resources/views/progres/label-status.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 12 | I-12 | Kelas Saya & Daftar Tugas | `src/resources/views/kelas-pelajar/index.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 13 | I-13 | Pengerjaan Tugas | `src/resources/views/kelas-pelajar/detail.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 14 | I-14 | Kelola Kelas | `src/resources/views/kelas-pengajar/index.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 15 | I-15 | Terbitkan Tugas | `src/resources/views/kelas-pengajar/tugas.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 16 | I-16 | Kelola Anggota dan Papan Peringkat | `src/resources/views/kelas-pengajar/anggota.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 17 | I-17 | Pantau Progres Anggota | `src/resources/views/pemantauan/daftar-kelas.blade.php`<br>`src/resources/views/pemantauan/index.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 18 | I-18 | Kelola Materi | `src/resources/views/materi/kelola.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 19 | I-19 | Unggah Materi | `src/resources/views/materi/form.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |
| 20 | I-20 | Feedback | `src/resources/views/feedback/index.blade.php` | - (dirender Blade; aset dibundel ke `src/public/build`) |

Tabel 3.4. File Lain

| No | Nama File | Keterangan |
| :--- | :--- | :--- |
| 1 | `src/routes/web.php, auth.php, materi.php, latihan.php, progres.php, kelas-pelajar.php, kelas-pengajar.php, feedback.php` | Definisi rute HTTP dan middleware peran untuk setiap modul. |
| 2 | `src/app/Http/Controllers/BerandaController.php` | Menyiapkan data Halaman Utama (I-03) sesuai peran; pelengkap C07. |
| 3 | `src/app/Http/Middleware/CekPeran.php, src/app/Support/HakAkses.php` | Pemeriksaan hak akses per peran pada rute (KF02). |
| 4 | `src/app/Http/Requests/DaftarRequest.php, SimpanMateriRequest.php, Latihan/KirimJawabanRequest.php, Latihan/SelesaikanLatihanRequest.php` | Aturan validasi masukan formulir dan payload JSON latihan. |
| 5 | `src/app/Support/** (HasilOperasi, DataPendaftaran, Periode, Materi/*, Kelas/*, Progres/*)` | Tipe data pendukung (DTO) seperti HasilOperasi, DataKonten, FilterMateri, PaketMateri, Periode. |
| 6 | `src/app/Enums/*.php` | Enum peran, jenis aksara, mode latihan, status publikasi, status riwayat, status feedback, dan lain-lain. |
| 7 | `src/app/Latihan/Data/*.php` | Objek data soal, jawaban, hasil penilaian, dan ringkasan motivasi latihan menulis. |
| 8 | `src/app/Console/Commands/ProfilProgresKelas.php` | Perintah artisan untuk profiling query progres kelas (dipakai saat optimasi T9). |
| 9 | `src/config/ngaksara.php, src/config/latihan.php, src/config/navigation.php` | Konfigurasi materi (ekstensi, ukuran berkas, ambang tuntas), parameter algoritma latihan, dan menu navigasi. |
| 10 | `src/database/migrations/2026_11_*.php` | Skema basis data: akun_pengguna, materi_aksara, konten_materi, konten_materi_arsip, kelas_belajar, keanggotaan_kelas, tugas_kelas, komponen_tugas, riwayat_latihan, motivasi_belajar, penyelesaian_tugas, data_feedback. |
| 11 | `src/database/seeders/*.php` | Data fiktif untuk demo dan pengujian (akun uji, materi, kelas contoh K7M2QX, riwayat). |
| 12 | `src/database/factories/AkunPenggunaFactory.php` | Pembuat data akun untuk pengujian otomatis. |
| 13 | `src/resources/aksara/template_aksara.json` | Templat garis tengah dan kontur aksara Jawa/Sunda untuk penilaian goresan. |
| 14 | `src/resources/css/*.css, src/resources/js/app.js, src/resources/js/efek.js` | Gaya tampilan dan skrip antarmuka umum (dibundel Vite ke public/build). |
| 15 | `src/public/fonts/*, src/public/img/*` | Aset fon Noto Sans Javanese/Sundanese, ikon, dan ilustrasi maskot. |
| 16 | `src/composer.json, src/package.json, src/vite.config.js` | Daftar dependensi PHP dan JavaScript serta konfigurasi build aset. |
| 17 | `src/phpunit.xml` | Konfigurasi PHPUnit (basis data SQLite in-memory untuk pengujian). |
| 18 | `src/tests/Unit/Hiupl/*.php` | Unit test HIUPL per kelas (C01–C04, C15–C26) dengan anotasi ID pengujian. |
| 19 | `src/tests/Feature/Hiupl/IntegrasiTest.php, KnfTest.php` | Pengujian integrasi (INT-TC01 s.d. INT-TC14) dan pengujian KNF terotomasi. |
| 20 | `src/tests/Feature/**, src/tests/Unit/Latihan/*` | Test fitur dan unit hasil Tugas 9 yang tetap dijalankan sebagai regresi. |
| 21 | `docs/M10/pengujian/uji_uc.py` | Skrip Playwright untuk pengujian use case UC01-TC01 s.d. UC09-TC02, KNF03-TC02, dan KNF04-TC01 di browser. |

<br>

---

# BAB 4: Hasil Pengujian

## 4.1 Strategi dan Kedalaman Pengujian Kelas

Tabel 4.1.1. Identifikasi Tingkat Pengujian Kelas

| ID Kelas | Nama Kelas | Tingkat | Alasan | Test Case Terkait |
| :--- | :--- | :--- | :--- | :--- |
| C01 | AkunPengguna | Mendalam | Menyimpan kredensial dan menentukan hak akses per peran (keamanan, KF02, KF05): normalisasi email, cek duplikasi, verifikasi hash, tabel hak akses. | C01-UT01 s.d. C01-UT19 |
| C02 | MateriAksara | Mendalam | Memuat validasi metadata (nama, tingkat 1–3, duplikat), filter status publikasi, dan algoritma rekomendasi materi berdasarkan ambang tuntas (Algo-040 s.d. Algo-044). | C02-UT01 s.d. C02-UT18 |
| C03 | RiwayatLatihan | Mendalam | Memvalidasi hasil latihan (skor 0–100, status, mode) dan menghitung status progres, penguasaan, serta pola kesalahan yang menjadi dasar UC-04 dan UC-08. | C03-UT01 s.d. C03-UT27 |
| C04 | DataFeedback | Sedang | Hanya menyimpan feedback dan memajukan status satu arah; validasi isi feedback berada di C18 FeedbackController. | C04-UT01 s.d. C04-UT04 |
| C05 | HalamanPendaftaran | Tidak perlu | View Blade tanpa logika bisnis; validasi dilakukan di server (C15). Teruji tidak langsung lewat pengujian use case. | UC01-TC01, UC01-TC02 |
| C06 | HalamanLogin | Tidak perlu | View Blade formulir masuk tanpa logika bisnis. | UC01-TC01 s.d. UC09-TC02 (langkah masuk), INT-TC01, INT-TC02 |
| C07 | HalamanUtama | Tidak perlu | View Blade yang menampilkan menu sesuai peran; data disiapkan BerandaController. | UC02-TC01, INT-TC01 |
| C08 | HalamanLatihan | Tidak perlu | View Blade dan skrip kanvas; penilaian dilakukan di server (C16). Perekaman goresan diukur pada KNF04. | UC03-TC01 s.d. UC03-TC04, KNF04-TC01 |
| C09 | HalamanKelolaMateri | Tidak perlu | View Blade formulir materi; validasi berkas berada di C17 dan SimpanMateriRequest. | UC06-TC01, UC06-TC02 |
| C10 | HalamanFeedback | Tidak perlu | View Blade formulir feedback; validasi di C18. | UC09-TC01, UC09-TC02, KNF05-TC01, KNF05-TC02 |
| C11 | HalamanKelasPelajar | Tidak perlu | View Blade kelas dan tugas Pelajar tanpa logika bisnis. | UC05-TC01 s.d. UC05-TC04 |
| C12 | HalamanKelolaKelas | Tidak perlu | View Blade pengelolaan kelas dan pemantauan; aturan berada di C20, C23–C25. | UC07-TC01 s.d. UC07-TC04, UC08-TC01 s.d. UC08-TC03 |
| C13 | HalamanMateri | Tidak perlu | View Blade daftar dan detail materi; pemutaran audio memakai elemen Audio browser. | UC02-TC01 s.d. UC02-TC03, KNF03-TC02 |
| C14 | HalamanProgres | Tidak perlu | View Blade progres; perhitungan berada di C19. | UC04-TC01 s.d. UC04-TC03 |
| C15 | OtentikasiController | Mendalam | Keamanan: validasi format pendaftaran, hash kata sandi bcrypt, verifikasi login dengan pesan galat yang sama (Algo-009 s.d. Algo-012, KNF02). | C15-UT01 s.d. C15-UT09 |
| C16 | LatihanController | Mendalam | Memuat algoritma akurasi goresan, penilaian bunyi/merangkai/transliterasi, aturan poin-streak-badge (Algo-021 s.d. Algo-027). | C16-UT01 s.d. C16-UT18 |
| C17 | MateriController | Mendalam | Hak akses Tim Materi, validasi jenis dan ukuran berkas, transaksi simpan materi + konten + berkas dengan pembersihan saat gagal (Algo-049 s.d. Algo-055). | C17-UT01 s.d. C17-UT24 |
| C18 | FeedbackController | Mendalam | Validasi kategori dan panjang pesan, hak akses peran, serta penanganan kegagalan simpan (KNF05, Algo-013, Algo-014). | C18-UT01 s.d. C18-UT09 |
| C19 | ProgresController | Mendalam | Agregasi progres per Pelajar dan per kelas, rekomendasi, serta aturan akses papan peringkat (Algo-100 s.d. Algo-102). | C19-UT01 s.d. C19-UT18 |
| C20 | KelasController | Sedang | Mengoordinasikan permintaan HTTP kelas lalu mendelegasikan aturan ke C23–C26. Pada implementasi dipecah menjadi KelasPengajarController, KelasPelajarController, dan PemantauanKelasController. | C20-UT01 s.d. C20-UT22, INT-TC10 s.d. INT-TC12 |
| C21 | KontenMateri | Mendalam | Pemilihan versi konten yang kompatibel, pengarsipan versi, kenaikan nomor versi, dan pemeriksaan berkas audio (Algo-056 s.d. Algo-059). | C21-UT01 s.d. C21-UT15 |
| C22 | MotivasiBelajar | Mendalam | Aturan streak harian, poin, badge tanpa duplikasi, dan peringkat dengan nilai seri (Algo-028 s.d. Algo-033). | C22-UT01 s.d. C22-UT18 |
| C23 | KelasBelajar | Mendalam | Validasi nama kelas, pembuatan kode bergabung unik tanpa karakter ambigu, kepemilikan kelas, pengaturan papan peringkat (Algo-087 s.d. Algo-092). | C23-UT01 s.d. C23-UT17 |
| C24 | KeanggotaanKelas | Mendalam | Transaksi bergabung (baru, sudah anggota, aktivasi ulang), perubahan status, dan kontrol akses anggota (Algo-109 s.d. Algo-112). | C24-UT01 s.d. C24-UT15 |
| C25 | TugasKelas | Mendalam | Validasi tugas (tenggat, komponen wajib, mode sesuai jenis, materi terbit) dan transisi draf ke terbit (Algo-113 s.d. Algo-117). | C25-UT01 s.d. C25-UT12 |
| C26 | PenyelesaianTugas | Mendalam | Aturan ketepatan waktu terhadap tenggat dan transaksi pengumpulan sekali saja (Algo-118 s.d. Algo-121); simpanPengumpulan diwujudkan oleh layanan PengumpulanTugas. | C26-UT01 s.d. C26-UT10 |

## 4.2 Pengujian Unit Kelas

Cara menjalankan unit test: `php artisan test tests/Unit/Hiupl` (atau `php vendor/bin/phpunit --testdox tests/Unit/Hiupl`).

Lokasi file test: `src/tests/Unit/Hiupl/`. Setiap method test memuat anotasi `@uji ID | jenis | operasi | prosedur | harapan` dan `#[TestDox]` dengan ID yang sama dengan tabel di bawah.

### 4.2.1 Kelas AkunPengguna

**ID Kelas:** C01  
**Nama Kelas:** AkunPengguna  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C01AkunPenggunaTest.php`

Tabel 4.2.1. Hasil Pengujian Unit Kelas C01

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C01-UT01 | `buatAkunBaru(data: DataPendaftaran)` | Normal | Data pendaftaran baru (email budi@contoh.test, username budi, peran pelajar) | Akun tersimpan di akun_pengguna dan hasil berhasil berisi id_akun | Sesuai harapan (3 asersi lulus, 174 ms) | Pass |
| C01-UT02 | `buatAkunBaru(data: DataPendaftaran)` | Batas | Email "  Budi@Contoh.TEST  " (huruf besar dan spasi) dan nama tampilan kosong | Email disimpan dalam huruf kecil tanpa spasi; nama tampilan diisi username | Sesuai harapan (2 asersi lulus, 13 ms) | Pass |
| C01-UT03 | `buatAkunBaru(data: DataPendaftaran)` | Negatif | Email yang sudah terdaftar dipakai lagi dengan username lain | Gagal dengan kode EMAIL_TERDAFTAR; tidak ada akun baru | Sesuai harapan (3 asersi lulus, 16 ms) | Pass |
| C01-UT04 | `buatAkunBaru(data: DataPendaftaran)` | Negatif | Username yang sudah dipakai didaftarkan dengan email lain | Gagal dengan kode USERNAME_TERPAKAI | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C01-UT05 | `cariBerdasarkanEmail(email: String): AkunPengguna?` | Normal | Email akun yang terdaftar | Mengembalikan akun yang sesuai | Sesuai harapan (1 asersi lulus, 21 ms) | Pass |
| C01-UT06 | `cariBerdasarkanEmail(email: String): AkunPengguna?` | Batas | Email terdaftar ditulis dengan huruf besar dan spasi di awal/akhir | Akun tetap ditemukan | Sesuai harapan (1 asersi lulus, 12 ms) | Pass |
| C01-UT07 | `cariBerdasarkanEmail(email: String): AkunPengguna?` | Negatif | Email yang tidak terdaftar | Mengembalikan null | Sesuai harapan (1 asersi lulus, 11 ms) | Pass |
| C01-UT08 | `cocokkanPassword(password: String): Boolean` | Normal | Kata sandi yang benar untuk akun uji | true | Sesuai harapan (1 asersi lulus, 13 ms) | Pass |
| C01-UT09 | `cocokkanPassword(password: String): Boolean` | Batas | String kosong dan kata sandi benar dengan beda satu huruf kapital | Keduanya false | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C01-UT10 | `cocokkanPassword(password: String): Boolean` | Negatif | Kata sandi salah | false; hash tersimpan bukan teks asli | Sesuai harapan (2 asersi lulus, 13 ms) | Pass |
| C01-UT11 | `verifikasiHakAkses(aksi: String): Boolean` | Normal | Pelajar meminta MENGERJAKAN_LATIHAN; Pengajar meminta KELOLA_KELAS | Keduanya true | Sesuai harapan (2 asersi lulus, 13 ms) | Pass |
| C01-UT12 | `verifikasiHakAkses(aksi: String): Boolean` | Batas | Tim Materi meminta KELOLA_MATERI (satu-satunya hak) dan KIRIM_FEEDBACK | KELOLA_MATERI true, KIRIM_FEEDBACK false | Sesuai harapan (2 asersi lulus, 12 ms) | Pass |
| C01-UT13 | `verifikasiHakAkses(aksi: String): Boolean` | Negatif | Pelajar meminta KELOLA_KELAS; akun yang belum tersimpan meminta MENGERJAKAN_LATIHAN | Keduanya false | Sesuai harapan (2 asersi lulus, 12 ms) | Pass |
| C01-UT14 | `getProfil(): Profil` | Normal | Akun Pelajar "Sari" | Berisi id_akun, username, nama_tampilan, peran | Sesuai harapan (2 asersi lulus, 12 ms) | Pass |
| C01-UT15 | `getProfil(): Profil` | Batas | Akun dengan nama tampilan sama dengan username (nama tidak diisi saat daftar) | Profil memakai username sebagai nama tampilan | Sesuai harapan (1 asersi lulus, 13 ms) | Pass |
| C01-UT16 | `getProfil(): Profil` | Negatif | Profil dipakai untuk tampilan publik (papan peringkat) | Tidak memuat email maupun password_hash | Sesuai harapan (2 asersi lulus, 12 ms) | Pass |
| C01-UT17 | `getDetailAkun(): DetailAkun` | Normal | Akun Pengajar | Profil ditambah email pemilik akun | Sesuai harapan (2 asersi lulus, 12 ms) | Pass |
| C01-UT18 | `getDetailAkun(): DetailAkun` | Batas | Akun dengan email yang didaftarkan berhuruf besar | Email pada detail sudah huruf kecil | Sesuai harapan (1 asersi lulus, 13 ms) | Pass |
| C01-UT19 | `getDetailAkun(): DetailAkun` | Negatif | Objek akun kosong (belum tersimpan) | id_akun dan email bernilai null, tanpa galat | Sesuai harapan (2 asersi lulus, 11 ms) | Pass |

Tabel 4.2.1b. Ringkasan Cakupan Kelas C01

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `buatAkunBaru()`, `getProfil()`, `verifikasiHakAkses()`, `getDetailAkun()`, `cariBerdasarkanEmail()`, `cocokkanPassword()` |
| Operasi yang diuji | `buatAkunBaru()`, `cariBerdasarkanEmail()`, `cocokkanPassword()`, `getDetailAkun()`, `getProfil()`, `verifikasiHakAkses()` |
| ID Algoritma yang tercakup | Algo-001, Algo-002 |
| Coverage (wajib ≥ 70%) | `AkunPengguna`: baris 96.3% (79/82), cabang 81.1% (30/37)<br>Alat: Xdebug 3.5.3 (`--path-coverage`), PHP 8.5.12, PHPUnit 12.5.43, Windows; diukur dari unit test kelas ini ditambah test fitur yang memakai kelas ini. Bukti: Tabel 4.6 (C01 coverage). |

### 4.2.2 Kelas MateriAksara

**ID Kelas:** C02  
**Nama Kelas:** MateriAksara  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C02MateriAksaraTest.php`

Tabel 4.2.2. Hasil Pengujian Unit Kelas C02

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C02-UT01 | `simpan(data: MetadataMateri): HasilOperasi` | Normal | Nama "Nglegena 1", jawa, tingkat 1, draf | Berhasil; metadata tersimpan | Sesuai harapan (2 asersi lulus, 17 ms) | Pass |
| C02-UT02 | `simpan(data: MetadataMateri): HasilOperasi` | Batas | Tingkat 3 (maksimum), tingkat 0 dan 4 (di luar rentang), nama tepat 100 karakter | Tingkat 3 dan nama 100 karakter diterima; tingkat 0 dan 4 DATA_TIDAK_VALID | Sesuai harapan (4 asersi lulus, 13 ms) | Pass |
| C02-UT03 | `simpan(data: MetadataMateri): HasilOperasi` | Negatif | Nama kosong; nama 101 karakter; nama/jenis/tingkat sama dengan materi yang ada | NAMA_TIDAK_VALID, NAMA_TIDAK_VALID, DUPLIKAT | Sesuai harapan (4 asersi lulus, 13 ms) | Pass |
| C02-UT04 | `ambilDaftarMateri(filter: FilterMateri): List` | Normal | Filter jawa tingkat 1; ada materi terbit, draf, arsip | Hanya materi terbit | Sesuai harapan (1 asersi lulus, 22 ms) | Pass |
| C02-UT05 | `ambilDaftarMateri(filter: FilterMateri): List` | Batas | termasukDraf = true (mode Tim Materi) | Terbit dan draf tampil; arsip tetap tidak tampil | Sesuai harapan (1 asersi lulus, 17 ms) | Pass |
| C02-UT06 | `ambilDaftarMateri(filter: FilterMateri): List` | Negatif | Filter jenis sunda padahal hanya ada materi jawa | Daftar kosong (memicu UC02 Alternatif 1 "materi belum tersedia") | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C02-UT07 | `ambilTargetLatihan(mode: ModeLatihan): TargetLatihan` | Normal | Mode menulis untuk materi berisi ꦲ ꦤ | Dua butir target (ꦲ dan ꦤ) beserta templat goresan | Sesuai harapan (2 asersi lulus, 31 ms) | Pass |
| C02-UT08 | `ambilTargetLatihan(mode: ModeLatihan): TargetLatihan` | Batas | Mode merangkai (memakai pasangan contoh, bukan bentuk) | Butir berasal dari contoh "ꦲꦤ = ana" | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C02-UT09 | `ambilTargetLatihan(mode: ModeLatihan): TargetLatihan` | Negatif | Materi tanpa konten | TargetLatihan kosong | Sesuai harapan (1 asersi lulus, 13 ms) | Pass |
| C02-UT10 | `ambilMateriRekomendasi(penguasaan: Map): List` | Normal | Materi tingkat 1 belum tuntas (40) dan tingkat 2 | Rekomendasi materi tingkat 1 (tingkat 2 masih terkunci) | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C02-UT11 | `ambilMateriRekomendasi(penguasaan: Map): List` | Batas | Penguasaan tingkat 1 tepat 70 (ambang tuntas); lima materi tingkat 2 | Tingkat 1 dianggap tuntas; tingkat 2 terbuka; maksimal 3 rekomendasi | Sesuai harapan (2 asersi lulus, 21 ms) | Pass |
| C02-UT12 | `ambilMateriRekomendasi(penguasaan: Map): List` | Negatif | Semua materi sudah tuntas (≥ 70) | Daftar kosong | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C02-UT13 | `perbaruiData(data: MetadataMateri): HasilOperasi` | Normal | Ganti nama dan status menjadi terbit | Berhasil; data berubah | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C02-UT14 | `perbaruiData(data: MetadataMateri): HasilOperasi` | Batas | Data yang sama persis dengan dirinya sendiri | Berhasil (tidak dianggap duplikat dirinya) | Sesuai harapan (1 asersi lulus, 13 ms) | Pass |
| C02-UT15 | `perbaruiData(data: MetadataMateri): HasilOperasi` | Negatif | Nama diganti menjadi nama materi lain (jenis dan tingkat sama); objek belum tersimpan | DUPLIKAT; TIDAK_DITEMUKAN | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C02-UT16 | `ambilMetadata(): MetadataMateri` | Normal | Materi terbit dengan konten versi 1.0 | Metadata berisi nama, jenis, tingkat, status, versiKonten 1.0 | Sesuai harapan (1 asersi lulus, 21 ms) | Pass |
| C02-UT17 | `ambilMetadata(): MetadataMateri` | Batas | Konten sudah diperbarui sekali | versiKonten mengikuti versi terbaru (1.1) | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C02-UT18 | `ambilMetadata(): MetadataMateri` | Negatif | Materi tanpa konten | versiKonten null tanpa galat | Sesuai harapan (1 asersi lulus, 13 ms) | Pass |

Tabel 4.2.2b. Ringkasan Cakupan Kelas C02

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `ambilMetadata()`, `ambilTargetLatihan()`, `perbaruiData()`, `simpan()`, `ambilDaftarMateri()`, `ambilMateriRekomendasi()` |
| Operasi yang diuji | `ambilDaftarMateri()`, `ambilMateriRekomendasi()`, `ambilMetadata()`, `ambilTargetLatihan()`, `perbaruiData()`, `simpan()` |
| ID Algoritma yang tercakup | Algo-040, Algo-041, Algo-042, Algo-043, Algo-044 |
| Coverage (wajib ≥ 70%) | `MateriAksara`: baris 98.9% (88/89), cabang 98.4% (63/64); seluruh suite (PCOV): baris 98.9%<br>Alat: Xdebug 3.4.1 (`--path-coverage`) untuk baris dan cabang, PCOV untuk baris seluruh suite. Bukti: Tabel 4.6 (C02 coverage). |

### 4.2.3 Kelas RiwayatLatihan

**ID Kelas:** C03  
**Nama Kelas:** RiwayatLatihan  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C03RiwayatLatihanTest.php`

Tabel 4.2.3. Hasil Pengujian Unit Kelas C03

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C03-UT01 | `simpanHasil(hasil: HasilLatihan): HasilOperasi` | Normal | Hasil latihan mode bunyi, skor 80, status berhasil | Berhasil; baris riwayat tersimpan dengan waktu_selesai terisi | Sesuai harapan (3 asersi lulus, 20 ms) | Pass |
| C03-UT02 | `simpanHasil(hasil: HasilLatihan): HasilOperasi` | Batas | Skor 0 dan skor 100 (batas rentang), mode ditulis "BUNYI" | Keduanya tersimpan; mode dinormalisasi menjadi bunyi; status perlu_diulang tanpa waktu_selesai | Sesuai harapan (2 asersi lulus, 16 ms) | Pass |
| C03-UT03 | `simpanHasil(hasil: HasilLatihan): HasilOperasi` | Negatif | Skor 100.01, skor -1, status "selesai", mode "menggambar", kolom skor hilang | Gagal dengan SKOR_TIDAK_VALID, STATUS_TIDAK_VALID, MODE_TIDAK_VALID, DATA_TIDAK_LENGKAP; tidak ada baris tersimpan | Sesuai harapan (6 asersi lulus, 14 ms) | Pass |
| C03-UT04 | `catatAktivitas(aktivitas: Aktivitas): HasilOperasi` | Normal | Aktivitas "materi" (Pelajar menyelesaikan materi) | Berhasil; status selesai | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C03-UT05 | `catatAktivitas(aktivitas: Aktivitas): HasilOperasi` | Batas | Aktivitas "PENGUMPULAN" (huruf besar) tanpa id_materi | Berhasil; id_materi null | Sesuai harapan (2 asersi lulus, 13 ms) | Pass |
| C03-UT06 | `catatAktivitas(aktivitas: Aktivitas): HasilOperasi` | Negatif | Jenis "latihan" (harus lewat simpanHasil) dan data tanpa id_pelajar | AKTIVITAS_TIDAK_VALID dan DATA_TIDAK_LENGKAP | Sesuai harapan (2 asersi lulus, 11 ms) | Pass |
| C03-UT07 | `cekPernahSelesai(idPelajar, targetSoal, mode): Boolean` | Normal | Pelajar pernah berhasil pada ꦲ mode menulis | true | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C03-UT08 | `cekPernahSelesai(idPelajar, targetSoal, mode): Boolean` | Batas | Target sama tetapi mode berbeda (bunyi), dan mode ditulis huruf besar "MENULIS" | Mode berbeda: false; huruf besar: true | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C03-UT09 | `cekPernahSelesai(idPelajar, targetSoal, mode): Boolean` | Negatif | Hanya ada riwayat perlu_diulang | false | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C03-UT10 | `ambilStatusProgres(idPelajar, daftarIdMateri): Map` | Normal | Materi A rata-rata skor 90, materi B rata-rata 50 | A selesai (90%), B sedang (50%) | Sesuai harapan (2 asersi lulus, 16 ms) | Pass |
| C03-UT11 | `ambilStatusProgres(idPelajar, daftarIdMateri): Map` | Batas | Rata-rata tepat 70 (ambang tuntas) dan 69.99 | 70: selesai; 69.99: sedang | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C03-UT12 | `ambilStatusProgres(idPelajar, daftarIdMateri): Map` | Negatif | Materi tanpa riwayat apa pun | Status belum, persen 0 | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C03-UT13 | `hitungPenguasaan(idPelajar, periode): Map` | Normal | Dua riwayat pada materi yang sama: 80 dan 60 | Penguasaan materi = 70.0 | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C03-UT14 | `hitungPenguasaan(idPelajar, periode): Map` | Batas | Riwayat milik Pelajar lain dan riwayat aktivitas tanpa skor | Tidak ikut dihitung (hasil kosong untuk Pelajar uji) | Sesuai harapan (1 asersi lulus, 16 ms) | Pass |
| C03-UT15 | `hitungPenguasaan(idPelajar, periode): Map` | Negatif | ID Pelajar yang tidak ada | Map kosong tanpa galat | Sesuai harapan (1 asersi lulus, 11 ms) | Pass |
| C03-UT16 | `hitungPolaKesalahan(idPelajar, periode): Map` | Normal | Salah pada ꦤ tiga kali dan ꦲ satu kali | {ꦤ: 3, ꦲ: 1} urut dari yang terbanyak | Sesuai harapan (1 asersi lulus, 16 ms) | Pass |
| C03-UT17 | `hitungPolaKesalahan(idPelajar, periode): Map` | Batas | Enam aksara berbeda masing-masing salah sekali | Hanya 5 aksara teratas yang dikembalikan | Sesuai harapan (1 asersi lulus, 16 ms) | Pass |
| C03-UT18 | `hitungPolaKesalahan(idPelajar, periode): Map` | Negatif | Semua riwayat berstatus berhasil | Map kosong | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C03-UT19 | `ambilRiwayat(idPelajar, periode): List<RiwayatLatihan>` | Normal | Tiga riwayat pada hari berbeda, periode semua | Tiga baris, urut terbaru lebih dulu, beserta nama materi | Sesuai harapan (2 asersi lulus, 18 ms) | Pass |
| C03-UT20 | `ambilRiwayat(idPelajar, periode): List<RiwayatLatihan>` | Batas | Periode 7 hari terakhir; riwayat tepat di awal periode (6 hari lalu 00.00) dan 7 hari lalu | Hanya riwayat 6 hari lalu yang masuk | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C03-UT21 | `ambilRiwayat(idPelajar, periode): List<RiwayatLatihan>` | Negatif | Riwayat milik Pelajar lain | Daftar kosong | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C03-UT22 | `ambilBuktiPenyelesaian(idPelajar, idTugas): Map` | Normal | Aktivitas tugas untuk satu komponen materi | Peta {idKomponen: waktu_selesai} | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C03-UT23 | `ambilBuktiPenyelesaian(idPelajar, idTugas): Map` | Batas | Komponen yang sama diselesaikan dua kali | Satu entri dengan waktu terakhir | Sesuai harapan (2 asersi lulus, 16 ms) | Pass |
| C03-UT24 | `ambilBuktiPenyelesaian(idPelajar, idTugas): Map` | Negatif | Latihan tugas berstatus perlu_diulang | Tidak dianggap bukti (map kosong) | Sesuai harapan (1 asersi lulus, 16 ms) | Pass |
| C03-UT25 | `ambilRiwayatMateri(idPelajar, idMateri) / ambilRiwayatPelajar(idPelajar)` | Normal | Dua riwayat pada materi A dan satu pada materi B | ambilRiwayatMateri(A) = 2 baris; ambilRiwayatPelajar = 3 baris | Sesuai harapan (2 asersi lulus, 16 ms) | Pass |
| C03-UT26 | `ambilRiwayatMateri(idPelajar, idMateri) / ambilRiwayatPelajar(idPelajar)` | Batas | Dua riwayat dengan waktu sama persis | Urutan stabil: id_riwayat terbesar lebih dulu | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C03-UT27 | `ambilRiwayatMateri(idPelajar, idMateri) / ambilRiwayatPelajar(idPelajar)` | Negatif | Materi yang tidak pernah dikerjakan; Pelajar tanpa riwayat | Keduanya koleksi kosong | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |

Tabel 4.2.3b. Ringkasan Cakupan Kelas C03

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `simpanHasil()`, `catatAktivitas()`, `ambilRiwayat()`, `ambilBuktiPenyelesaian()`, `ambilStatusProgres()`, `hitungPenguasaan()`, `ambilRiwayatPelajar()`, `hitungPolaKesalahan()`, `cekPernahSelesai()`, `ambilRiwayatMateri()` |
| Operasi yang diuji | `ambilBuktiPenyelesaian()`, `ambilRiwayat()`, `ambilRiwayatMateri()`, `ambilRiwayatPelajar()`, `ambilStatusProgres()`, `catatAktivitas()`, `cekPernahSelesai()`, `hitungPenguasaan()`, `hitungPolaKesalahan()`, `simpanHasil()` |
| ID Algoritma yang tercakup | - |
| Coverage (wajib ≥ 70%) | `RiwayatLatihan`: baris 97.7% (127/130), cabang 87.9% (58/66); seluruh suite (PCOV): baris 99.2%<br>Alat: Xdebug 3.4.1 (`--path-coverage`) untuk baris dan cabang, PCOV untuk baris seluruh suite. Bukti: Tabel 4.6 (C03 coverage). |

### 4.2.4 Kelas DataFeedback

**ID Kelas:** C04  
**Nama Kelas:** DataFeedback  
**Tingkat:** Sedang  
**File test:** `src/tests/Unit/Hiupl/C04DataFeedbackTest.php`

Tabel 4.2.4. Hasil Pengujian Unit Kelas C04

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C04-UT01 | `simpan(data: MasukanFeedback): HasilOperasi` | Normal | Feedback kategori fitur dari Pelajar | Berhasil; baris data_feedback berstatus baru | Sesuai harapan (1 asersi lulus, 17 ms) | Pass |
| C04-UT02 | `simpan(data: MasukanFeedback): HasilOperasi` | Negatif | id_akun yang tidak ada di akun_pengguna (melanggar foreign key) | Gagal dengan SIMPAN_GAGAL; tidak ada baris tersimpan | Sesuai harapan (2 asersi lulus, 12 ms) | Pass |
| C04-UT03 | `perbaruiStatus(status: StatusFeedback): HasilOperasi` | Normal | Status baru dimajukan ke ditinjau lalu selesai | Keduanya berhasil; status akhir selesai | Sesuai harapan (3 asersi lulus, 14 ms) | Pass |
| C04-UT04 | `perbaruiStatus(status: StatusFeedback): HasilOperasi` | Negatif | Status selesai dimundurkan ke baru; status diubah pihak lain sebelum disimpan | STATUS_TIDAK_VALID dan STATUS_BERUBAH | Sesuai harapan (2 asersi lulus, 18 ms) | Pass |

### 4.2.5 Kelas OtentikasiController

**ID Kelas:** C15  
**Nama Kelas:** OtentikasiController  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C15OtentikasiControllerTest.php`

Tabel 4.2.5. Hasil Pengujian Unit Kelas C15

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C15-UT01 | `verifikasiFormatData(data: DataPendaftaran)` | Normal | Kirim formulir daftar yang valid ke POST /daftar | Tidak ada galat validasi; akun dibuat | Sesuai harapan (2 asersi lulus, 278 ms) | Pass |
| C15-UT02 | `verifikasiFormatData(data: DataPendaftaran)` | Batas | Kata sandi tepat 8 karakter (huruf + angka) dan username tepat 3 karakter | Diterima tanpa galat | Sesuai harapan (1 asersi lulus, 251 ms) | Pass |
| C15-UT03 | `verifikasiFormatData(data: DataPendaftaran)` | Negatif | Sandi 7 karakter, sandi tanpa angka, email tidak valid, T&C tidak dicentang, peran tim_materi | Ditolak dengan galat pada isian terkait; tidak ada akun dibuat | Sesuai harapan (11 asersi lulus, 31 ms) | Pass |
| C15-UT04 | `simpanAkun(data: DataPendaftaran): HasilOperasi` | Normal | Data Pelajar valid | Berhasil; sandi tersimpan sebagai hash bcrypt; baris motivasi_belajar dibuat | Sesuai harapan (5 asersi lulus, 477 ms) | Pass |
| C15-UT05 | `simpanAkun(data: DataPendaftaran): HasilOperasi` | Batas | Data Pengajar valid | Berhasil tanpa baris motivasi_belajar (motivasi hanya untuk Pelajar) | Sesuai harapan (2 asersi lulus, 246 ms) | Pass |
| C15-UT06 | `simpanAkun(data: DataPendaftaran): HasilOperasi` | Negatif | Email yang sudah terdaftar | Gagal dengan kode EMAIL_TERDAFTAR; jumlah akun tetap | Sesuai harapan (2 asersi lulus, 479 ms) | Pass |
| C15-UT07 | `validasiLogin(email: String, password: String): HasilOperasi` | Normal | Email dan sandi akun uji yang benar | Berhasil, data berisi akun yang sesuai | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C15-UT08 | `validasiLogin(email: String, password: String): HasilOperasi` | Batas | Email benar dengan huruf besar; sandi benar | Berhasil (email tidak peka huruf besar) | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C15-UT09 | `validasiLogin(email: String, password: String): HasilOperasi` | Negatif | (a) sandi salah, (b) email tidak terdaftar | Keduanya gagal dengan kode dan pesan yang sama (KREDENSIAL_SALAH) agar tidak membocorkan email terdaftar | Sesuai harapan (3 asersi lulus, 16 ms) | Pass |

Tabel 4.2.5b. Ringkasan Cakupan Kelas C15

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `verifikasiFormatData()`, `simpanAkun()`, `validasiLogin()` |
| Operasi yang diuji | `simpanAkun()`, `validasiLogin()`, `verifikasiFormatData()`<br>Catatan: hashPassword() bersifat privat dan diuji lewat simpanAkun (C15-UT04). |
| ID Algoritma yang tercakup | Algo-009, Algo-010, Algo-011, Algo-012 |
| Coverage (wajib ≥ 70%) | `OtentikasiController`: baris 100.0% (75/75), cabang 94.3% (33/35)<br>`DaftarRequest`: baris 100.0% (24/24), cabang 92.0% (23/25)<br>Alat: Xdebug 3.5.3 (`--path-coverage`), PHP 8.5.12, PHPUnit 12.5.43, Windows; diukur dari unit test kelas ini ditambah test fitur yang memakai kelas ini. Bukti: Tabel 4.6 (C15 coverage). |

### 4.2.6 Kelas LatihanController

**ID Kelas:** C16  
**Nama Kelas:** LatihanController  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C16LatihanControllerTest.php`

Tabel 4.2.6. Hasil Pengujian Unit Kelas C16

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C16-UT01 | `siapkanSoal(idMateri: Integer, mode: ModeLatihan): SoalLatihan` | Normal | Pelajar membuka GET /latihan/1/menulis | 200; soal aktif tersimpan di sesi; templat goresan tidak dikirim ke HTML | Sesuai harapan (4 asersi lulus, 77 ms) | Pass |
| C16-UT02 | `siapkanSoal(idMateri: Integer, mode: ModeLatihan): SoalLatihan` | Batas | Halaman soal dimuat ulang (refresh) | Soal yang sama dipakai lagi (idSoal tetap) | Sesuai harapan (5 asersi lulus, 77 ms) | Pass |
| C16-UT03 | `siapkanSoal(idMateri: Integer, mode: ModeLatihan): SoalLatihan` | Negatif | (a) tamu tanpa login; (b) Pengajar membuka latihan | (a) dialihkan ke /masuk; (b) 403 | Sesuai harapan (3 asersi lulus, 47 ms) | Pass |
| C16-UT04 | `hitungAkurasiGoresan(goresan: List<Goresan>, templat: TemplatAksara): Decimal` | Normal | Goresan tepat di garis tengah ꦲ, ꦏ, ꦤ | Skor 100.0 | Sesuai harapan (3 asersi lulus, 115 ms) | Pass |
| C16-UT05 | `hitungAkurasiGoresan(goresan: List<Goresan>, templat: TemplatAksara): Decimal` | Batas | Goresan kosong, satu titik, templat kosong; goresan separuh huruf | Kasus tepi: 0.0; separuh: < 70 (ambang lulus) | Sesuai harapan (4 asersi lulus, 64 ms) | Pass |
| C16-UT06 | `hitungAkurasiGoresan(goresan: List<Goresan>, templat: TemplatAksara): Decimal` | Negatif | Menulis ꦗ untuk soal ꦲ, dan ꦕ untuk soal ꦫ | Skor < 70 (tidak lulus) | Sesuai harapan (2 asersi lulus, 87 ms) | Pass |
| C16-UT07 | `nilaiJawaban(jawaban: JawabanLatihan): HasilPenilaian` | Normal | POST /latihan/jawaban dengan goresan sesuai templat soal | 200; status berhasil; skor ≥ 70 dihitung server | Sesuai harapan (5 asersi lulus, 105 ms) | Pass |
| C16-UT08 | `nilaiJawaban(jawaban: JawabanLatihan): HasilPenilaian` | Batas | 51 goresan (batas 50), 2001 titik dalam satu goresan (batas 2000), koordinat x = 3.5 | 422 untuk semuanya | Sesuai harapan (5 asersi lulus, 106 ms) | Pass |
| C16-UT09 | `nilaiJawaban(jawaban: JawabanLatihan): HasilPenilaian` | Negatif | (a) goresan jauh dari templat; (b) idSoal bukan soal aktif | (a) perlu_diulang dan riwayat tercatat; (b) 409 SOAL_TIDAK_DITEMUKAN | Sesuai harapan (5 asersi lulus, 95 ms) | Pass |
| C16-UT10 | `nilaiJawaban (mode non-kanvas: bunyi, merangkai, transliterasi)` | Normal | Pilihan bunyi benar; urutan ubin benar; jawaban "Hana" untuk kunci "hana" | Semua benar, skor 100 | Sesuai harapan (3 asersi lulus, 42 ms) | Pass |
| C16-UT11 | `nilaiJawaban (mode non-kanvas)` | Batas | Merangkai 3 ubin dengan 1 posisi tertukar; urutan kosong vs kunci kosong | Skor 33.33 dengan posisiSalah [1,2]; urutan kosong skor 0 | Sesuai harapan (3 asersi lulus, 41 ms) | Pass |
| C16-UT12 | `nilaiJawaban (mode non-kanvas)` | Negatif | Bunyi tanpa pilihan (null), transliterasi kosong, merangkai kurang ubin | Semua salah | Sesuai harapan (3 asersi lulus, 39 ms) | Pass |
| C16-UT13 | `simpanHasil(hasil: HasilLatihan): RingkasanMotivasi` | Normal | Jawaban benar lalu POST /latihan/selesai | Riwayat berhasil tersimpan; poin +10; streak 1; badge pemula | Sesuai harapan (6 asersi lulus, 105 ms) | Pass |
| C16-UT14 | `simpanHasil(hasil: HasilLatihan): RingkasanMotivasi` | Batas | Soal yang sama diselesaikan dua kali | Riwayat bertambah tetapi poin tidak bertambah lagi (tetap 10) | Sesuai harapan (2 asersi lulus, 46 ms) | Pass |
| C16-UT15 | `simpanHasil(hasil: HasilLatihan): RingkasanMotivasi` | Negatif | (a) POST /latihan/selesai sebelum jawaban berhasil; (b) daftar hasil kosong; (c) skor 150 | (a) 422 BELUM_BERHASIL; (b) TANPA_HASIL; (c) SIMPAN_GAGAL dan transaksi dibatalkan | Sesuai harapan (6 asersi lulus, 64 ms) | Pass |
| C16-UT16 | `periksaBadge(streak, poin, dimiliki): List<String>` | Normal | streak 7, poin 100, belum punya badge | [pemula, konsisten_7, seratus_poin] | Sesuai harapan (1 asersi lulus, 38 ms) | Pass |
| C16-UT17 | `periksaBadge(streak, poin, dimiliki): List<String>` | Batas | streak 6, poin 9 (tepat di bawah ambang) dan poin 10 | Poin 9: []; poin 10: [pemula] | Sesuai harapan (2 asersi lulus, 35 ms) | Pass |
| C16-UT18 | `periksaBadge(streak, poin, dimiliki): List<String>` | Negatif | Syarat terpenuhi tetapi badge sudah dimiliki | Daftar kosong (tidak diberikan ulang) | Sesuai harapan (1 asersi lulus, 32 ms) | Pass |

Tabel 4.2.6b. Ringkasan Cakupan Kelas C16

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `siapkanSoal()`, `nilaiJawaban()`, `simpanHasil()` |
| Operasi yang diuji | `hitungAkurasiGoresan()`, `nilaiJawaban()`, `periksaBadge()`, `siapkanSoal()`, `simpanHasil()`<br>Catatan: siapkanSoal, nilaiJawaban, dan simpanHasil diuji melalui LatihanController; algoritma pembantunya (hitungAkurasiGoresan, cocokkanBunyi, validasiRangkaian, periksaBadge) diuji langsung pada kelas implementasinya. |
| ID Algoritma yang tercakup | Algo-021, Algo-022, Algo-023, Algo-024, Algo-025, Algo-026, Algo-027 |
| Coverage (wajib ≥ 70%) | `LatihanController`: baris 68.1% (113/166), cabang 66.0% (66/100); seluruh suite (PCOV): baris 95.8%<br>`LatihanNonKanvasController`: baris 99.3% (136/137), cabang 94.9% (56/59); seluruh suite (PCOV): baris 99.3%<br>`AkurasiGoresan`: baris 96.0% (72/75), cabang 91.7% (55/60); seluruh suite (PCOV): baris 96.0%<br>`PemeriksaBadge`: baris 100.0% (10/10), cabang 100.0% (1/1); seluruh suite (PCOV): baris 100.0%<br>`PenilaiJawaban`: baris 100.0% (15/15), cabang 100.0% (29/29); seluruh suite (PCOV): baris 100.0%<br>`PenyusunSoal`: baris 95.7% (67/70), cabang 92.7% (51/55); seluruh suite (PCOV): baris 95.7%<br>`SelesaikanLatihan`: baris 97.9% (46/47), cabang 100.0% (5/5); seluruh suite (PCOV): baris 97.9%<br>Gabungan seluruh berkas C16 dari unit test C16 (Xdebug): baris 81,8% (256/313), cabang 80,0% (156/195). Cabang `LatihanController` di bawah 70% karena aksi HTTP lain (index, hasil, ulangi, lanjut) diuji oleh test fitur `tests/Feature/Latihan`, yang tidak dapat diukur cabangnya dalam batas waktu karena perhitungan akurasi sangat lambat di bawah Xdebug; coverage baris seluruh suite 95,8%.<br>Alat: Xdebug 3.4.1 (`--path-coverage`) untuk baris dan cabang, PCOV untuk baris seluruh suite. Bukti: Tabel 4.6 (C16 coverage). |

### 4.2.7 Kelas MateriController

**ID Kelas:** C17  
**Nama Kelas:** MateriController  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C17MateriControllerTest.php`

Tabel 4.2.7. Hasil Pengujian Unit Kelas C17

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C17-UT01 | `ambilDaftarMateri(filter: FilterMateri): List<MetadataMateri>` | Normal | Pelajar dengan riwayat skor 90 pada materi terbit | Daftar berisi materi dengan statusProgres selesai, persen 90 | Sesuai harapan (1 asersi lulus, 20 ms) | Pass |
| C17-UT02 | `ambilDaftarMateri(filter: FilterMateri): List<MetadataMateri>` | Batas | Pelajar meminta termasukDraf = true | Permintaan draf diabaikan; hanya materi terbit | Sesuai harapan (1 asersi lulus, 16 ms) | Pass |
| C17-UT03 | `ambilDaftarMateri(filter: FilterMateri): List<MetadataMateri>` | Negatif | Filter tingkat 3 tanpa materi | Daftar kosong | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C17-UT04 | `muatPaketMateri(idMateri: Integer, versiAplikasi: String): PaketMateri` | Normal | Materi terbit, versi 1.0 | Paket berhasil dengan aksara ꦲ ꦤ | Sesuai harapan (2 asersi lulus, 16 ms) | Pass |
| C17-UT05 | `muatPaketMateri(idMateri: Integer, versiAplikasi: String): PaketMateri` | Batas | Materi draf dibuka Tim Materi (pratinjau) dan Pelajar | Tim Materi: berhasil; Pelajar: MATERI_TIDAK_TERSEDIA | Sesuai harapan (2 asersi lulus, 17 ms) | Pass |
| C17-UT06 | `muatPaketMateri(idMateri: Integer, versiAplikasi: String): PaketMateri` | Negatif | ID tidak ada; materi terbit tanpa konten | MATERI_TIDAK_TERSEDIA; KONTEN_TIDAK_ADA | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C17-UT07 | `ambilAudioPengucapan(idMateri: Integer): HasilBerkas` | Normal | Materi terbit dengan berkas audio ada | Berhasil dengan path audio | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C17-UT08 | `ambilAudioPengucapan(idMateri: Integer): HasilBerkas` | Batas | Materi terbit tanpa audio | AUDIO_TIDAK_ADA (halaman tetap tampil tanpa tombol audio) | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C17-UT09 | `ambilAudioPengucapan(idMateri: Integer): HasilBerkas` | Negatif | Materi draf; audio terdaftar tetapi berkas hilang | MATERI_TIDAK_TERSEDIA; BERKAS_HILANG (UC02 Alternatif 2) | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C17-UT10 | `tambahMateri(data: DataKonten, berkas: Berkas?): HasilOperasi` | Normal | Tim Materi menambah materi dengan audio mp3 | Berhasil; materi + konten tersimpan; berkas audio ada di penyimpanan | Sesuai harapan (2 asersi lulus, 19 ms) | Pass |
| C17-UT11 | `tambahMateri(data: DataKonten, berkas: Berkas?): HasilOperasi` | Batas | Tanpa berkas (berkas opsional) | Berhasil; path_audio null | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C17-UT12 | `tambahMateri(data: DataKonten, berkas: Berkas?): HasilOperasi` | Negatif | (a) Pelajar menambah materi; (b) berkas .exe; (c) konten tidak lengkap | AKSES_DITOLAK; FORMAT_TIDAK_VALID; KONTEN_TIDAK_LENGKAP dan transaksi dibatalkan (tidak ada materi) | Sesuai harapan (4 asersi lulus, 14 ms) | Pass |
| C17-UT13 | `validasiFormatFile(berkas: Berkas): HasilValidasi` | Normal | Berkas json berisi objek valid dan png kecil | Keduanya valid | Sesuai harapan (2 asersi lulus, 12 ms) | Pass |
| C17-UT14 | `validasiFormatFile(berkas: Berkas): HasilValidasi` | Batas | mp3 tepat 10.240 KB (10 MB) dan 10.241 KB; ekstensi "MP3" huruf besar | 10 MB valid; 10.241 KB tidak valid; MP3 valid | Sesuai harapan (3 asersi lulus, 11 ms) | Pass |
| C17-UT15 | `validasiFormatFile(berkas: Berkas): HasilValidasi` | Negatif | Berkas .pdf dan berkas json yang isinya bukan JSON | Tidak valid: "Jenis berkas tidak didukung" dan "Isi berkas json tidak valid" (UC06 Alternatif 1) | Sesuai harapan (2 asersi lulus, 10 ms) | Pass |
| C17-UT16 | `perbaruiMateri(idMateri: Integer, data: DataKonten): HasilOperasi` | Normal | Tim Materi mengubah aturan materi | Berhasil; versi konten naik 1.1 | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C17-UT17 | `perbaruiMateri(idMateri: Integer, data: DataKonten): HasilOperasi` | Negatif | Materi yang sudah diarsipkan; Pengajar memperbarui | TIDAK_DITEMUKAN; AKSES_DITOLAK | Sesuai harapan (2 asersi lulus, 13 ms) | Pass |
| C17-UT18 | `hapusMateri(idMateri: Integer): HasilOperasi` | Normal | Tim Materi menghapus materi terbit | Status menjadi arsip (soft delete); materi tidak tampil di daftar Pelajar | Sesuai harapan (2 asersi lulus, 20 ms) | Pass |
| C17-UT19 | `hapusMateri(idMateri: Integer): HasilOperasi` | Batas | Menghapus materi yang sudah diarsipkan (dua kali) | TIDAK_DITEMUKAN pada percobaan kedua | Sesuai harapan (1 asersi lulus, 13 ms) | Pass |
| C17-UT20 | `hapusMateri(idMateri: Integer): HasilOperasi` | Negatif | Pelajar menghapus materi | AKSES_DITOLAK; materi tetap terbit | Sesuai harapan (2 asersi lulus, 12 ms) | Pass |
| C17-UT21 | `ambilKontenLatihan(idMateri: Integer, mode: ModeLatihan): TargetLatihan` | Normal | Materi terbit, mode menulis | Target berisi ꦲ dan ꦤ | Sesuai harapan (1 asersi lulus, 13 ms) | Pass |
| C17-UT22 | `ambilKontenLatihan(idMateri: Integer, mode: ModeLatihan): TargetLatihan` | Batas | Materi terbit tanpa konten | Target kosong (halaman latihan menampilkan "belum tersedia") | Sesuai harapan (1 asersi lulus, 11 ms) | Pass |
| C17-UT23 | `ambilKontenLatihan(idMateri: Integer, mode: ModeLatihan): TargetLatihan` | Negatif | Materi draf dan ID tidak ada | Target kosong (tidak dapat dilatih) | Sesuai harapan (2 asersi lulus, 12 ms) | Pass |
| C17-UT24 | `perbaruiMateri(idMateri: Integer, data: DataKonten): HasilOperasi` | Batas | Materi lama tanpa konten diperbarui | Konten baru dibuat dengan versi 1.0 | Sesuai harapan (2 asersi lulus, 13 ms) | Pass |

Tabel 4.2.7b. Ringkasan Cakupan Kelas C17

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `ambilDaftarMateri()`, `muatPaketMateri()`, `ambilAudioPengucapan()`, `ambilKontenLatihan()`, `tambahMateri()`, `perbaruiMateri()`, `validasiFormatFile()`, `hapusMateri()` |
| Operasi yang diuji | `ambilAudioPengucapan()`, `ambilDaftarMateri()`, `ambilKontenLatihan()`, `hapusMateri()`, `muatPaketMateri()`, `perbaruiMateri()`, `tambahMateri()`, `validasiFormatFile()` |
| ID Algoritma yang tercakup | Algo-049, Algo-050, Algo-051, Algo-052, Algo-053, Algo-054, Algo-055 |
| Coverage (wajib ≥ 70%) | `MateriController`: baris 77.2% (156/202), cabang 70.7% (111/157); seluruh suite (PCOV): baris 77.2%<br>`SimpanMateriRequest`: baris 97.1% (34/35), cabang 77.8% (7/9); seluruh suite (PCOV): baris 97.1%<br>Alat: Xdebug 3.4.1 (`--path-coverage`) untuk baris dan cabang, PCOV untuk baris seluruh suite. Bukti: Tabel 4.6 (C17 coverage). |

### 4.2.8 Kelas FeedbackController

**ID Kelas:** C18  
**Nama Kelas:** FeedbackController  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C18FeedbackControllerTest.php`

Tabel 4.2.8. Hasil Pengujian Unit Kelas C18

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C18-UT01 | `validasiFeedback(data: MasukanFeedback): HasilValidasi` | Normal | Pelajar, kategori fitur, pesan 30 karakter | Tidak ada galat | Sesuai harapan (1 asersi lulus, 11 ms) | Pass |
| C18-UT02 | `validasiFeedback(data: MasukanFeedback): HasilValidasi` | Batas | Pesan tepat 10 dan 1.000 karakter (valid); 9 dan 1.001 karakter; 10 karakter diapit spasi | 10 dan 1.000: valid; 9 dan 1.001: galat pesan; spasi tidak dihitung | Sesuai harapan (5 asersi lulus, 10 ms) | Pass |
| C18-UT03 | `validasiFeedback(data: MasukanFeedback): HasilValidasi` | Negatif | (a) kategori "lainnya" dan pesan kosong; (b) akun Tim Materi; (c) tanpa akun | (a) galat kategori dan pesan; (b)(c) HttpException 403 | Sesuai harapan (3 asersi lulus, 10 ms) | Pass |
| C18-UT04 | `simpanFeedback(data: MasukanFeedback): HasilOperasi` | Normal | Pengajar mengirim kategori tampilan | Berhasil; baris data_feedback dengan id_akun Pengajar dan pesan ter-trim | Sesuai harapan (2 asersi lulus, 11 ms) | Pass |
| C18-UT05 | `simpanFeedback(data: MasukanFeedback): HasilOperasi` | Batas | Pesan 1.000 karakter beraksara Jawa (multibyte) | Tersimpan utuh 1.000 karakter | Sesuai harapan (1 asersi lulus, 11 ms) | Pass |
| C18-UT06 | `simpanFeedback(data: MasukanFeedback): HasilOperasi` | Negatif | (a) pesan 5 karakter; (b) tabel data_feedback tidak dapat ditulis (tabel dihapus) | (a) DATA_TIDAK_VALID; (b) SIMPAN_GAGAL dan galat tercatat di log | Sesuai harapan (3 asersi lulus, 14 ms) | Pass |
| C18-UT07 | `kirimKonfirmasi(hasil: HasilOperasi): void` | Normal | HasilOperasi sukses | berhasil true dengan pesan "Feedback terkirim. Matur nuwun!" | Sesuai harapan (1 asersi lulus, 9 ms) | Pass |
| C18-UT08 | `kirimKonfirmasi(hasil: HasilOperasi): void` | Negatif | HasilOperasi gagal SIMPAN_GAGAL | berhasil false; pesan meminta mencoba lagi dan menyatakan isian tidak hilang | Sesuai harapan (2 asersi lulus, 9 ms) | Pass |
| C18-UT09 | `kirimKonfirmasi(hasil: HasilOperasi): void` | Batas | Alur HTTP POST /feedback saat penyimpanan gagal | Kembali ke formulir dengan pesan galat dan isian lama dipertahankan (old input) | Sesuai harapan (4 asersi lulus, 14 ms) | Pass |

Tabel 4.2.8b. Ringkasan Cakupan Kelas C18

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `validasiFeedback()`, `simpanFeedback()`, `kirimKonfirmasi()` |
| Operasi yang diuji | `kirimKonfirmasi()`, `simpanFeedback()`, `validasiFeedback()` |
| ID Algoritma yang tercakup | Algo-013, Algo-014 |
| Coverage (wajib ≥ 70%) | `FeedbackController`: baris 100.0% (36/36), cabang 93.9% (31/33)<br>Alat: Xdebug 3.5.3 (`--path-coverage`), PHP 8.5.12, PHPUnit 12.5.43, Windows; diukur dari unit test kelas ini ditambah test fitur yang memakai kelas ini. Bukti: Tabel 4.6 (C18 coverage). |

### 4.2.9 Kelas ProgresController

**ID Kelas:** C19  
**Nama Kelas:** ProgresController  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C19ProgresControllerTest.php`

Tabel 4.2.9. Hasil Pengujian Unit Kelas C19

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C19-UT01 | `hitungProgres(idPelajar: Integer, periode: Periode): RingkasanProgres` | Normal | Riwayat 80 dan 60 pada satu materi, satu kesalahan pada ꦤ | Penguasaan 70, jumlahLatihan 2, pola {ꦤ:1}, kosong false | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C19-UT02 | `hitungProgres(idPelajar: Integer, periode: Periode): RingkasanProgres` | Batas | Riwayat 10 hari lalu dengan filter 7 hari terakhir | Tidak dihitung; kosong true | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C19-UT03 | `hitungProgres(idPelajar: Integer, periode: Periode): RingkasanProgres` | Negatif | Pelajar tanpa riwayat | kosong true, penguasaan [] (memicu UC04 Alternatif 1) | Sesuai harapan (2 asersi lulus, 11 ms) | Pass |
| C19-UT04 | `siapkanRekomendasi(idPelajar: Integer): List<MateriAksara>` | Normal | Penguasaan materi tingkat 1 = 50 | Rekomendasi berisi materi itu dengan penguasaan 50 | Sesuai harapan (2 asersi lulus, 13 ms) | Pass |
| C19-UT05 | `siapkanRekomendasi(idPelajar: Integer): List<MateriAksara>` | Batas | Pelajar baru tanpa riwayat | Materi tingkat 1 direkomendasikan dengan penguasaan null | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C19-UT06 | `siapkanRekomendasi(idPelajar: Integer): List<MateriAksara>` | Negatif | Belum ada materi terbit | Daftar kosong | Sesuai harapan (1 asersi lulus, 11 ms) | Pass |
| C19-UT07 | `ambilScoreboard(idKelas: Integer): List<Peringkat>` | Normal | Anggota aktif membuka kelas dengan scoreboard aktif | aktif true; daftar berisi anggota | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C19-UT08 | `ambilScoreboard(idKelas: Integer): List<Peringkat>` | Batas | Pengajar pemilik (bukan anggota) membuka; scoreboard nonaktif dibuka anggota | Pemilik: aktif true; nonaktif: pesan "belum diaktifkan" | Sesuai harapan (2 asersi lulus, 12 ms) | Pass |
| C19-UT09 | `ambilScoreboard(idKelas: Integer): List<Peringkat>` | Negatif | Pelajar bukan anggota; kelas tidak ada | aktif false dengan pesan "bukan anggota aktif" / "tidak ditemukan"; daftar kosong (UC08 Alternatif 2) | Sesuai harapan (2 asersi lulus, 11 ms) | Pass |
| C19-UT10 | `agregasiProgres(anggotaAktif: List<Profil>, periode: Periode): RingkasanProgres` | Normal | Dua anggota: skor 80 dan 60 | jumlahLatihan 2, rataPenguasaan 70 | Sesuai harapan (1 asersi lulus, 17 ms) | Pass |
| C19-UT11 | `agregasiProgres(anggotaAktif: List<Profil>, periode: Periode): RingkasanProgres` | Batas | Anggota ada tetapi tanpa riwayat dalam periode | jumlahAnggota 1, rataPenguasaan null, kosong true (UC08 Alternatif 1) | Sesuai harapan (1 asersi lulus, 20 ms) | Pass |
| C19-UT12 | `agregasiProgres(anggotaAktif: List<Profil>, periode: Periode): RingkasanProgres` | Negatif | Daftar anggota kosong; riwayat non-anggota ada | Ringkasan kosong; riwayat non-anggota tidak ikut | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C19-UT13 | `ambilIndikatorMotivasi(idPelajar: Integer): DataMotivasi` | Normal | Pelajar memiliki badge pemula | Daftar badge lengkap dengan pemula dimiliki = true dan badge lain false | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C19-UT14 | `ambilIndikatorMotivasi(idPelajar: Integer): DataMotivasi` | Negatif | Pelajar tanpa baris motivasi | streak 0, poin 0, semua badge dimiliki = false | Sesuai harapan (2 asersi lulus, 13 ms) | Pass |
| C19-UT15 | `ambilIndikatorMotivasi(idPelajar: Integer): DataMotivasi` | Batas | Pelajar memiliki ketiga badge | Seluruh badge dimiliki = true | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C19-UT16 | `ambilRiwayat(idPelajar, periode) / ambilDetailHasil(idPelajar, idMateri)` | Normal | Dua riwayat materi A, satu materi B | ambilRiwayat semua = 3; ambilDetailHasil(A) = 2 | Sesuai harapan (2 asersi lulus, 17 ms) | Pass |
| C19-UT17 | `ambilRiwayat(idPelajar, periode) / hitungPolaKesalahan(idPelajar, periode)` | Batas | Kesalahan 10 hari lalu, filter 7 hari | Riwayat dan pola kesalahan kosong untuk periode itu | Sesuai harapan (3 asersi lulus, 17 ms) | Pass |
| C19-UT18 | `ambilDetailHasil(idPelajar, idMateri) / hitungPolaKesalahan(idPelajar, periode)` | Negatif | Materi yang tidak dikerjakan; Pelajar tanpa kesalahan | Koleksi kosong; pola kosong | Sesuai harapan (2 asersi lulus, 16 ms) | Pass |

Tabel 4.2.9b. Ringkasan Cakupan Kelas C19

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `ambilRiwayat()`, `hitungProgres()`, `siapkanRekomendasi()`, `ambilScoreboard()`, `agregasiProgres()`, `hitungPolaKesalahan()`, `ambilDetailHasil()`, `ambilIndikatorMotivasi()` |
| Operasi yang diuji | `agregasiProgres()`, `ambilDetailHasil()`, `ambilIndikatorMotivasi()`, `ambilRiwayat()`, `ambilScoreboard()`, `hitungPolaKesalahan()`, `hitungProgres()`, `siapkanRekomendasi()` |
| ID Algoritma yang tercakup | Algo-100, Algo-101, Algo-102 |
| Coverage (wajib ≥ 70%) | `ProgresController`: baris 100.0% (135/135), cabang 97.1% (34/35); seluruh suite (PCOV): baris 100.0%<br>Alat: Xdebug 3.4.1 (`--path-coverage`) untuk baris dan cabang, PCOV untuk baris seluruh suite. Bukti: Tabel 4.6 (C19 coverage). |

### 4.2.10 Kelas KelasController

**ID Kelas:** C20  
**Nama Kelas:** KelasController  
**Tingkat:** Sedang  
**File test:** `src/tests/Unit/Hiupl/C20KelasControllerTest.php`

Tabel 4.2.10. Hasil Pengujian Unit Kelas C20

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C20-UT01 | `buatKelas(data: DataKelas): HasilOperasi` | Normal | Pengajar POST /pengajar/kelas nama "Aksara Jawa Dasar" | Redirect ke daftar kelas dengan pesan sukses; kelas tersimpan | Sesuai harapan (4 asersi lulus, 22 ms) | Pass |
| C20-UT02 | `buatKelas(data: DataKelas): HasilOperasi` | Negatif | Nama kosong; Pelajar membuat kelas | Galat isian nama; 403 | Sesuai harapan (3 asersi lulus, 20 ms) | Pass |
| C20-UT03 | `validasiKodeBergabung(kode) + gabungkanPelajar(idKelas): HasilOperasi` | Normal | Pelajar POST /pelajar/kelas/gabung dengan kode huruf kecil yang benar | Redirect ke halaman kelas; keanggotaan aktif | Sesuai harapan (3 asersi lulus, 26 ms) | Pass |
| C20-UT04 | `validasiKodeBergabung(kode: String): HasilOperasi` | Negatif | Kode "ZZZZZZ" (tidak terdaftar) dan "AB-12" (format salah) | Galat isian kode; tidak ada keanggotaan (UC05 Alternatif 3) | Sesuai harapan (5 asersi lulus, 21 ms) | Pass |
| C20-UT05 | `gantiKodeKelas(idKelas: Integer): HasilOperasi` | Normal | Pemilik POST ganti-kode | Kode berubah; pesan sukses berisi kode baru | Sesuai harapan (2 asersi lulus, 18 ms) | Pass |
| C20-UT06 | `gantiKodeKelas(idKelas: Integer): HasilOperasi` | Negatif | Pengajar lain mengganti kode | 403; kode tetap | Sesuai harapan (2 asersi lulus, 18 ms) | Pass |
| C20-UT07 | `kelolaAnggota(idKelas, idPelajar, status): HasilOperasi` | Normal | Pemilik mengeluarkan anggota aktif | Status keanggotaan nonaktif; anggota kehilangan akses halaman kelas (404, kelas disembunyikan) | Sesuai harapan (3 asersi lulus, 24 ms) | Pass |
| C20-UT08 | `kelolaAnggota(idKelas, idPelajar, status): HasilOperasi` | Negatif | Mengeluarkan Pelajar yang bukan anggota | Galat "bukan anggota kelas ini" | Sesuai harapan (2 asersi lulus, 19 ms) | Pass |
| C20-UT09 | `aturScoreboard(idKelas: Integer, aktif: Boolean): HasilOperasi` | Normal | Pemilik mengaktifkan scoreboard | status_scoreboard true | Sesuai harapan (1 asersi lulus, 18 ms) | Pass |
| C20-UT10 | `aturScoreboard(idKelas: Integer, aktif: Boolean): HasilOperasi` | Negatif | Nilai aktif "mungkin" (bukan boolean) | Galat validasi isian aktif | Sesuai harapan (2 asersi lulus, 19 ms) | Pass |
| C20-UT11 | `buatTugas + terbitkanTugas(idKelas, data: DataTugas): HasilOperasi` | Normal | Pemilik mengirim instruksi, tenggat besok, satu komponen latihan menulis wajib | Tugas berstatus terbit | Sesuai harapan (2 asersi lulus, 35 ms) | Pass |
| C20-UT12 | `buatTugas + terbitkanTugas(idKelas, data: DataTugas): HasilOperasi` | Negatif | Tenggat kemarin dan tanpa komponen | Galat tenggat dan komponen; tidak ada tugas | Sesuai harapan (4 asersi lulus, 21 ms) | Pass |
| C20-UT13 | `ambilProgresAnggota(idKelas, idPelajar): Data` | Normal | Pemilik meminta progres anggota aktif dengan satu riwayat | Berhasil; ringkasan jumlahLatihan 1 | Sesuai harapan (1 asersi lulus, 20 ms) | Pass |
| C20-UT14 | `ambilProgresAnggota(idKelas, idPelajar): Data` | Negatif | Pelajar bukan anggota aktif | BUKAN_ANGGOTA_AKTIF (UC08 Alternatif 2) | Sesuai harapan (1 asersi lulus, 21 ms) | Pass |
| C20-UT15 | `kirimUmpanBalik(idKelas, data: DataUmpanBalik): HasilOperasi` | Normal | Pemilik mengirim pesan ke anggota aktif | Pesan tersimpan di data_feedback untuk anggota tersebut | Sesuai harapan (2 asersi lulus, 19 ms) | Pass |
| C20-UT16 | `kirimUmpanBalik(idKelas, data: DataUmpanBalik): HasilOperasi` | Negatif | Pesan kosong; penerima bukan anggota | Galat isian pesan; tidak ada data tersimpan | Sesuai harapan (5 asersi lulus, 21 ms) | Pass |
| C20-UT17 | `ambilKelasPengajar() + ambilDaftarAnggota(idKelas)` | Normal | Pemilik membuka /pengajar/kelas dan /pengajar/kelas/{id}/anggota | 200; nama kelas dan nama anggota aktif tampil | Sesuai harapan (4 asersi lulus, 35 ms) | Pass |
| C20-UT18 | `ambilKelasPengajar() + ambilDaftarAnggota(idKelas)` | Negatif | Pengajar lain membuka daftar anggota; Pelajar membuka daftar kelas Pengajar | 403 untuk keduanya | Sesuai harapan (2 asersi lulus, 22 ms) | Pass |
| C20-UT19 | `ambilTugasAktif(idKelas) + bukaTugas(idKelas, idTugas)` | Normal | Anggota membuka halaman kelas dan detail tugas terbit | 200; instruksi tugas tampil | Sesuai harapan (4 asersi lulus, 39 ms) | Pass |
| C20-UT20 | `ambilTugasAktif(idKelas) + bukaTugas(idKelas, idTugas)` | Negatif | Bukan anggota membuka kelas; anggota membuka ID tugas yang tidak ada | 404 untuk keduanya | Sesuai harapan (2 asersi lulus, 23 ms) | Pass |
| C20-UT21 | `simpanPengumpulan(idKelas, idTugas)` | Normal | Anggota menyelesaikan komponen materi lalu POST kumpulkan | Pesan sukses; penyelesaian_tugas tepat_waktu | Sesuai harapan (2 asersi lulus, 33 ms) | Pass |
| C20-UT22 | `simpanPengumpulan(idKelas, idTugas)` | Negatif | Pengajar mencoba POST kumpulkan | 403; tidak ada penyelesaian | Sesuai harapan (2 asersi lulus, 17 ms) | Pass |

### 4.2.11 Kelas KontenMateri

**ID Kelas:** C21  
**Nama Kelas:** KontenMateri  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C21KontenMateriTest.php`

Tabel 4.2.11. Hasil Pengujian Unit Kelas C21

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C21-UT01 | `ambilKonten(versiAplikasi: String): PaketMateri` | Normal | Versi aplikasi 1.0, konten versi 1.0 | Paket berisi aksara ꦲ ꦤ, aturan, contoh, versi 1.0 | Sesuai harapan (3 asersi lulus, 15 ms) | Pass |
| C21-UT02 | `ambilKonten(versiAplikasi: String): PaketMateri` | Batas | Konten terbaru butuh aplikasi 2.0 tetapi versi lama (1.0) ada di arsip; aplikasi klien versi 1.5 | Versi arsip 1.0 yang kompatibel dipilih | Sesuai harapan (2 asersi lulus, 17 ms) | Pass |
| C21-UT03 | `ambilKonten(versiAplikasi: String): PaketMateri` | Negatif | Aplikasi versi 0.9, semua konten butuh minimal 1.0 | Galat KONTEN_TIDAK_KOMPATIBEL | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C21-UT04 | `ambilAudio(): HasilBerkas` | Normal | path_audio "materi/1/ha.mp3" ada di disk penyimpanan | Berhasil dengan path berkas | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C21-UT05 | `ambilAudio(): HasilBerkas` | Batas | path_audio berupa string kosong | AUDIO_TIDAK_ADA | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C21-UT06 | `ambilAudio(): HasilBerkas` | Negatif | path_audio terisi tetapi berkas tidak ada di disk | BERKAS_HILANG (memicu UC02 Alternatif 2) | Sesuai harapan (1 asersi lulus, 16 ms) | Pass |
| C21-UT07 | `perbaruiKonten(data: DataKonten): HasilOperasi` | Normal | Aturan diubah | Berhasil; versi naik 1.0 → 1.1; versi lama masuk konten_materi_arsip | Sesuai harapan (3 asersi lulus, 15 ms) | Pass |
| C21-UT08 | `perbaruiKonten(data: DataKonten): HasilOperasi` | Batas | Data identik dengan konten sekarang | Berhasil dengan pesan "Tidak ada perubahan"; versi tetap 1.0; tidak ada arsip | Sesuai harapan (3 asersi lulus, 14 ms) | Pass |
| C21-UT09 | `perbaruiKonten(data: DataKonten): HasilOperasi` | Negatif | Aturan berisi spasi saja | KONTEN_TIDAK_LENGKAP; konten lama tidak berubah | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C21-UT10 | `simpanKonten(data: DataKonten): HasilOperasi` | Normal | Konten lengkap untuk materi baru | Berhasil; versi_konten 1.0 | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C21-UT11 | `simpanKonten(data: DataKonten): HasilOperasi` | Negatif | Bentuk aksara kosong | KONTEN_TIDAK_LENGKAP; tidak ada baris konten | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C21-UT12 | `simpanKonten(data: DataKonten): HasilOperasi` | Batas | Bentuk, aturan, dan contoh diapit spasi | Tersimpan setelah di-trim | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C21-UT13 | `arsipkanVersi(): HasilOperasi` | Normal | Konten versi 1.0 diarsipkan | Berhasil; baris konten_materi_arsip berisi salinan versi 1.0 | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C21-UT14 | `arsipkanVersi(): HasilOperasi` | Batas | Konten diperbarui dua kali (1.0 → 1.1 → 1.2) | Arsip berisi versi 1.0 dan 1.1; konten aktif 1.2 | Sesuai harapan (2 asersi lulus, 16 ms) | Pass |
| C21-UT15 | `arsipkanVersi(): HasilOperasi` | Negatif | Tabel arsip tidak tersedia saat konten diperbarui | Operasi gagal dengan galat basis data dan konten aktif tidak tertimpa | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |

Tabel 4.2.11b. Ringkasan Cakupan Kelas C21

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `ambilKonten()`, `ambilAudio()`, `perbaruiKonten()`, `arsipkanVersi()`, `simpanKonten()` |
| Operasi yang diuji | `ambilAudio()`, `ambilKonten()`, `arsipkanVersi()`, `perbaruiKonten()`, `simpanKonten()` |
| ID Algoritma yang tercakup | Algo-056, Algo-057, Algo-058, Algo-059 |
| Coverage (wajib ≥ 70%) | `KontenMateri`: baris 98.7% (73/74), cabang 92.5% (37/40); seluruh suite (PCOV): baris 98.6%<br>Alat: Xdebug 3.4.1 (`--path-coverage`) untuk baris dan cabang, PCOV untuk baris seluruh suite. Bukti: Tabel 4.6 (C21 coverage). |

### 4.2.12 Kelas MotivasiBelajar

**ID Kelas:** C22  
**Nama Kelas:** MotivasiBelajar  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C22MotivasiBelajarTest.php`

Tabel 4.2.12. Hasil Pengujian Unit Kelas C22

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C22-UT01 | `perbaruiStreak(tanggal: Date)` | Normal | Aktivitas pada 01, 02, dan 03-11-2026 berturut-turut | Streak = 3 | Sesuai harapan (1 asersi lulus, 22 ms) | Pass |
| C22-UT02 | `perbaruiStreak(tanggal: Date)` | Batas | Dua aktivitas pada hari yang sama (23.59 lalu 00.00 hari berikutnya juga diuji) | Hari sama: streak tetap 1; pergantian hari 23.59 ke 00.00: streak menjadi 2 | Sesuai harapan (2 asersi lulus, 16 ms) | Pass |
| C22-UT03 | `perbaruiStreak(tanggal: Date)` | Negatif | Aktivitas 01 dan 02-11, lalu terlewat sehari dan aktif lagi 04-11 | Streak kembali 1 | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C22-UT04 | `tambahPoin(idAktivitas: Integer, nilai: Integer): HasilOperasi` | Normal | Tambah 10 poin dua kali | Berhasil; total poin 20 | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C22-UT05 | `tambahPoin(idAktivitas: Integer, nilai: Integer): HasilOperasi` | Batas | Nilai poin terkecil yang sah, yaitu 1 | Berhasil; poin = 1 | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C22-UT06 | `tambahPoin(idAktivitas: Integer, nilai: Integer): HasilOperasi` | Negatif | Nilai 0 dan nilai -5 | Gagal (NILAI_TIDAK_VALID); poin tetap 0 | Sesuai harapan (3 asersi lulus, 14 ms) | Pass |
| C22-UT07 | `berikanBadge(kode: String): HasilOperasi` | Normal | Badge "pemula" diberikan pertama kali | Berhasil dengan baru = true; daftar badge berisi pemula | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C22-UT08 | `berikanBadge(kode: String): HasilOperasi` | Batas | Badge kedua yang berbeda ditambahkan setelah badge pertama | Daftar berisi dua badge sesuai urutan | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C22-UT09 | `berikanBadge(kode: String): HasilOperasi` | Negatif | Badge yang sama diberikan dua kali | Pemberian kedua baru = false; badge tidak ganda | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C22-UT10 | `susunScoreboard(anggotaAktif: List<Profil>): List<Peringkat>` | Normal | Tiga anggota dengan poin 50, 30, 10 | Peringkat 1, 2, 3 urut poin menurun dengan nama tampilan | Sesuai harapan (2 asersi lulus, 18 ms) | Pass |
| C22-UT11 | `susunScoreboard(anggotaAktif: List<Profil>): List<Peringkat>` | Batas | Poin sama (30, 30) dan anggota tanpa baris motivasi | Poin sama mendapat peringkat sama (1, 1, 3); anggota tanpa motivasi berpoin 0; tidak ada email di hasil | Sesuai harapan (3 asersi lulus, 17 ms) | Pass |
| C22-UT12 | `susunScoreboard(anggotaAktif: List<Profil>): List<Peringkat>` | Negatif | Daftar anggota kosong | Hasil daftar kosong, tanpa galat | Sesuai harapan (1 asersi lulus, 11 ms) | Pass |
| C22-UT13 | `ambilMotivasi(idPelajar: Integer): Data` | Normal | Pelajar aktif hari ini dengan streak 1, poin 40, badge pemula | Data berisi streak 1, poin 40, badge [pemula] | Sesuai harapan (1 asersi lulus, 16 ms) | Pass |
| C22-UT14 | `ambilMotivasi(idPelajar: Integer): Data` | Batas | Aktivitas terakhir kemarin (masih tersambung) dan tiga hari lalu (terputus) | Kemarin: streak tetap; tiga hari lalu: streak ditampilkan 0 tanpa mengubah data tersimpan | Sesuai harapan (3 asersi lulus, 17 ms) | Pass |
| C22-UT15 | `ambilMotivasi(idPelajar: Integer): Data` | Negatif | ID Pelajar yang tidak punya baris motivasi | Streak 0, poin 0, badge kosong | Sesuai harapan (1 asersi lulus, 12 ms) | Pass |
| C22-UT16 | `ambilPoinKelas(anggotaAktif: List<Integer>): List<Peringkat>` | Normal | Tiga anggota dengan poin 10, 40, 20 | Urut 40, 20, 10 | Sesuai harapan (1 asersi lulus, 18 ms) | Pass |
| C22-UT17 | `ambilPoinKelas(anggotaAktif: List<Integer>): List<Peringkat>` | Batas | Dua anggota berpoin sama dan satu tanpa baris motivasi | Poin sama diurutkan id terkecil; tanpa baris = 0 | Sesuai harapan (2 asersi lulus, 17 ms) | Pass |
| C22-UT18 | `ambilPoinKelas(anggotaAktif: List<Integer>): List<Peringkat>` | Negatif | Daftar anggota kosong | Koleksi kosong | Sesuai harapan (1 asersi lulus, 11 ms) | Pass |

Tabel 4.2.12b. Ringkasan Cakupan Kelas C22

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `perbaruiStreak()`, `tambahPoin()`, `berikanBadge()`, `susunScoreboard()`, `ambilMotivasi()`, `ambilPoinKelas()` |
| Operasi yang diuji | `ambilMotivasi()`, `ambilPoinKelas()`, `berikanBadge()`, `perbaruiStreak()`, `susunScoreboard()`, `tambahPoin()` |
| ID Algoritma yang tercakup | Algo-028, Algo-029, Algo-030, Algo-031, Algo-032, Algo-033 |
| Coverage (wajib ≥ 70%) | `MotivasiBelajar`: baris 97.2% (69/71), cabang 91.8% (45/49); seluruh suite (PCOV): baris 97.2%<br>Alat: Xdebug 3.4.1 (`--path-coverage`) untuk baris dan cabang, PCOV untuk baris seluruh suite. Bukti: Tabel 4.6 (C22 coverage). |

### 4.2.13 Kelas KelasBelajar

**ID Kelas:** C23  
**Nama Kelas:** KelasBelajar  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C23KelasBelajarTest.php`

Tabel 4.2.13. Hasil Pengujian Unit Kelas C23

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C23-UT01 | `simpan(data: DataKelas): HasilOperasi` | Normal | Nama "Kelas 7A" dan pemilik Pengajar | Berhasil; kelas aktif dengan kode 6 karakter dan scoreboard nonaktif | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C23-UT02 | `simpan(data: DataKelas): HasilOperasi` | Batas | Nama tepat 100 karakter, dan nama 101 karakter | 100 karakter diterima; 101 karakter NAMA_TIDAK_VALID | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C23-UT03 | `simpan(data: DataKelas): HasilOperasi` | Negatif | Nama berisi spasi saja; pemilik kosong | NAMA_TIDAK_VALID dan PEMILIK_TIDAK_VALID; tidak ada kelas tersimpan | Sesuai harapan (3 asersi lulus, 12 ms) | Pass |
| C23-UT04 | `buatKodeBergabung(): String` | Normal | Dipanggil 50 kali | Setiap kode 6 karakter dari alfabet tanpa huruf/angka ambigu (I, O, 0, 1) | Sesuai harapan (50 asersi lulus, 16 ms) | Pass |
| C23-UT05 | `buatKodeBergabung(): String` | Batas | Kode yang sudah dipakai kelas lain ada di basis data | Kode baru tidak sama dengan kode yang sudah tersimpan | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C23-UT06 | `gantiKodeBergabung(idPengajar: Integer): HasilOperasi` | Normal | Pemilik kelas mengganti kode | Berhasil; kode di basis data berubah dan kode lama tidak berlaku | Sesuai harapan (3 asersi lulus, 14 ms) | Pass |
| C23-UT07 | `gantiKodeBergabung(idPengajar: Integer): HasilOperasi` | Batas | Pemilik mengganti kode pada kelas yang sudah diarsipkan | TIDAK_DITEMUKAN | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C23-UT08 | `gantiKodeBergabung(idPengajar: Integer): HasilOperasi` | Negatif | Pengajar lain (bukan pemilik) mengganti kode | AKSES_DITOLAK; kode tidak berubah | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C23-UT09 | `periksaPemilik(idPengajar: Integer): Boolean` | Normal | ID pemilik kelas | true | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C23-UT10 | `periksaPemilik(idPengajar: Integer): Boolean` | Batas | null (sesi tanpa akun) | false | Sesuai harapan (1 asersi lulus, 13 ms) | Pass |
| C23-UT11 | `periksaPemilik(idPengajar: Integer): Boolean` | Negatif | ID Pengajar lain | false | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C23-UT12 | `aturScoreboard(aktif: Boolean): HasilOperasi` | Normal | Aktifkan papan peringkat | Berhasil; statusScoreboard aktif dan kolom bernilai true | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C23-UT13 | `aturScoreboard(aktif: Boolean): HasilOperasi` | Batas | Aktifkan dua kali lalu nonaktifkan | Tetap berhasil (idempoten); status akhir nonaktif | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C23-UT14 | `aturScoreboard(aktif: Boolean): HasilOperasi` | Negatif | Objek kelas yang tidak valid (tanpa nama dan pemilik, gagal disimpan) | GAGAL_SIMPAN | Sesuai harapan (1 asersi lulus, 12 ms) | Pass |
| C23-UT15 | `daftarKelasPengajar(idPengajar: Integer): List` | Normal | Pengajar memiliki dua kelas aktif (B dan A) | Dua kelas urut nama (A, B) | Sesuai harapan (1 asersi lulus, 13 ms) | Pass |
| C23-UT16 | `daftarKelasPengajar(idPengajar: Integer): List` | Batas | Satu kelas aktif dan satu kelas arsip | Hanya kelas aktif | Sesuai harapan (1 asersi lulus, 20 ms) | Pass |
| C23-UT17 | `daftarKelasPengajar(idPengajar: Integer): List` | Negatif | Pengajar lain tanpa kelas | Daftar kosong (kelas Pengajar lain tidak terlihat) | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |

Tabel 4.2.13b. Ringkasan Cakupan Kelas C23

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `validasiKode()`, `gabungkanPelajar()`, `gantiKodeBergabung()`, `finalisasiTugas()`, `periksaPemilik()`, `ambilDaftarTugas()`, `bukaTugas()`, `simpan()`, `buatKodeBergabung()`, `daftarKelasPengajar()`, `aturScoreboard()` |
| Operasi yang diuji | `aturScoreboard()`, `buatKodeBergabung()`, `daftarKelasPengajar()`, `gantiKodeBergabung()`, `periksaPemilik()`, `simpan()`<br>Catatan: validasiKode, gabungkanPelajar, ambilDaftarTugas, bukaTugas, dan finalisasiTugas pada DPPL diwujudkan oleh KeanggotaanKelas (C24-UT01 s.d. C24-UT03), KelasPelajarController (C20-UT03, C20-UT04, C20-UT19, C20-UT20), dan PengumpulanTugas (C26-UT04 s.d. C26-UT06). |
| ID Algoritma yang tercakup | Algo-087, Algo-088, Algo-089, Algo-090, Algo-091, Algo-092, Algo-093, Algo-094, Algo-095, Algo-096, Algo-097 |
| Coverage (wajib ≥ 70%) | `KelasBelajar`: baris 81.4% (48/59), method 57.1% (4/7)<br>Alat: Xdebug 3.5.3 (`xdebug.mode=coverage`), PHP 8.3.36, PHPUnit 12.5.43, Windows. Bukti: Tabel 4.6 (C23 coverage). |

### 4.2.14 Kelas KeanggotaanKelas

**ID Kelas:** C24  
**Nama Kelas:** KeanggotaanKelas  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C24KeanggotaanKelasTest.php`

Tabel 4.2.14. Hasil Pengujian Unit Kelas C24

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C24-UT01 | `simpanKeanggotaan(idPelajar: Integer, idKelas: Integer): HasilOperasi` | Normal | Pelajar baru bergabung ke kelas aktif | berhasilBergabung; satu baris keanggotaan aktif | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C24-UT02 | `simpanKeanggotaan(idPelajar: Integer, idKelas: Integer): HasilOperasi` | Batas | (a) Pelajar yang sudah anggota bergabung lagi; (b) anggota berstatus keluar bergabung lagi | (a) sudahAnggota tanpa baris ganda; (b) anggotaDiaktifkan | Sesuai harapan (3 asersi lulus, 16 ms) | Pass |
| C24-UT03 | `simpanKeanggotaan(idPelajar: Integer, idKelas: Integer): HasilOperasi` | Negatif | (a) kelas diarsipkan; (b) kelas tidak ada; (c) akun Pengajar mencoba bergabung | kelasTidakAktif, kelasTidakAktif, pelajarTidakValid; tidak ada baris | Sesuai harapan (4 asersi lulus, 15 ms) | Pass |
| C24-UT04 | `ubahStatus(status: String): HasilOperasi` | Normal | Anggota aktif dikeluarkan (status keluar) | statusDiubah; periksaAkses menjadi false | Sesuai harapan (2 asersi lulus, 15 ms) | Pass |
| C24-UT05 | `ubahStatus(status: String): HasilOperasi` | Batas | Anggota keluar diaktifkan kembali pada kelas yang sudah diarsipkan; anggota dinonaktifkan pada kelas arsip | Aktivasi: kelasTidakAktif; nonaktif: statusDiubah | Sesuai harapan (2 asersi lulus, 16 ms) | Pass |
| C24-UT06 | `ubahStatus(status: String): HasilOperasi` | Negatif | Status "dihapus" (tidak dikenal) dan objek keanggotaan yang belum tersimpan | statusTidakValid | Sesuai harapan (2 asersi lulus, 14 ms) | Pass |
| C24-UT07 | `periksaAkses(idPelajar: Integer, idKelas: Integer): Boolean` | Normal | Anggota aktif | true | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C24-UT08 | `periksaAkses(idPelajar: Integer, idKelas: Integer): Boolean` | Batas | Anggota berstatus nonaktif | false | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C24-UT09 | `periksaAkses(idPelajar: Integer, idKelas: Integer): Boolean` | Negatif | Pelajar yang bukan anggota | false | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C24-UT10 | `daftarAnggotaAktif(idKelas: Integer): List` | Normal | Dua anggota aktif dan satu anggota keluar | Dua anggota aktif | Sesuai harapan (2 asersi lulus, 16 ms) | Pass |
| C24-UT11 | `daftarAnggotaAktif(idKelas: Integer): List` | Batas | Kelas tanpa anggota | Daftar kosong | Sesuai harapan (1 asersi lulus, 13 ms) | Pass |
| C24-UT12 | `daftarAnggotaAktif(idKelas: Integer): List` | Negatif | Anggota kelas lain | Tidak ikut terdaftar | Sesuai harapan (1 asersi lulus, 14 ms) | Pass |
| C24-UT13 | `daftarAnggota(idKelas: Integer): List` | Normal | Anggota aktif, keluar, dan nonaktif | Ketiganya terdaftar dengan statusnya (riwayat keanggotaan tidak hilang) | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C24-UT14 | `daftarAnggota(idKelas: Integer): List` | Batas | Anggota keluar lalu bergabung lagi | Tetap satu baris (tidak ganda) berstatus aktif | Sesuai harapan (1 asersi lulus, 15 ms) | Pass |
| C24-UT15 | `daftarAnggota(idKelas: Integer): List` | Negatif | ID kelas yang tidak ada | Koleksi kosong | Sesuai harapan (1 asersi lulus, 12 ms) | Pass |

Tabel 4.2.14b. Ringkasan Cakupan Kelas C24

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `simpanKeanggotaan()`, `periksaAkses()`, `daftarAnggotaAktif()`, `ubahStatus()`, `daftarAnggota()` |
| Operasi yang diuji | `daftarAnggota()`, `daftarAnggotaAktif()`, `periksaAkses()`, `simpanKeanggotaan()`, `ubahStatus()` |
| ID Algoritma yang tercakup | Algo-109, Algo-110, Algo-111, Algo-112 |
| Coverage (wajib ≥ 70%) | `KeanggotaanKelas`: baris 83.3% (50/60), cabang 76.5% (13/17); seluruh suite (PCOV): baris 98.3%<br>Alat: Xdebug 3.4.1 (`--path-coverage`) untuk baris dan cabang, PCOV untuk baris seluruh suite. Bukti: Tabel 4.6 (C24 coverage). |

### 4.2.15 Kelas TugasKelas

**ID Kelas:** C25  
**Nama Kelas:** TugasKelas  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C25TugasKelasTest.php`

Tabel 4.2.15. Hasil Pengujian Unit Kelas C25

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C25-UT01 | `simpan(data: DataTugas): HasilOperasi` | Normal | Instruksi, tenggat 3 hari lagi, satu komponen latihan wajib | drafDisimpan; tugas berstatus draf dengan 1 komponen | Sesuai harapan (2 asersi lulus, 19 ms) | Pass |
| C25-UT02 | `simpan(data: DataTugas): HasilOperasi` | Batas | Komponen materi (mode null) + latihan tidak wajib, satu komponen wajib; tenggat 1 menit lagi | drafDisimpan dengan 2 komponen | Sesuai harapan (2 asersi lulus, 19 ms) | Pass |
| C25-UT03 | `simpan(data: DataTugas): HasilOperasi` | Negatif | (a) tenggat sudah lewat; (b) tanpa komponen wajib; (c) mode latihan tidak dikenal; (d) materi masih draf; (e) kelas diarsipkan | dataTidakValid untuk (a)–(d), kelasTidakAktif untuk (e); tidak ada tugas tersimpan | Sesuai harapan (6 asersi lulus, 24 ms) | Pass |
| C25-UT04 | `terbitkan(): HasilOperasi` | Normal | Draf valid diterbitkan | tugasDiterbitkan; status terbit; muncul di ambilTugasAktif | Sesuai harapan (2 asersi lulus, 22 ms) | Pass |
| C25-UT05 | `terbitkan(): HasilOperasi` | Batas | Tugas yang sudah terbit diterbitkan lagi | sudahTerbit (idempoten, tidak galat) | Sesuai harapan (1 asersi lulus, 22 ms) | Pass |
| C25-UT06 | `terbitkan(): HasilOperasi` | Negatif | (a) draf tanpa komponen; (b) draf yang tenggatnya sudah lewat; (c) objek tugas belum tersimpan | KOMPONEN_KOSONG, dataTidakValid, tugasTidakDitemukan; status tetap draf | Sesuai harapan (4 asersi lulus, 26 ms) | Pass |
| C25-UT07 | `ambilTugasAktif(idKelas: Integer): List` | Normal | Dua tugas terbit dengan tenggat berbeda | Urut tenggat terdekat | Sesuai harapan (1 asersi lulus, 28 ms) | Pass |
| C25-UT08 | `ambilTugasAktif(idKelas: Integer): List` | Batas | Kelas hanya punya draf | Daftar kosong (draf tidak terlihat Pelajar) | Sesuai harapan (1 asersi lulus, 21 ms) | Pass |
| C25-UT09 | `ambilDetail(idKelas: Integer, idTugas: Integer): TugasKelas?` | Negatif | ID tugas dari kelas lain | null | Sesuai harapan (2 asersi lulus, 23 ms) | Pass |
| C25-UT10 | `ambilDetail(idKelas: Integer, idTugas: Integer): TugasKelas?` | Normal | Tugas terbit pada kelas yang benar | Tugas beserta komponennya | Sesuai harapan (1 asersi lulus, 22 ms) | Pass |
| C25-UT11 | `ambilDetail(idKelas: Integer, idTugas: Integer): TugasKelas?` | Batas | Tugas masih draf | null (draf belum boleh dibuka Pelajar) | Sesuai harapan (1 asersi lulus, 18 ms) | Pass |
| C25-UT12 | `ambilTugasAktif(idKelas: Integer): List` | Negatif | Tugas terbit milik kelas lain | Tidak ikut dalam daftar kelas ini | Sesuai harapan (1 asersi lulus, 29 ms) | Pass |

Tabel 4.2.15b. Ringkasan Cakupan Kelas C25

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `ambilTugasAktif()`, `terbitkan()`, `verifikasiDanSimpan()`, `ambilStatus()`, `ambilDetail()`, `simpan()` |
| Operasi yang diuji | `ambilDetail()`, `ambilTugasAktif()`, `simpan()`, `terbitkan()`<br>Catatan: verifikasiDanSimpan dan periksaKomponenWajib diwujudkan oleh PengumpulanTugas (C26-UT04 s.d. C26-UT06); ambilStatus diwujudkan oleh PenyelesaianTugas (C26-UT07 s.d. C26-UT10). |
| ID Algoritma yang tercakup | Algo-113, Algo-114, Algo-115, Algo-116, Algo-117 |
| Coverage (wajib ≥ 70%) | `TugasKelas`: baris 86.0% (98/114), method 71.4% (5/7)<br>Alat: Xdebug 3.5.3 (`xdebug.mode=coverage`), PHP 8.3.36, PHPUnit 12.5.43, Windows. Bukti: Tabel 4.6 (C25 coverage). |

### 4.2.16 Kelas PenyelesaianTugas

**ID Kelas:** C26  
**Nama Kelas:** PenyelesaianTugas  
**Tingkat:** Mendalam  
**File test:** `src/tests/Unit/Hiupl/C26PenyelesaianTugasTest.php`

Tabel 4.2.16. Hasil Pengujian Unit Kelas C26

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| C26-UT01 | `tentukanStatusKetepatan(waktuSelesai: DateTime, tenggat: DateTime): String` | Normal | Selesai 10-11-2026 20.00, tenggat 11-11-2026 21.50 | "tepat_waktu" | Sesuai harapan (1 asersi lulus, 11 ms) | Pass |
| C26-UT02 | `tentukanStatusKetepatan(waktuSelesai: DateTime, tenggat: DateTime): String` | Batas | Selesai tepat pada detik tenggat, dan satu detik sesudahnya | Tepat di tenggat: "tepat_waktu"; +1 detik: "terlambat" | Sesuai harapan (2 asersi lulus, 11 ms) | Pass |
| C26-UT03 | `tentukanStatusKetepatan(waktuSelesai: DateTime, tenggat: DateTime): String` | Negatif | Selesai sehari setelah tenggat | "terlambat" | Sesuai harapan (1 asersi lulus, 11 ms) | Pass |
| C26-UT04 | `simpanPengumpulan(idPelajar, idTugas, waktuSelesai, tenggat): HasilOperasi` | Normal | Komponen wajib selesai sebelum tenggat lalu tugas dikumpulkan | Berhasil (tugasDikumpulkan) dengan status tepat_waktu; baris penyelesaian dan catatan pengumpulan tersimpan | Sesuai harapan (3 asersi lulus, 20 ms) | Pass |
| C26-UT05 | `simpanPengumpulan(idPelajar, idTugas, waktuSelesai, tenggat): HasilOperasi` | Batas | (a) komponen selesai setelah tenggat; (b) pengumpulan diulang | (a) status terlambat; (b) sudahDikumpulkan tanpa baris ganda | Sesuai harapan (3 asersi lulus, 20 ms) | Pass |
| C26-UT06 | `simpanPengumpulan(idPelajar, idTugas, waktuSelesai, tenggat): HasilOperasi` | Negatif | (a) komponen wajib belum selesai; (b) pengguna bukan anggota kelas | (a) komponenBelumLengkap; (b) aksesDitolak; tidak ada baris penyelesaian | Sesuai harapan (3 asersi lulus, 20 ms) | Pass |
| C26-UT07 | `ambilStatus(idPelajar: Integer, daftarTugas: List): List` | Normal | Pelajar sudah mengumpulkan satu tugas | Status tugas tersebut tersedia (tepat_waktu) | Sesuai harapan (2 asersi lulus, 20 ms) | Pass |
| C26-UT08 | `ambilStatus(idPelajar: Integer, daftarTugas: List): List` | Batas | Daftar tugas kosong | Koleksi kosong | Sesuai harapan (1 asersi lulus, 12 ms) | Pass |
| C26-UT09 | `ambilStatus(idPelajar: Integer, daftarTugas: List): List` | Negatif | Pelajar lain yang tidak mengumpulkan menanyakan tugas yang sama | Status belum_selesai (status milik Pelajar lain tidak terbawa) | Sesuai harapan (1 asersi lulus, 21 ms) | Pass |
| C26-UT10 | `ambilStatus(idPelajar: Integer, daftarTugas: List): List` | Negatif | Daftar tugas berisi ID 0 (tidak valid) | InvalidArgumentException dilempar | Sesuai harapan (1 asersi lulus, 11 ms) | Pass |

Tabel 4.2.16b. Ringkasan Cakupan Kelas C26

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | `simpanPengumpulan()`, `ambilStatus()` |
| Operasi yang diuji | `ambilStatus()`, `simpanPengumpulan()`, `tentukanStatusKetepatan()`<br>Catatan: simpanAtomik dijalankan di dalam transaksi PengumpulanTugas::simpanPengumpulan (C26-UT04 s.d. C26-UT06). |
| ID Algoritma yang tercakup | Algo-118, Algo-119, Algo-120, Algo-121 |
| Coverage (wajib ≥ 70%) | `PenyelesaianTugas`: baris 94.4% (17/18), cabang 93.8% (15/16); seluruh suite (PCOV): baris 94.4%<br>`PengumpulanTugas`: baris 87.8% (65/74), cabang 95.2% (40/42); seluruh suite (PCOV): baris 97.3%<br>Alat: Xdebug 3.4.1 (`--path-coverage`) untuk baris dan cabang, PCOV untuk baris seluruh suite. Bukti: Tabel 4.6 (C26 coverage). |

<br>

## 4.3 Pengujian Integrasi

Cara menjalankan: `php artisan test tests/Feature/Hiupl/IntegrasiTest.php`. 

Lokasi: `src/tests/Feature/Hiupl/IntegrasiTest.php`.

Tabel 4.3.1. Daftar Antarmuka Antarkomponen

| ID Antarmuka | Komponen Asal | Komponen Tujuan | Jenis Batas | Test Case |
| :--- | :--- | :--- | :--- | :--- |
| AK-01 | View I-01/I-02 (HalamanPendaftaran, HalamanLogin) | OtentikasiController | View - Controller (HTTP) | INT-TC01, INT-TC02 |
| AK-02 | OtentikasiController | AkunPengguna, MotivasiBelajar | Controller - Model | INT-TC01, INT-TC02 |
| AK-03 | Model (Eloquent) | Basis data (tabel akun_pengguna, materi_aksara, riwayat_latihan, kelas_belajar, dll.) | Aplikasi - Basis data | INT-TC01, INT-TC02, INT-TC03, INT-TC04, INT-TC06, INT-TC07, INT-TC08, INT-TC10, INT-TC11, INT-TC12, INT-TC14 |
| AK-04 | MateriController | MateriAksara, KontenMateri, RiwayatLatihan | Controller - Model | INT-TC03, INT-TC04, INT-TC05 |
| AK-05 | MateriController / KontenMateri | PenyimpananBerkas (Laravel Filesystem, disk local) | Aplikasi - Penyimpanan berkas | INT-TC04, INT-TC05 |
| AK-06 | LatihanController / LatihanNonKanvasController | AkurasiGoresan, PenilaiJawaban, RiwayatLatihan, MotivasiBelajar | Controller - Model | INT-TC06, INT-TC07 |
| AK-07 | FeedbackController | DataFeedback | Controller - Model | INT-TC08, INT-TC09 |
| AK-08 | Controller | LogAplikasi (Laravel Log, storage/logs) | Aplikasi - Log | INT-TC09 |
| AK-09 | KelasPengajarController / KelasPelajarController | KelasBelajar, KeanggotaanKelas, TugasKelas, PengumpulanTugas, PenyelesaianTugas | Controller - Model | INT-TC10, INT-TC11 |
| AK-10 | PemantauanKelasController / ProgresController | RiwayatLatihan, MotivasiBelajar, KeanggotaanKelas | Controller - Controller - Model | INT-TC06, INT-TC12, INT-TC14 |
| AK-11 | Middleware CekPeran (rute) | Seluruh Controller | Rute - Controller (hak akses) | INT-TC13 |

Tabel 4.3.2. Hasil Pengujian Integrasi

| ID Pengujian | ID Antarmuka | Skenario | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| INT-TC01 | AK-01, AK-02, AK-03 | Normal: HalamanPendaftaran → OtentikasiController → AkunPengguna → Basis Data (akun_pengguna, motivasi_belajar) | POST /daftar data Pelajar valid, lalu POST /masuk dengan kredensial yang sama | Akun dan baris motivasi tersimpan; sandi ter-hash; login berhasil dan diarahkan ke beranda | Sesuai harapan (8 asersi lulus, 472 ms) | Pass |
| INT-TC02 | AK-01, AK-02, AK-03 | Negatif: HalamanLogin → OtentikasiController → AkunPengguna → Basis Data | POST /masuk dengan sandi salah | Kembali ke formulir dengan galat; pengguna tetap tamu (UC01 Alternatif 1) | Sesuai harapan (4 asersi lulus, 20 ms) | Pass |
| INT-TC03 | AK-04, AK-03 | Normal: HalamanMateri → MateriController → MateriAksara + RiwayatLatihan → Basis Data | Pelajar (data MateriSeeder) membuka GET /materi?jenis=jawa&tingkat=1 lalu GET /materi/{id} | Daftar menampilkan materi terbit; detail menampilkan isi materi | Sesuai harapan (4 asersi lulus, 46 ms) | Pass |
| INT-TC04 | AK-04, AK-05, AK-03 | Normal: HalamanKelolaMateri → MateriController → MateriAksara + KontenMateri → Basis Data + PenyimpananBerkas | Tim Materi POST /kelola/materi dengan berkas mp3, terbitkan, lalu Pelajar GET /materi/{id}/audio | Materi, konten, dan berkas tersimpan; audio dapat diputar (200) | Sesuai harapan (4 asersi lulus, 37 ms) | Pass |
| INT-TC05 | AK-04, AK-05 | Negatif: HalamanKelolaMateri → MateriController → PenyimpananBerkas | POST /kelola/materi dengan berkas .exe | Galat "Jenis berkas tidak didukung"; tidak ada materi dan berkas tersimpan (UC06 Alternatif 1) | Sesuai harapan (4 asersi lulus, 20 ms) | Pass |
| INT-TC06 | AK-06, AK-03, AK-10 | Normal: HalamanLatihan → LatihanController → AkurasiGoresan → RiwayatLatihan + MotivasiBelajar → Basis Data | Buka soal menulis, kirim goresan benar, POST /latihan/selesai, buka /latihan/hasil dan /progres | Riwayat berhasil tersimpan; poin 10, streak 1; halaman hasil dan progres menampilkan data yang sama | Sesuai harapan (10 asersi lulus, 124 ms) | Pass |
| INT-TC07 | AK-06, AK-03 | Normal: HalamanLatihan (non-kanvas) → LatihanNonKanvasController → PenilaiJawaban → RiwayatLatihan → Basis Data | Mulai latihan bunyi, jawab semua soal dengan kunci yang benar | Halaman hasil tampil; riwayat mode bunyi tersimpan | Sesuai harapan (4 asersi lulus, 54 ms) | Pass |
| INT-TC08 | AK-07, AK-03 | Normal: HalamanFeedback → FeedbackController → DataFeedback → Basis Data | POST /feedback kategori fitur | Redirect dengan pesan "Feedback terkirim"; baris data_feedback status baru | Sesuai harapan (4 asersi lulus, 19 ms) | Pass |
| INT-TC09 | AK-07, AK-08 | Negatif: FeedbackController → DataFeedback → Basis Data → LogAplikasi | Tabel data_feedback tidak tersedia saat POST /feedback | Isian dipertahankan dengan pesan gagal; galat tertulis di berkas log aplikasi (UC09 Alternatif 1, KNF01) | Sesuai harapan (4 asersi lulus, 29 ms) | Pass |
| INT-TC10 | AK-09, AK-03 | Normal: HalamanKelasPelajar → KelasPelajarController → KeanggotaanKelas + TugasKelas + PengumpulanTugas + PenyelesaianTugas → Basis Data | Pengajar membuat kelas dan menerbitkan tugas (komponen materi), Pelajar bergabung dengan kode, menyelesaikan komponen, lalu mengumpulkan | Keanggotaan aktif; penyelesaian_tugas berstatus tepat_waktu | Sesuai harapan (6 asersi lulus, 60 ms) | Pass |
| INT-TC11 | AK-09, AK-03 | Negatif: HalamanKelasPelajar → KelasPelajarController → PengumpulanTugas → Basis Data | Pelajar mengumpulkan tugas sebelum komponen wajib selesai | Galat pengumpulan; tidak ada baris penyelesaian_tugas | Sesuai harapan (3 asersi lulus, 23 ms) | Pass |
| INT-TC12 | AK-10, AK-03 | Normal: HalamanKelolaKelas → PemantauanKelasController → ProgresController → RiwayatLatihan → Basis Data | Pengajar membuka GET /pemantauan/{idKelas}?pelajar={id} untuk anggota yang punya riwayat | 200; nama anggota dan ringkasan progres tampil | Sesuai harapan (2 asersi lulus, 28 ms) | Pass |
| INT-TC13 | AK-11 | Negatif: Middleware CekPeran → Controller (hak akses lintas peran) | Pelajar membuka /kelola/materi dan /pengajar/kelas; Pengajar membuka /pemantauan kelas milik Pengajar lain; tamu membuka /progres | 403, 403, 403, dialihkan ke /masuk | Sesuai harapan (5 asersi lulus, 27 ms) | Pass |
| INT-TC14 | AK-10, AK-03 | Normal: HalamanProgres → ProgresController → MotivasiBelajar (scoreboard) → Basis Data | Dua anggota kelas dengan poin 30 dan 10, scoreboard aktif; anggota membuka GET /progres?kelas={id} | Papan peringkat tampil dengan urutan poin; email tidak tampil | Sesuai harapan (3 asersi lulus, 30 ms) | Pass |

<br>

## 4.4 Pengujian Use Case

### 4.4.1 Use Case Melakukan Pendaftaran Akun

**ID Use Case:** UC-01  
**Nama Use Case:** Melakukan Pendaftaran Akun

Tabel 4.4.1. Hasil Pengujian Use Case UC-01

| ID Pengujian | ID KF | Skenario | Prosedur Pengujian | Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| UC01-TC01 | KF01, KF04, KF05, KF06, KF07 | Normal | 1. Buka halaman Daftar (I-01).<br>2. Pilih peran Pelajar.<br>3. Isi email, username, kata sandi.<br>4. Centang persetujuan T&C.<br>5. Tekan "Daftar". | Peran: Pelajar<br>Email: dewi.e2e@ngaksara.test<br>Username: dewi_e2e<br>Sandi: Rahasia123 | Akun tersimpan dengan sandi ter-hash dan pengguna diarahkan ke halaman Masuk dengan pesan berhasil. | Akun tersimpan, diarahkan ke /masuk; password_hash diawali $2y$12$. Bukti: Gambar UC01-TC01. | Pass |
| UC01-TC02 | KF04 | Alternatif 1: Kredensial tidak valid | 1. Buka halaman Daftar.<br>2. Isi email yang sudah terdaftar.<br>3. Centang T&C lalu tekan "Daftar". | Email: pelajar@ngaksara.test (sudah ada)<br>Username: sari_baru<br>Sandi: Rahasia123 | Pendaftaran ditolak, tetap di halaman Daftar, pesan "Email sudah terdaftar" tampil dan isian lain dipertahankan. | Tetap di halaman daftar, pesan: "Email sudah terdaftar. Gunakan email lain atau masuk dengan akun yang ada.". Bukti: Gambar UC01-TC02. | Pass |

### 4.4.2 Use Case Mempelajari Materi

**ID Use Case:** UC-02  
**Nama Use Case:** Mempelajari Materi

Tabel 4.4.2. Hasil Pengujian Use Case UC-02

| ID Pengujian | ID KF | Skenario | Prosedur Pengujian | Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| UC02-TC01 | KF08, KF09 | Normal | 1. Masuk sebagai Pelajar.<br>2. Buka Materi, pilih aksara Jawa tingkat 1.<br>3. Pilih "Nglegena: ha na ca ra ka".<br>4. Tekan "Dengarkan". | Akun: pelajar@ngaksara.test<br>Filter: jawa, tingkat 1 | Daftar materi terbit tampil berurutan, isi materi tampil, audio diputar tanpa pesan galat. | Daftar & isi materi tampil; GET /materi/1/audio = 200, tanpa pesan galat audio. Bukti: Gambar UC02-TC01. | Pass |
| UC02-TC02 | KF09 | Alternatif 1: Materi belum tersedia | 1. Masuk sebagai Pelajar.<br>2. Buka Materi dengan filter aksara Sunda tingkat 3. | Filter: sunda, tingkat 3 | Halaman menampilkan keadaan kosong bahwa materi belum tersedia. | Halaman menampilkan keadaan kosong "materi belum tersedia". Bukti: Gambar UC02-TC02. | Pass |
| UC02-TC03 | KF08 | Alternatif 2: Audio gagal diputar | 1. Masuk sebagai Pelajar.<br>2. Buka materi 2 yang berkas audionya hilang.<br>3. Tekan "Dengarkan". | Materi: Nglegena: da ta sa wa la (path audio tidak ada di penyimpanan) | Pesan "Audio tidak dapat diputar. Periksa koneksi lalu tekan Dengarkan sekali lagi." tampil; isi materi tetap terbaca. | Berkas audio hilang -> pesan "Audio tidak dapat diputar..." tampil, materi tetap terbaca. Bukti: Gambar UC02-TC03. | Pass |

### 4.4.3 Use Case Mengerjakan Latihan Aksara

**ID Use Case:** UC-03  
**Nama Use Case:** Mengerjakan Latihan Aksara

Tabel 4.4.3. Hasil Pengujian Use Case UC-03

| ID Pengujian | ID KF | Skenario | Prosedur Pengujian | Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| UC03-TC01 | KF10, KF11, KF12, KF14 | Normal | 1. Masuk sebagai Pelajar.<br>2. Buka latihan menulis materi 1 (outline aktif).<br>3. Gambar aksara target mengikuti bentuk templat.<br>4. Tekan "Periksa", lalu "Selesaikan". | Akun: budi@ngaksara.test<br>Goresan: titik-titik garis tengah templat aksara target | Umpan balik "Bagus!" dengan akurasi ≥ 70%, lalu halaman hasil menampilkan skor, poin, dan streak. | Aksara ꦕ: Akurasi 100%.; hasil latihan, poin, dan streak tampil. Bukti: Gambar UC03-TC01. | Pass |
| UC03-TC02 | KF21 | Alternatif 1: Latihan bunyi | 1. Masuk sebagai Pelajar.<br>2. Buka latihan bunyi materi 1.<br>3. Pilih satu jawaban untuk setiap soal.<br>4. Tekan "Kirim jawaban". | Akun: wulan@ngaksara.test<br>Jawaban: opsi pertama setiap soal | Jawaban dinilai di server dan halaman hasil menampilkan benar/salah per soal beserta skor. | 5 soal bunyi dijawab; halaman hasil tampil dengan skor per soal. Bukti: Gambar UC03-TC02. | Pass |
| UC03-TC03 | KF22 | Alternatif 2: Latihan merangkai | 1. Buka latihan merangkai materi 1.<br>2. Tekan ubin aksara sesuai urutan yang dipilih.<br>3. Tekan "Kirim jawaban". | Urutan ubin: sesuai urutan tampil | Rangkaian dievaluasi; halaman hasil menandai posisi yang salah dan menampilkan skor. | Ubin dirangkai lalu dikirim; posisi salah ditandai di halaman hasil. Bukti: Gambar UC03-TC03. | Pass |
| UC03-TC04 | KF12 | Alternatif 3: Hasil gambar kurang akurat | 1. Buka latihan menulis materi 1.<br>2. Gambar garis pendek yang tidak sesuai bentuk aksara.<br>3. Tekan "Periksa".<br>4. Tekan "Ulangi". | Goresan: satu garis pendek di pojok kiri atas kanvas | Status "Belum tepat" dengan pesan "Tulis lebih dekat ke garis bantu."; tombol Ulangi menghapus goresan tanpa mengganti soal. | Umpan balik "Akurasi 0%. Tulis lebih dekat ke garis bantu."; tombol Ulangi menghapus goresan, soal tetap. Bukti: Gambar UC03-TC04. | Pass |

### 4.4.4 Use Case Meninjau Progres dan Motivasi

**ID Use Case:** UC-04  
**Nama Use Case:** Meninjau Progres dan Motivasi

Tabel 4.4.4. Hasil Pengujian Use Case UC-04

| ID Pengujian | ID KF | Skenario | Prosedur Pengujian | Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| UC04-TC01 | KF24 | Normal | 1. Masuk sebagai Pelajar yang memiliki riwayat.<br>2. Buka halaman Progres. | Akun: pelajar@ngaksara.test (riwayat dari seeder) | Penguasaan per materi, pola kesalahan, rekomendasi, streak, poin, dan badge tampil. | Penguasaan per materi, pola kesalahan, rekomendasi, streak, poin, dan badge tampil. Bukti: Gambar UC04-TC01. | Pass |
| UC04-TC02 | KF24 | Alternatif 1: Belum ada riwayat | 1. Masuk sebagai Pelajar baru.<br>2. Buka halaman Progres. | Akun: dewi.e2e@ngaksara.test (tanpa riwayat) | Halaman menampilkan keterangan belum ada riwayat dan rekomendasi materi awal. | Pelajar baru melihat pesan belum ada riwayat dan ajakan mulai belajar. Bukti: Gambar UC04-TC02. | Pass |
| UC04-TC03 | KF24 | Alternatif 2: Melihat papan peringkat | 1. Masuk sebagai anggota kelas.<br>2. Buka Progres dan pilih kelas "Aksara Jawa Dasar". | Kelas: K7M2QX (papan peringkat aktif) | Papan peringkat anggota aktif tampil urut poin, memakai nama tampilan tanpa email. | Papan peringkat anggota aktif tampil urut poin, tanpa email. Bukti: Gambar UC04-TC03. | Pass |

### 4.4.5 Use Case Mengikuti Pembelajaran Kelas

**ID Use Case:** UC-05  
**Nama Use Case:** Mengikuti Pembelajaran Kelas

Tabel 4.4.5. Hasil Pengujian Use Case UC-05

| ID Pengujian | ID KF | Skenario | Prosedur Pengujian | Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| UC05-TC01 | KF14, KF26, KF28 | Normal | 1. Masuk sebagai Pelajar baru.<br>2. Buka Kelas Saya, masukkan kode, tekan "Gabung".<br>3. Buka tugas, selesaikan komponen materi ("Selesai membaca") dan komponen latihan menulis.<br>4. Tekan "Kumpulkan". | Kode: k7m2qx (huruf kecil)<br>Akun: dewi.e2e@ngaksara.test | Pelajar menjadi anggota, kedua komponen tercatat selesai, tugas terkumpul dengan status "tepat waktu". | Bergabung dengan kode, komponen materi & latihan selesai, tugas terkumpul tepat_waktu. Bukti: Gambar UC05-TC01. | Pass |
| UC05-TC02 | KF26 | Alternatif 1: Sudah menjadi anggota | 1. Masuk sebagai anggota kelas.<br>2. Masukkan kode kelas yang sama lalu tekan "Gabung". | Kode: K7M2QX<br>Akun: pelajar@ngaksara.test | Pesan "Kamu sudah menjadi anggota kelas ini." tampil dan tidak ada keanggotaan ganda. | Pesan "Kamu sudah menjadi anggota kelas ini." dan diarahkan ke halaman kelas. Bukti: Gambar UC05-TC02. | Pass |
| UC05-TC03 | KF28 | Alternatif 2: Pengumpulan terlambat | 1. Masuk sebagai anggota.<br>2. Buka tugas yang tenggatnya sudah lewat.<br>3. Selesaikan komponen materi lalu tekan "Kumpulkan". | Tugas: tenggat kemarin<br>Akun: budi@ngaksara.test | Tugas tetap dapat dikumpulkan dan tersimpan dengan status "terlambat". | Tugas lewat tenggat tetap dapat dikumpulkan dan berstatus "terlambat". Bukti: Gambar UC05-TC03. | Pass |
| UC05-TC04 | KF26 | Alternatif 3: Kode tidak valid | 1. Masuk sebagai Pelajar.<br>2. Masukkan kode yang tidak terdaftar lalu tekan "Gabung". | Kode: ZZZZZZ | Pesan "Kode tidak ditemukan atau kelas tidak aktif." tampil; tidak ada keanggotaan baru. | Pesan "Kode tidak ditemukan atau kelas tidak aktif." tampil; isian tetap. Bukti: Gambar UC05-TC04. | Pass |

### 4.4.6 Use Case Memperbaharui Materi

**ID Use Case:** UC-06  
**Nama Use Case:** Memperbaharui Materi

Tabel 4.4.6. Hasil Pengujian Use Case UC-06

| ID Pengujian | ID KF | Skenario | Prosedur Pengujian | Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| UC06-TC01 | KF15, KF16 | Normal | 1. Masuk sebagai Tim Materi.<br>2. Buka Kelola Materi → Tambah.<br>3. Isi nama, jenis, tingkat, aturan dan contoh, unggah mp3.<br>4. Tekan "Simpan sebagai draf", lalu "Terbitkan". | Nama: Pasangan: ha na<br>Jawa, tingkat 2<br>Berkas: audio.mp3 (1 detik) | Materi tersimpan sebagai draf dengan audio, lalu berstatus terbit. | Materi tersimpan (audio materi/10/audio.mp3) lalu terbit. Bukti: Gambar UC06-TC01. | Pass |
| UC06-TC02 | KF15 | Alternatif 1: Penambahan gagal | 1. Masuk sebagai Tim Materi.<br>2. Isi formulir materi baru.<br>3. Unggah berkas .exe lalu simpan. | Berkas: virus.exe (66 byte) | Pesan "Jenis berkas tidak didukung" tampil; materi dan berkas tidak tersimpan. | Pesan "Jenis berkas tidak didukung"; materi tidak tersimpan. Bukti: Gambar UC06-TC02. | Pass |

### 4.4.7 Use Case Menyiapkan Pembelajaran Kelas

**ID Use Case:** UC-07  
**Nama Use Case:** Menyiapkan Pembelajaran Kelas

Tabel 4.4.7. Hasil Pengujian Use Case UC-07

| ID Pengujian | ID KF | Skenario | Prosedur Pengujian | Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| UC07-TC01 | KF25, KF27 | Normal | 1. Masuk sebagai Pengajar.<br>2. Buat kelas baru.<br>3. Buka Tugas, isi instruksi dan tenggat, tambah komponen, tekan "Terbitkan". | Nama kelas: Kelas Uji HIUPL<br>Tenggat: 3 hari lagi 21.00 | Kelas dibuat dengan kode bergabung 6 karakter dan tugas berstatus terbit. | Kelas dibuat dengan kode NJ528B; tugas terbit. Bukti: Gambar UC07-TC01. | Pass |
| UC07-TC02 | KF25 | Alternatif 1: Mengganti kode kelas | 1. Masuk sebagai pemilik kelas.<br>2. Tekan "Ganti kode" pada kartu kelas. | Kelas: Kelas Uji HIUPL | Kode baru tampil dan kode lama tidak berlaku. | Kode NJ528B diganti menjadi ANEKER; kode lama tidak berlaku. Bukti: Gambar UC07-TC02. | Pass |
| UC07-TC03 | KF02, KF25 | Alternatif 2: Mengeluarkan anggota | 1. Masuk sebagai pemilik kelas K7M2QX.<br>2. Buka Anggota, tekan "Keluarkan" pada Sari.<br>3. Konfirmasi "Ya, keluarkan". | Anggota: Sari | Dialog konfirmasi tampil; Sari tidak lagi aktif dan kehilangan akses kelas. | Dialog konfirmasi tampil; Sari dikeluarkan (status nonaktif). Bukti: Gambar UC07-TC03. | Pass |
| UC07-TC04 | KF24, KF25 | Alternatif 3: Mengaktifkan papan peringkat | 1. Masuk sebagai pemilik kelas.<br>2. Buka Anggota, nyalakan sakelar Papan peringkat. | Kelas: Kelas Uji HIUPL | Status papan peringkat kelas menjadi aktif. | Sakelar papan peringkat aktif; status_scoreboard = 1. Bukti: Gambar UC07-TC04. | Pass |

### 4.4.8 Use Case Memantau dan Menindaklanjuti Progres

**ID Use Case:** UC-08  
**Nama Use Case:** Memantau dan Menindaklanjuti Progres

Tabel 4.4.8. Hasil Pengujian Use Case UC-08

| ID Pengujian | ID KF | Skenario | Prosedur Pengujian | Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| UC08-TC01 | KF13, KF14 | Normal | 1. Masuk sebagai Pengajar.<br>2. Buka Pantau Progres kelas, periode semua, pilih Sari.<br>3. Tulis umpan balik lalu kirim. | Pesan: "Bagus, Sari! Latih lagi aksara ra ya." | Ringkasan kelas dan detail progres Sari tampil; pesan "Umpan balik terkirim" tampil. | Ringkasan kelas & detail Sari tampil; umpan balik terkirim. Bukti: Gambar UC08-TC01. | Pass |
| UC08-TC02 | KF13 | Alternatif 1: Belum ada hasil latihan | 1. Masuk sebagai Pengajar kelas baru.<br>2. Buka Pantau Progres kelas tersebut. | Kelas: Kelas Uji HIUPL (belum ada latihan) | Halaman menampilkan keterangan belum ada data progres. | Kelas tanpa hasil latihan menampilkan pesan belum ada data progres. Bukti: Gambar UC08-TC02. | Pass |
| UC08-TC03 | KF02, KF13 | Alternatif 2: Bukan anggota aktif | 1. Masuk sebagai Pengajar.<br>2. Buka Pantau Progres untuk Raka (status keluar). | Pelajar: raka@ngaksara.test | Pesan "Pengguna ini bukan anggota aktif kelas. Data progres tidak ditampilkan." tampil. | Pesan "Pengguna ini bukan anggota aktif kelas..." dan data progres tidak ditampilkan. Bukti: Gambar UC08-TC03. | Pass |

### 4.4.9 Use Case Menyampaikan Feedback

**ID Use Case:** UC-09  
**Nama Use Case:** Menyampaikan Feedback

Tabel 4.4.9. Hasil Pengujian Use Case UC-09

| ID Pengujian | ID KF | Skenario | Prosedur Pengujian | Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| UC09-TC01 | KF18, KF19, KF20 | Normal | 1. Masuk sebagai Pelajar.<br>2. Buka Feedback, pilih kategori Fitur, isi pesan.<br>3. Tekan "Kirim". | Pesan: "Tolong tambahkan latihan aksara Bali juga." | Pesan "Feedback terkirim. Matur nuwun!" tampil dan formulir kosong kembali. | Pesan "Feedback terkirim. Matur nuwun!" tampil; formulir kosong. Bukti: Gambar UC09-TC01. | Pass |
| UC09-TC02 | KF17, KF18 | Alternatif 1: Feedback gagal terkirim | 1. Masuk sebagai Pelajar dan isi formulir Feedback.<br>2. Tabel data_feedback dibuat tidak dapat ditulis (simulasi galat basis data).<br>3. Tekan "Kirim". | Pesan: "Audio materi kedua tidak bisa diputar." | Pesan "Feedback gagal terkirim ... Isi pesanmu tidak hilang." tampil, isian tetap, galat tercatat di log. | Basis data gagal menulis -> pesan gagal tampil dan isi pesan tidak hilang. Bukti: Gambar UC09-TC02. | Pass |

## 4.5 Pengujian Kebutuhan Non-Fungsional (KNF)


Tabel 4.5. Hasil Pengujian KNF

| ID Pengujian | ID KNF | Parameter | Target Terukur | Metode dan Alat | Hasil Aktual | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| KNF01-TC01 | KNF01 | Maintainability | Setiap kegagalan login dan kegagalan simpan tercatat di berkas log dengan waktu dan level; data pribadi (email) tidak ikut tertulis | PHPUnit `KnfTest::test_knf01_tc01_log`: picu login gagal dan galat basis data, lalu baca berkas log (`storage/logs`) | 2 entri tercatat: `[2026-11-20 01:51:58] testing.WARNING: Login gagal {"ip":"127.0.0.1"} ` dan `[2026-11-20 01:51:58] testing.ERROR: Feedback gagal disimpan {"id_akun":1} `; email tidak muncul | Pass |
| KNF01-TC02 | KNF01 | Maintainability | Penolakan akses pemantauan tercatat sebagai WARNING | PHPUnit `KnfTest::test_knf01_tc02_log_penolakan` | Entri "Pemantauan ditolak: bukan anggota aktif" tertulis di log | Pass |
| KNF02-TC01 | KNF02 | Security | Kolom `password_hash` berisi hash adaptif bersalt (bcrypt, cost ≥ 10), bukan teks asli | Daftar akun lewat HTTP lalu inspeksi tabel `akun_pengguna` dengan `password_get_info()` | Algoritma bcrypt, cost 12, awalan `$2y$12$`, panjang 60 karakter; teks "Rahasia123" tidak ditemukan di tabel | Pass |
| KNF02-TC02 | KNF02 | Security | Sandi sama menghasilkan hash berbeda; hash tidak dapat dipakai sebagai sandi | Dua akun dengan sandi sama, lalu login memakai nilai hash | Hash kedua akun berbeda (salt unik); login dengan hash ditolak | Pass |
| KNF04-TC01 | KNF04 | Response time | Jeda dari event `pointermove` sampai segmen goresan digambar di kanvas ≤ 50 ms | Chromium (Playwright) pada I-07: skrip pengukur membandingkan `event.timeStamp` dengan `performance.now()` saat `stroke()` dipanggil; 209 segmen dari UC03-TC01 dan UC05-TC01 | Median 12.5 ms, p95 13.6 ms, maks 13.9 ms | Pass |
| KNF05-TC01 | KNF05 | Reliability | Feedback valid tersimpan dan konfirmasi tampil | PHPUnit `KnfTest::test_knf05_tc01_terkirim` + UC09-TC01 di browser | Pesan "Feedback terkirim. Matur nuwun!" tampil; 1 baris tersimpan di `data_feedback` | Pass |
| KNF05-TC02 | KNF05 | Reliability | Saat penyimpanan gagal, pesan galat tampil dan isi pesan tidak hilang | PHPUnit `KnfTest::test_knf05_tc02_gagal` (tabel dihapus) + UC09-TC02 di browser (tabel diganti nama sementara) | Pesan "Feedback gagal terkirim. Periksa koneksi lalu coba lagi. Isi pesanmu tidak hilang." tampil, isian tetap | Pass |
| *KNF01-TC01* | *KNF01* | *Reliability* | *Tidak ada data hilang/ganda saat jaringan putus di tengah transaksi* | *Simulasi putus koneksi saat bayar, lalu cek status di DB* | *[Hasil aktual]* | *Pass/Fail* |
| *KNF02-TC01* | *KNF02* | *Security* | *Password tersimpan sebagai hash, bukan plain-text* | *Inspeksi tabel pengguna di DB* | *[...]* | *Pass/Fail* |
| *KNFXX-TC01* | *KNFXX* | *Response time* | *Halaman tampil ≤ 4 detik* | *Chrome DevTools, 5 kali ukur, ambil rata-rata* | *[Rata-rata ... detik, maks ... detik]* | *Pass/Fail* |
| *...* | *...* | *...* | *...* | *...* | *...* | *...* |

<br>

## 4.6 Tangkapan Layar dan Bukti Hasil Pengujian

Tabel 4.6. Bukti Hasil Pengujian

| ID Pengujian | Bukti Hasil Pengujian |
| :--- | :--- |
| C01-UT01 – C01-UT19 | <img src="./assets/bukti/unit-c01.png" alt="Keluaran PHPUnit C01" width="600"> |
| C01 (coverage) | <img src="./assets/bukti/coverage-C01.png" alt="Coverage C01" width="600"> |
| C02-UT01 – C02-UT18 | <img src="./assets/bukti/unit-C02.png" alt="Keluaran PHPUnit C02" width="600"> |
| C02 (coverage) | <img src="./assets/bukti/coverage-C02.png" alt="Coverage C02" width="600"> |
| C03-UT01 – C03-UT27 | <img src="./assets/bukti/unit-C03.png" alt="Keluaran PHPUnit C03" width="600"> |
| C03 (coverage) | <img src="./assets/bukti/coverage-C03.png" alt="Coverage C03" width="600"> |
| C04-UT01 – C04-UT04 | <img src="./assets/bukti/unit-C04.png" alt="Keluaran PHPUnit C04" width="600"> |
| C04 (coverage) | <img src="./assets/bukti/coverage-C04.png" alt="Coverage C04" width="600"> |
| C15-UT01 – C15-UT09 | <img src="./assets/bukti/unit-C15.png" alt="Keluaran PHPUnit C15" width="600"> |
| C15 (coverage) | <img src="./assets/bukti/coverage-C15.png" alt="Coverage C15" width="600"> |
| C16-UT01 – C16-UT18 | <img src="./assets/bukti/unit-C16.png" alt="Keluaran PHPUnit C16" width="600"> |
| C16 (coverage) | <img src="./assets/bukti/coverage-C16.png" alt="Coverage C16" width="600"> |
| C17-UT01 – C17-UT24 | <img src="./assets/bukti/unit-C17.png" alt="Keluaran PHPUnit C17" width="600"> |
| C17 (coverage) | <img src="./assets/bukti/coverage-C17.png" alt="Coverage C17" width="600"> |
| C18-UT01 – C18-UT09 | <img src="./assets/bukti/unit-C18.png" alt="Keluaran PHPUnit C18" width="600"> |
| C18 (coverage) | <img src="./assets/bukti/coverage-C18.png" alt="Coverage C18" width="600"> |
| C19-UT01 – C19-UT18 | <img src="./assets/bukti/unit-C19.png" alt="Keluaran PHPUnit C19" width="600"> |
| C19 (coverage) | <img src="./assets/bukti/coverage-C19.png" alt="Coverage C19" width="600"> |
| C20-UT01 – C20-UT22 | <img src="./assets/bukti/unit-C20.png" alt="Keluaran PHPUnit C20" width="600"> |
| C20 (coverage) | <img src="./assets/bukti/coverage-C20.png" alt="Coverage C20" width="600"> |
| C21-UT01 – C21-UT15 | <img src="./assets/bukti/unit-C21.png" alt="Keluaran PHPUnit C21" width="600"> |
| C21 (coverage) | <img src="./assets/bukti/coverage-C21.png" alt="Coverage C21" width="600"> |
| C22-UT01 – C22-UT18 | <img src="./assets/bukti/unit-C22.png" alt="Keluaran PHPUnit C22" width="600"> |
| C22 (coverage) | <img src="./assets/bukti/coverage-C22.png" alt="Coverage C22" width="600"> |
| C23-UT01 – C23-UT17 | <img src="./assets/bukti/unit-C23.png" alt="Keluaran PHPUnit C23" width="600"> |
| C23 (coverage) | <img src="./assets/bukti/coverage-C23.png" alt="Coverage C23" width="600"> |
| C24-UT01 – C24-UT15 | <img src="./assets/bukti/unit-C24.png" alt="Keluaran PHPUnit C24" width="600"> |
| C24 (coverage) | <img src="./assets/bukti/coverage-C24.png" alt="Coverage C24" width="600"> |
| C25-UT01 – C25-UT12 | <img src="./assets/bukti/unit-C25.png" alt="Keluaran PHPUnit C25" width="600"> |
| C25 (coverage) | <img src="./assets/bukti/coverage-C25.png" alt="Coverage C25" width="600"> |
| C26-UT01 – C26-UT10 | <img src="./assets/bukti/unit-C26.png" alt="Keluaran PHPUnit C26" width="600"> |
| C26 (coverage) | <img src="./assets/bukti/coverage-C26.png" alt="Coverage C26" width="600"> |
| INT-TC01 – INT-TC14 | <img src="./assets/bukti/integrasi.png" alt="Keluaran PHPUnit integrasi" width="600"> |
| UC01-TC01 | <img src="./assets/uc/UC01-TC1.png" alt="Gambar UC01-TC01" width="600"> |
| UC01-TC2 | <img src="./assets/uc/UC01-TC2.png" alt="Gambar UC01-TC02" width="600"> |
| UC02-TC01 | <img src="./assets/uc/UC02-TC01.png" alt="Gambar UC02-TC01" width="600"> |
| UC02-TC02 | <img src="./assets/uc/UC02-TC02.png" alt="Gambar UC02-TC02" width="600"> |
| UC02-TC03 | <img src="./assets/uc/UC02-TC03.png" alt="Gambar UC02-TC03" width="600"> |
| UC03-TC01 | <img src="./assets/uc/UC03-TC01.png" alt="Gambar UC03-TC01" width="600"><br><img src="./assets/uc/UC03-TC01-a.png" alt="UC03-TC01-a" width="600"> |
| UC03-TC02 | <img src="./assets/uc/UC03-TC02.png" alt="Gambar UC03-TC02" width="600"><br><img src="./assets/uc/UC03-TC02-a.png" alt="UC03-TC02-a" width="600"> |
| UC03-TC03 | <img src="./assets/uc/UC03-TC03.png" alt="Gambar UC03-TC03" width="600"><br><img src="./assets/uc/UC03-TC03-a.png" alt="UC03-TC03-a" width="600"> |
| UC03-TC04 | <img src="./assets/uc/UC03-TC04.png" alt="Gambar UC03-TC04" width="600"><br><img src="./assets/uc/UC03-TC04-a.png" alt="UC03-TC04-a" width="600"> |
| UC04-TC01 | <img src="./assets/uc/UC04-TC01.png" alt="Gambar UC04-TC01" width="600"> |
| UC04-TC02 | <img src="./assets/uc/UC04-TC02.png" alt="Gambar UC04-TC02" width="600"> |
| UC04-TC03 | <img src="./assets/uc/UC04-TC03.png" alt="Gambar UC04-TC03" width="600"> |
| UC05-TC01 | <img src="./assets/uc/UC05-TC01.png" alt="Gambar UC05-TC01" width="600"> |
| UC05-TC02 | <img src="./assets/uc/UC05-TC02.png" alt="Gambar UC05-TC02" width="600"> |
| UC05-TC03 | <img src="./assets/uc/UC05-TC03.png" alt="Gambar UC05-TC03" width="600"> |
| UC05-TC04 | <img src="./assets/uc/UC05-TC04.png" alt="Gambar UC05-TC04" width="600"> |
| UC06-TC01 | <img src="./assets/uc/UC06-TC01.png" alt="Gambar UC06-TC01" width="600"> |
| UC06-TC02 | <img src="./assets/uc/UC06-TC02.png" alt="Gambar UC06-TC02" width="600"> |
| UC07-TC01 | <img src="./assets/uc/UC07-TC01.png" alt="Gambar UC07-TC01" width="600"><br><img src="./assets/uc/UC07-TC01-a.png" alt="UC07-TC01-a" width="600"> |
| UC07-TC02 | <img src="./assets/uc/UC07-TC02.png" alt="Gambar UC07-TC02" width="600"> |
| UC07-TC03 | <img src="./assets/uc/UC07-TC03.png" alt="Gambar UC07-TC03" width="600"><br><img src="./assets/uc/UC07-TC03-a.png" alt="UC07-TC03-a" width="600"> |
| UC07-TC04 | <img src="./assets/uc/UC07-TC04.png" alt="Gambar UC07-TC04" width="600"> |
| UC08-TC01 | <img src="./assets/uc/UC08-TC01.png" alt="Gambar UC08-TC01" width="600"> |
| UC08-TC02 | <img src="./assets/uc/UC08-TC02.png" alt="Gambar UC08-TC02" width="600"> |
| UC08-TC03 | <img src="./assets/uc/UC08-TC03.png" alt="Gambar UC08-TC03" width="600"> |
| UC09-TC01 | <img src="./assets/uc/UC09-TC1.png" alt="Gambar UC09-TC01" width="600"> |
| UC09-TC02 | <img src="./assets/uc/UC09-TC2.png" alt="Gambar UC09-TC02" width="600"> |
| KNF01-TC01, KNF01-TC02, KNF02-TC01, KNF02-TC02, KNF03-TC01, KNF05-TC01, KNF05-TC02 | <img src="./assets/bukti/knf.png" alt="Keluaran PHPUnit KNF" width="600"> |
| KNF03-TC02, KNF04-TC01 | <img src="./assets/bukti/KNF03-TC02.png" alt="Hasil ukur browser" width="600"> |
| Seluruh suite | <img src="./assets/bukti/suite.png" alt="Ringkasan seluruh test" width="600"> |

<br>

---


# BAB 5: Traceability

Tabel 5.1. Matriks Kerunutan KF terhadap Implementasi dan Pengujian

| KF ID | KNF Terkait | Use Case ID | Kelas Terkait | Implementasi File Terkait | Test Case (UC) | Test Case (Unit/Integrasi/KNF) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| KF01 | - | UC01 | C05 - HalamanPendaftaran<br>C15 - OtentikasiController | `src/resources/views/auth/daftar.blade.php`<br>`src/app/Http/Controllers/OtentikasiController.php` | UC01-TC01 | C15-UT01 s.d. C15-UT03, INT-TC01 |
| KF02 | - | UC01–UC09 | C01, C15–C20 | `src/app/Models/AkunPengguna.php`<br>`src/app/Support/HakAkses.php`<br>`src/app/Http/Middleware/CekPeran.php` | UC07-TC03, UC08-TC03 | C01-UT11 s.d. C01-UT13, C16-UT03, C17-UT12, C18-UT03, C20-UT02, C20-UT18, INT-TC13 |
| KF03 | - | UC01–UC09 | C06 - HalamanLogin<br>C15 - OtentikasiController | `src/resources/views/auth/masuk.blade.php`<br>`src/app/Http/Controllers/OtentikasiController.php` | UC02-TC01 s.d. UC09-TC02 (langkah masuk lewat I-02) | C15-UT07 s.d. C15-UT09, INT-TC01, INT-TC02 |
| KF04 | - | UC01 | C01 - AkunPengguna<br>C15 - OtentikasiController | `src/app/Http/Controllers/OtentikasiController.php`<br>`src/app/Http/Requests/DaftarRequest.php` | UC01-TC01, UC01-TC02 | C15-UT01 s.d. C15-UT09, C01-UT05 s.d. C01-UT10, INT-TC02 |
| KF05 | KNF02 | UC01 | C01 - AkunPengguna<br>C15 - OtentikasiController | `src/app/Http/Controllers/OtentikasiController.php`<br>`src/app/Models/AkunPengguna.php` | UC01-TC01 | C15-UT04, C01-UT08 s.d. C01-UT10, KNF02-TC01, KNF02-TC02 |
| KF06 | - | UC01 | C05 - HalamanPendaftaran | `src/resources/views/auth/daftar.blade.php` | UC01-TC01 | - |
| KF07 | - | UC01 | C05 - HalamanPendaftaran<br>C15 - OtentikasiController | `src/app/Http/Requests/DaftarRequest.php` | UC01-TC01 | C15-UT03 |
| KF08 | - | UC02, UC03 | C13 - HalamanMateri<br>C17 - MateriController<br>C21 - KontenMateri | `src/app/Http/Controllers/MateriController.php`<br>`src/app/Models/KontenMateri.php`<br>`src/resources/views/materi/show.blade.php` | UC02-TC01, UC02-TC03 | C17-UT04 s.d. C17-UT09, C21-UT01 s.d. C21-UT06, INT-TC03 |
| KF09 | KNF03 | UC02 | C02 - MateriAksara<br>C13 - HalamanMateri<br>C17 - MateriController | `src/app/Models/MateriAksara.php`<br>`src/resources/views/materi/index.blade.php` | UC02-TC01, UC02-TC02 | C02-UT04 s.d. C02-UT06, C17-UT01 s.d. C17-UT03, INT-TC03, KNF03-TC01, KNF03-TC02 |
| KF10 | - | UC03 | C08 - HalamanLatihan<br>C16 - LatihanController | `src/resources/views/latihan/menulis.blade.php`<br>`src/resources/js/latihan/kanvas-menulis.js` | UC03-TC01 | C16-UT01 s.d. C16-UT03 |
| KF11 | KNF04 | UC03 | C08 - HalamanLatihan | `src/resources/js/latihan/kanvas-menulis.js` | UC03-TC01, UC03-TC04 | KNF04-TC01 |
| KF12 | - | UC03 | C16 - LatihanController | `src/app/Latihan/AkurasiGoresan.php`<br>`src/app/Http/Controllers/LatihanController.php` | UC03-TC01, UC03-TC04 | C16-UT04 s.d. C16-UT09 |
| KF13 | - | UC08 | C12 - HalamanKelolaKelas<br>C19 - ProgresController<br>C20 - KelasController | `src/app/Http/Controllers/PemantauanKelasController.php`<br>`src/app/Http/Controllers/ProgresController.php` | UC08-TC01 s.d. UC08-TC03 | C19-UT10 s.d. C19-UT12, C20-UT13, C20-UT14, INT-TC12 |
| KF14 | - | UC03–UC05, UC08 | C03 - RiwayatLatihan<br>C16 - LatihanController | `src/app/Models/RiwayatLatihan.php`<br>`src/app/Services/Latihan/SelesaikanLatihan.php` | UC03-TC01, UC05-TC01, UC08-TC01 | C03-UT01 s.d. C03-UT06, C16-UT13 s.d. C16-UT15, INT-TC06 |
| KF15 | - | UC06 | C09 - HalamanKelolaMateri<br>C17 - MateriController<br>C21 - KontenMateri | `src/app/Http/Controllers/MateriController.php`<br>`src/app/Models/KontenMateri.php` | UC06-TC01, UC06-TC02 | C17-UT10 s.d. C17-UT15, C21-UT10 s.d. C21-UT12, INT-TC04, INT-TC05 |
| KF16 | - | UC06 | C02 - MateriAksara<br>C17 - MateriController<br>C21 - KontenMateri | `src/app/Http/Controllers/MateriController.php`<br>`src/app/Models/MateriAksara.php` | UC06-TC01 | C17-UT16 s.d. C17-UT20, C17-UT24, C02-UT13 s.d. C02-UT15, C21-UT07 s.d. C21-UT09, C21-UT13 s.d. C21-UT15 |
| KF17 | KNF01 | UC01–UC09 | C15–C20 | `src/config/logging.php`<br>`src/storage/logs/laravel.log` | UC09-TC02 | C18-UT06, INT-TC09, KNF01-TC01, KNF01-TC02 |
| KF18 | KNF05 | UC09 | C10 - HalamanFeedback<br>C18 - FeedbackController | `src/resources/views/feedback/index.blade.php`<br>`src/app/Http/Controllers/FeedbackController.php` | UC09-TC01, UC09-TC02 | C18-UT01 s.d. C18-UT03, INT-TC08 |
| KF19 | KNF05 | UC09 | C04 - DataFeedback<br>C18 - FeedbackController | `src/app/Http/Controllers/FeedbackController.php`<br>`src/app/Models/DataFeedback.php` | UC09-TC01 | C18-UT04 s.d. C18-UT06, C04-UT01 s.d. C04-UT04, INT-TC08 |
| KF20 | KNF05 | UC09 | C04 - DataFeedback<br>C10 - HalamanFeedback<br>C18 - FeedbackController | `src/app/Http/Controllers/FeedbackController.php` | UC09-TC01 | C18-UT07 s.d. C18-UT09, KNF05-TC01, KNF05-TC02 |
| KF21 | - | UC03 | C16 - LatihanController | `src/app/Http/Controllers/LatihanNonKanvasController.php`<br>`src/app/Services/Latihan/PenilaiJawaban.php` | UC03-TC02 | C16-UT10 s.d. C16-UT12, INT-TC07 |
| KF22 | - | UC03 | C16 - LatihanController | `src/app/Services/Latihan/PenilaiJawaban.php` | UC03-TC03 | C16-UT10 s.d. C16-UT12 |
| KF23 | - | UC03 | C16 - LatihanController | `src/app/Services/Latihan/PenilaiJawaban.php`<br>`src/resources/views/latihan/transliterasi.blade.php` | - | C16-UT10 s.d. C16-UT12 |
| KF24 | - | UC04, UC07 | C14 - HalamanProgres<br>C19 - ProgresController<br>C22 - MotivasiBelajar | `src/app/Http/Controllers/ProgresController.php`<br>`src/app/Models/MotivasiBelajar.php`<br>`src/resources/views/progres/index.blade.php` | UC04-TC01 s.d. UC04-TC03, UC07-TC04 | C19-UT01 s.d. C19-UT09, C19-UT13 s.d. C19-UT18, C22-UT01 s.d. C22-UT18, INT-TC14 |
| KF25 | - | UC07 | C12 - HalamanKelolaKelas<br>C20 - KelasController<br>C23 - KelasBelajar | `src/app/Http/Controllers/KelasPengajarController.php`<br>`src/app/Models/KelasBelajar.php` | UC07-TC01 s.d. UC07-TC04 | C23-UT01 s.d. C23-UT17, C20-UT01, C20-UT02, C20-UT05 s.d. C20-UT10, C20-UT17, C20-UT18, INT-TC10 |
| KF26 | - | UC05 | C11 - HalamanKelasPelajar<br>C20 - KelasController<br>C24 - KeanggotaanKelas | `src/app/Http/Controllers/KelasPelajarController.php`<br>`src/app/Models/KeanggotaanKelas.php` | UC05-TC01, UC05-TC02, UC05-TC04 | C24-UT01 s.d. C24-UT15, C20-UT03, C20-UT04, INT-TC10 |
| KF27 | - | UC07 | C12 - HalamanKelolaKelas<br>C20 - KelasController<br>C25 - TugasKelas | `src/app/Http/Controllers/KelasPengajarController.php`<br>`src/app/Models/TugasKelas.php` | UC07-TC01 | C25-UT01 s.d. C25-UT12, C20-UT11, C20-UT12, INT-TC10 |
| KF28 | - | UC05 | C11 - HalamanKelasPelajar<br>C20 - KelasController<br>C26 - PenyelesaianTugas | `src/app/Http/Controllers/KelasPelajarController.php`<br>`src/app/Services/PengumpulanTugas.php`<br>`src/app/Models/PenyelesaianTugas.php` | UC05-TC01, UC05-TC03 | C26-UT01 s.d. C26-UT10, C20-UT19 s.d. C20-UT22, INT-TC10, INT-TC11 |

Tabel 5.2. Matriks Kerunutan KNF terhadap Pengujian

| ID KNF | Parameter | KF/UC Terkait | Test Case |
| :--- | :--- | :--- | :--- |
| KNF01 | Maintainability | KF17 / UC01–UC09 | KNF01-TC01, KNF01-TC02, INT-TC09 |
| KNF02 | Security | KF05 / UC01 | KNF02-TC01, KNF02-TC02, C15-UT04 |
| KNF03 | Response time | KF09 / UC02 | KNF03-TC01, KNF03-TC02 |
| KNF04 | Response time | KF11 / UC03 | KNF04-TC01 |
| KNF05 | Reliability | KF18–KF20 / UC09 | KNF05-TC01, KNF05-TC02, UC09-TC01, UC09-TC02 |