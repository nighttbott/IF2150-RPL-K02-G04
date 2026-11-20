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
Tuliskan uraian ringkas mengenai isi dokumen, tujuan pembuatan dokumen HIUPL ini, dan siapa saja target pembacanya.

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
Tuliskan aturan penomoran (ID) yang digunakan dalam dokumen ini. Gunakan pola ID yang **sama** dengan yang sudah dipakai pada dokumen-dokumen sebelumnya (SKPL, APL, DPPL), kecuali untuk ID pengujian yang baru diperkenalkan pada dokumen ini.

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
Tuliskan sistematika pembahasan dokumen ini secara ringkas dan runut bab demi bab, dengan maksimal 1 paragraf.

<br>

---

# BAB 2: Deskripsi Umum Perangkat Lunak

Ngaksara menyediakan pengelolaan akun, materi, latihan, progres, motivasi belajar, pembelajaran kelas, dan penerimaan feedback. Pengguna mengakses layanan melalui browser dengan hak akses sesuai peran. Data yang dikelola mencakup profil pengguna, materi, hasil latihan, indikator motivasi, kelas, keanggotaan, tugas, penyelesaian tugas, dan feedback sistem.

Latihan mencakup menggambar dengan atau tanpa outline, mencocokkan bunyi, merangkai aksara, dan transliterasi Latin ke aksara Jawa/Sunda atau sebaliknya. Transliterasi tidak menerjemahkan makna. Sistem mengevaluasi latihan dan menyediakan hasil untuk peninjauan progres serta rekomendasi materi. Papan peringkat hanya berlaku per kelas, menggunakan nama tampilan, dan dapat diaktifkan atau dinonaktifkan Pengajar.

Pelajar dan Pengajar menyampaikan feedback aplikasi melalui UC-09. Ngaksara tidak menyediakan pengiriman pesan antar pengguna. Pengajar memantau progres anggota kelas melalui UC-08 tanpa mengirim pesan melalui sistem.

Untuk implementasi awal, Ngaksara tidak menggunakan API atau layanan eksternal. Materi, audio, gambar, font, dan komponen antarmuka disediakan sebagai aset aplikasi pada server. Basis data dan penyimpanan berkas merupakan komponen internal. Distribusi audio dan gambar dari server kepada browser merupakan bagian operasi Ngaksara.

<p align="center">
<img alt="System Overview / Use Case Diagram" src="./assets/contoh-uc-diagram.webp" width="90%">
</p>
<p align="center">
<i>Gambar 2.1. Use Case Diagram [Nama P/L]</i>
</p>

<br>

---

# BAB 3: Implementasi

Bagian ini diisi dengan informasi tentang elemen P/L yang dikembangkan (*executable files*, *configuration files*, *data files*, dan sebagainya) serta perubahannya terhadap rancangan pada DPPL (jika ada).

## 3.1 Lingkungan Implementasi

Tuliskan *hardware* dan *software* yang digunakan untuk implementasi, mencakup *operating system*, bahasa pemrograman beserta versinya, *framework*/*library*, DBMS, *development tools*, dan *version control*. Lingkungan ini harus konsisten dengan **Subbab 2.1 DPPL**; jelaskan jika ada perubahan. Cantumkan juga *framework* dan alat pengujian yang dipakai.

Tabel 3.1. Lingkungan Implementasi

| Aspek | Keterangan |
| :--- | :--- |
| *Hardware* | *[Spesifikasi perangkat keras]* |
| *Operating System* | *[Nama dan versi OS]* |
| *Bahasa Pemrograman* | *[Bahasa dan versi]* |
| *Framework/Library* | *[Daftar framework/library beserta versi]* |
| *DBMS* | *[Nama dan versi DBMS]* |
| *Development Tools* | *[IDE, version control, tools lain]* |
| *Testing Tools* | *[Framework unit test, alat ukur performa, alat coverage]* |

## 3.2 Implementasi Modul

Daftar modul/kelas yang **TELAH** diimplementasikan. Gunakan nama dan ID kelas yang sama dengan DPPL, dan konsisten dengan Tabel B.5 pada dokumen Implementasi (Tugas 9).

Tabel 3.2. Implementasi Modul

| No | ID Kelas | Nama Modul/Kelas | Nama File Fisik | Nama File Executable |
| :--- | :--- | :--- | :--- | :--- |
| 1 | *[C01]* | *[Nama modul/kelas]* | *[path/ke/file]* | *[Nama file executable, atau - jika tidak ada]* |
| 2 | *[C02]* | *[Nama modul/kelas]* | *[path/ke/file]* | *[...]* |
| ... | *...* | *...* | *...* | *...* |

## 3.3 Implementasi Antarmuka

Daftar antarmuka yang telah diimplementasikan. Gunakan ID dan nama layar yang sama dengan ID dan nama layar pada tugas DPPL.

Tabel 3.3. Implementasi Antarmuka

| No | ID Antarmuka | Nama Antarmuka | Nama File Fisik | Nama File Executable |
| :--- | :--- | :--- | :--- | :--- |
| 1 | *[I-01]* | *[Nama layar]* | *[path/ke/file]* | *[Nama file executable, atau - jika tidak ada]* |
| 2 | *[I-02]* | *[Nama layar]* | *[path/ke/file]* | *[...]* |
| ... | *...* | *...* | *...* | *...* |

## 3.4 File Lain

Daftar file lain yang dibuat selain file kelas dan antarmuka, misalnya file *utility*, *configuration*, *database schema/seed*, *file test*, atau *data*.

Tabel 3.4. File Lain

| No | Nama File | Keterangan |
| :--- | :--- | :--- |
| 1 | *[Nama file]* | *[Fungsi file]* |
| ... | *...* | *...* |

<br>

---

# BAB 4: Hasil Pengujian

Bab ini melaporkan hasil pengujian P/L pada level unit, integrasi, dan sistem. Pengujian dilakukan pada tag release terakhir Tugas 9. Hasil aktual diisi berdasarkan pengamatan saat pengujian. Kesimpulan diisi `Pass` atau `Fail`, dan untuk `Fail` dilengkapi penyebab serta usulan perbaikan.

## 4.1 Strategi dan Kedalaman Pengujian Kelas

Setiap kelas perancangan pada Tabel 4.1 DPPL diklasifikasikan ke dalam tiga tingkat kedalaman pengujian. Tingkat **Mendalam** diberikan pada kelas yang memuat logika bisnis, perhitungan, validasi, aturan status, algoritma, integrasi eksternal, atau keamanan. Tingkat **Sedang** diberikan pada kelas yang hanya mengoordinasikan atau mendelegasikan proses. Tingkat **Tidak perlu** diberikan pada kelas tanpa logika, seperti *entity/DTO* dan kelas konfigurasi, yang teruji secara tidak langsung melalui test case use case. Seluruh kelas wajib dicantumkan beserta alasan penentuan tingkatnya.

Tabel 4.1.1. Identifikasi Tingkat Pengujian Kelas

| ID Kelas | Nama Kelas | Tingkat | Alasan | Test Case Terkait |
| :--- | :--- | :--- | :--- | :--- |
| *C02* | *Pesanan* | *Mendalam* | *Memuat `hitungTotal()` dan transisi status pembayaran* | *C02-UT01 s.d. C02-UT08* |
| *C03* | *Keranjang* | *Sedang* | *Delegasi sederhana ke Produk dan Pesanan* | *C03-UT01 s.d. C03-UT03, INT-TC02* |
| *C01* | *Pelanggan* | *Tidak perlu* | *Hanya menyimpan data akun, tanpa logika* | *-* |
| *[CXX]* | *[Nama kelas]* | *[Mendalam/Sedang/Tidak perlu]* | *[Alasan penentuan tingkat pengujian]* | *[ID test case atau -]* |
| *...* | *...* | *...* | *...* | *...* |

## 4.2 Pengujian Unit Kelas

Pengujian unit dilakukan pada kelas bertingkat Mendalam dan Sedang. Kelas Mendalam diuji pada seluruh operasi public dengan kasus normal, batas, dan negatif, serta mencapai *coverage* minimal 70%. Kelas Sedang diuji minimal dengan satu kasus normal dan satu kasus gagal pada operasi public utama.

Cara menjalankan unit test: *[tuliskan perintah, mis. `pytest src/tests/`]*.

Lokasi file test: *[path, mis. `src/tests/`]*.

### 4.2.1 Kelas [Nama Kelas 1]

**ID Kelas:** *[C02]*  
**Nama Kelas:** *[Pesanan]*  
**Tingkat:** *[Mendalam]*

Tabel 4.2.1. Hasil Pengujian Unit Kelas [C02]

| ID Pengujian | Operasi yang Diuji | Jenis Kasus | Prosedur/Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| *C02-UT01* | *`hitungTotal(): Decimal`* | *Normal* | *Pesanan berisi 2 item: 10.000 x 2 dan 5.000 x 1* | *Total = 25.000* | *[Hasil aktual]* | *Pass/Fail* |
| *C02-UT02* | *`hitungTotal(): Decimal`* | *Batas* | *Pesanan berisi tepat 1 item dengan jumlah 1* | *Total = harga item tersebut* | *[...]* | *Pass/Fail* |
| *C02-UT03* | *`hitungTotal(): Decimal`* | *Negatif* | *Pesanan tanpa item* | *Total = 0 atau melempar kesalahan sesuai rancangan* | *[...]* | *Pass/Fail* |
| *...* | *...* | *...* | *...* | *...* | *...* | *...* |

Khusus kelas **Mendalam**:

Tabel 4.2.1b. Ringkasan Cakupan Kelas [C02]

| Informasi | Keterangan |
| :--- | :--- |
| Daftar operasi public pada DPPL | *[op1(), op2(), ...]* |
| Operasi yang diuji | *[op1(), op2(), ...]* |
| ID Algoritma yang tercakup | *[Algo-001, ...]* |
| Coverage (wajib ≥ 70%) | *[% baris / % cabang, alat dan bukti pada 4.6]* |

### 4.2.2 Kelas [Nama Kelas 2]

*[Ulangi pola 4.2.1 untuk setiap kelas bertingkat Mendalam atau Sedang.]*

<br>

## 4.3 Pengujian Integrasi

Pengujian integrasi memeriksa interaksi antarkomponen yang melewati batas, yaitu View ke Controller lewat HTTP, Controller ke Model, aplikasi ke basis data, aplikasi ke penyimpanan berkas, dan aplikasi ke log. Daftar antarmuka mengacu pada relasi komponen Subbab 2.4 DPPL. Semua test memakai komponen nyata: rute dan middleware Laravel, controller, model Eloquent, basis data SQLite, disk penyimpanan Laravel (disk `local` palsu untuk berkas unggahan), dan berkas log sungguhan. Ngaksara tidak memakai sistem eksternal, sehingga tidak ada batas aplikasi–sistem eksternal.

Cara menjalankan: `php artisan test tests/Feature/Hiupl/IntegrasiTest.php`. Lokasi: `src/tests/Feature/Hiupl/IntegrasiTest.php`.

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

Pengujian use case dilakukan pada seluruh skenario normal dan alternatif sesuai Subbab 4.4 SKPL, dengan minimal satu test case untuk setiap skenario. Kolom ID KF menunjukkan KF yang diverifikasi oleh test case tersebut.

### 4.4.1 Use Case [Nama Use Case 1]

**ID Use Case:** *[UC01 sesuai SKPL]*  
**Nama Use Case:** *[Nama use case sesuai SKPL]*

Tabel 4.4.1. Hasil Pengujian Use Case [UC01]

| ID Pengujian | ID KF | Skenario | Prosedur Pengujian | Masukan | Hasil yang Diharapkan | Hasil Pengujian | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| *UC01-TC01* | *KF01* | *Normal* | *1. Buka halaman login.<br>2. Masukkan kredensial.<br>3. Klik tombol "Login".* | *User: rpl@gmail.com<br>Pass: 123456* | *Pengguna diarahkan ke Halaman Dashboard.* | *Pengguna diarahkan ke Halaman Dashboard sesuai hasil yang diharapkan.* | *Pass* |
| *UC01-TC02* | *KF01* | *Alternatif 1: [Nama skenario]* | *[Langkah-langkah pengujian]* | *[Data masukan]* | *[Hasil yang diharapkan]* | *[Hasil aktual]* | *[Pass/Fail. Jika Fail: penjelasan dan solusi]* |
| *...* | *...* | *...* | *...* | *...* | *...* | *...* | *...* |

### 4.4.2 Use Case [Nama Use Case 2]

*[Lanjutkan untuk seluruh use case dengan pola yang sama, hingga 4.4.N.]*

<br>
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

## 4.5 Pengujian Kebutuhan Non-Fungsional (KNF)

Setiap KNF pada Tabel 3.2 SKPL diuji secara terukur berdasarkan parameter yang telah didefinisikan. Setiap pengujian memuat target terukur, metode dan alat yang sesuai dengan parameter (misalnya Chrome DevTools untuk *response time* dan inspeksi basis data untuk *security*), serta hasil aktual berupa angka atau data. KNF yang belum dipenuhi atau tidak diimplementasikan dicantumkan beserta alasan dan rencana perbaikan.

Tabel 4.5. Hasil Pengujian KNF

| ID Pengujian | ID KNF | Parameter | Target Terukur | Metode dan Alat | Hasil Aktual | Kesimpulan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| *KNF01-TC01* | *KNF01* | *Reliability* | *Tidak ada data hilang/ganda saat jaringan putus di tengah transaksi* | *Simulasi putus koneksi saat bayar, lalu cek status di DB* | *[Hasil aktual]* | *Pass/Fail* |
| *KNF02-TC01* | *KNF02* | *Security* | *Password tersimpan sebagai hash, bukan plain-text* | *Inspeksi tabel pengguna di DB* | *[...]* | *Pass/Fail* |
| *KNFXX-TC01* | *KNFXX* | *Response time* | *Halaman tampil ≤ 4 detik* | *Chrome DevTools, 5 kali ukur, ambil rata-rata* | *[Rata-rata ... detik, maks ... detik]* | *Pass/Fail* |
| *...* | *...* | *...* | *...* | *...* | *...* | *...* |

<br>

## 4.6 Tangkapan Layar dan Bukti Hasil Pengujian

Tabel 4.6. Bukti Hasil Pengujian

| ID Pengujian | Bukti Hasil Pengujian |
| :--- | :--- |
| C01-UT01 – C01-UT19 | <img src="./assets/bukti/unit-C01.png" alt="Keluaran PHPUnit C01" width="600"> |
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
| UC01-TC01 | <img src="./assets/uc/UC01-TC01.png" alt="Gambar UC01-TC01" width="600"> |
| UC01-TC02 | <img src="./assets/uc/UC01-TC02.png" alt="Gambar UC01-TC02" width="600"> |
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
| UC09-TC01 | <img src="./assets/uc/UC09-TC01.png" alt="Gambar UC09-TC01" width="600"> |
| UC09-TC02 | <img src="./assets/uc/UC09-TC02.png" alt="Gambar UC09-TC02" width="600"> |
| KNF01-TC01, KNF01-TC02, KNF02-TC01, KNF02-TC02, KNF03-TC01, KNF05-TC01, KNF05-TC02 | <img src="./assets/bukti/knf.png" alt="Keluaran PHPUnit KNF" width="600"> |
| KNF03-TC02, KNF04-TC01 | <img src="./assets/bukti/knf-browser.png" alt="Hasil ukur browser" width="600"> |
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
