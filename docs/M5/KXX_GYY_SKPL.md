<h1>
IF2150 REKAYASA PERANGKAT LUNAK
<br>
TUGAS 5
<br>
SPESIFIKASI KEBUTUHAN PERANGKAT LUNAK (SKPL)
</h1>
<br>

## *Nama Perangkat Lunak*

### Untuk: *[Nama Asisten]*

Dipersiapkan oleh:
| Informasi | Keterangan |
| --- | --- |
| Kelas | *\[Kelas\]* |
| Kelompok | *\[Nomor Kelompok\]*  |

| NIM | Nama |
|---|---|
| *[NIM 1]* | *[Nama Anggota 1]* |
| *[NIM 2]* | *[Nama Anggota 2]* |
| *[NIM 3]* | *[Nama Anggota 3]* |
| *[NIM 4]* | *[Nama Anggota 4]* |
| *[NIM 5]* | *[Nama Anggota 5]* |
---

## Daftar Perubahan

| Revisi | Deskripsi |
| :--- | :--- |
| *A* | *Deskripsikan perubahan yang dilakukan dari dokumen sebelumnya pada dokumen ini. Jika tidak terdapat perubahan, harap kosongkan tabel.* |
| *B* |  |
| *C* |  |
| ... |  |

<br>

# BAB 1: Pendahuluan

## 1.1 Tujuan Penulisan Dokumen
Tuliskan dengan ringkas tujuan dokumen SKPL ini dibuat dan siapa saja yang akan menggunakan dokumen ini.

## 1.2 Lingkup Masalah
Tuliskan dengan ringkas nama aplikasi dan deskripsi singkatnya. Bagian ini maksimal berisi satu paragraf, dapat diringkas dari BAB 1 *Analisis Permasalahan* pada dokumen *Topic Brainstorming*.

## 1.3 Definisi, Istilah, dan Singkatan
Semua definisi dan singkatan yang digunakan dalam dokumen ini beserta penjelasannya.

Tabel 1.3. Definisi Istilah dan Singkatan

| Singkatan, Akronim, atau Istilah | Penjelasan |
| :--- | :--- |
| *P/L* | *Singkatan dari Perangkat Lunak, yaitu aplikasi yang memberikan perintah kepada komputer untuk menjalankan tugas tertentu.* |
| *SKPL* | *Singkatan dari Spesifikasi Kebutuhan Perangkat Lunak, yaitu dokumen yang merangkum kriteria-kriteria yang diperlukan untuk membangun aplikasi menjalankan tugasnya.* |
| *KF* | *Singkatan dari Kebutuhan Fungsional.* |
| *KNF* | *Singkatan dari Kebutuhan Non-Fungsional.* |
| *UC* | *Singkatan dari Use Case.* |
| *EARS* | *Easy Approach to Requirements Syntax, yaitu pola penulisan kebutuhan agar konsisten dan mudah diuji.* |
| *...* | *...* |

## 1.4 Aturan Penomoran
Tuliskan aturan penomoran (ID) yang digunakan dalam dokumen ini. Gunakan pola ID yang **sama** dengan yang sudah dipakai pada dokumen-dokumen sebelumnya, jangan membuat pola baru di dokumen ini.

Tabel 1.4. Aturan Penomoran

| Hal/Bagian | Penomoran | Keterangan |
| :--- | :--- | :--- |
| *Kebutuhan Fungsional* | *KFXX* | |
| *Kebutuhan Non-Fungsional* | *KNFXX* | |
| *Aktor* | *AXX* | |
| *Use Case* | *UCXX* | |
| *Kelas* | *CXX* | |
| *...* | *...* |

## 1.5 Referensi
Dokumentasi P/L yang dirujuk oleh dokumen ini. Referensi dapat berupa buku, panduan, ataupun dokumentasi lain yang dipakai dalam pengembangan P/L ini.

## 1.6 Deskripsi Umum Dokumen (Ikhtisar)
Tuliskan sistematika pembahasan dokumen SKPL ini secara runut (misalnya: BAB 2 membahas deskripsi umum P/L, BAB 3 membahas kebutuhan fungsional dan non-fungsional, dst).

---

# BAB 2: Deskripsi Perangkat Lunak

## 2.1 Deskripsi Umum Sistem
Bagian ini dapat disalin dari BAB 1.1 *Deskripsi Umum Sistem* pada dokumen *Requirement Gathering*, disesuaikan bila ada perubahan alur bisnis. Lengkapi dengan gambaran proses bisnis dalam bentuk *Activity Diagram* (boleh disalin dan diperbarui dari 3.3 *Model Proses Bisnis* pada dokumen *Topic Brainstorming*).

<p align="center">
<img alt="Contoh Activity Diagram" src="./assets/diagram/diagram-act-1.avif" width="70%">
</p>
<p align="center">
<i>Gambar 1. Contoh Activity Diagram Proses Bisnis</i>
</p>

## 2.2 Deskripsi Umum Perangkat Lunak
Diisi dengan deskripsi umum perangkat lunak untuk mendukung proses bisnis yang telah diuraikan pada sub-bab sebelumnya. Uraian harus menunjukkan lingkup perangkat lunak, mencakup keterkaitan perangkat lunak dengan sistem lain di luar (misalnya *Payment Gateway* atau layanan pihak ketiga lain yang dipakai).

*Contoh narasi:* "*[Nama P/L]* merupakan aplikasi *[deskripsi singkat]* yang berinteraksi dengan *Payment Gateway (dummy)* untuk memproses otorisasi pembayaran. Sistem menerima input dari *Pelanggan* melalui antarmuka aplikasi dan mengirimkan permintaan transaksi ke *Payment Gateway* setiap kali pelanggan melakukan checkout."

## 2.3 Pengguna dan Kebutuhan Pengguna Perangkat Lunak
Tuliskan seluruh jenis pengguna (*role*/aktor) yang terlibat dalam perangkat lunak (P/L), beserta kebutuhannya secara umum. Bagian ini dapat disalin dari 1.2 *Deskripsi Pengguna Perangkat Lunak* (dokumen Requirement Gathering) atau 3.1 *Identifikasi Aktor* (dokumen Use Case), pastikan sudah konsisten dengan aktor final yang dipakai di BAB 4.

| Pengguna | Kebutuhan |
| :--- | :--- |
| *Pelanggan* | *Pelanggan harus dapat memesan produk, mengelola keranjang, dan menyelesaikan pembayaran melalui sistem.* |
| *...* | *...* |

## 2.4 Batasan Perangkat Lunak
Batasan yang harus dituliskan, di antaranya:
1. *P/L harus memakai file data/API dari sistem lain (sebutkan, misal Payment Gateway dummy).*
2. *P/L harus memakai format data yang sama dengan sistem lain.*
3. *P/L harus berfungsi pada platform tertentu (misal: web browser modern, atau desktop Windows dan Linux).*
4. *...*

## 2.5 Lingkungan Operasi Perangkat Lunak
Spesifikasi *operating system* atau lingkungan yang dibutuhkan P/L untuk beroperasi. Bagian ini digunakan untuk memastikan pengguna memiliki spesifikasi yang cukup untuk menjalankan P/L. Misalnya mencakup komponen server, client, OS, DBMS, tetapi tidak menutupi kemungkinan komponen lain.

| Komponen | Spesifikasi |
| :--- | :--- |
| *Server* | *[contoh: Node.js v20, dijalankan pada layanan cloud]* |
| *Client* | *[contoh: Web Browser modern (Chrome, Firefox terbaru)]* |
| *DBMS* | *[contoh: PostgreSQL 15]* |
| *OS* | *[contoh: Cross-platform (Windows/Linux/MacOS) melalui browser]* |
| *...* | *...* |

---

# BAB 3: Deskripsi Kebutuhan Perangkat Lunak

## 3.1 Kebutuhan Fungsional (KF)

Tabel 3.1. Kebutuhan Fungsional

| ID KF | ID Kebutuhan | Penjelasan |
| --- | --- | --- |
| KF01 | R01 | Sistem harus menampilkan pilihan antarmuka pendaftaran akun untuk setiap opsi pengguna (pelajar dan pengajar) |
| KF02 | R01 | Sistem harus memberikan akses kontrol/privilege berbeda untuk setiap jenis pengguna |
| KF03 | R02 | Sistem harus menampilkan pilihan antarmuka log-in untuk setiap opsi pengguna (pelajar dan pengajar) |
| KF04 | R02 | Ketika pengguna melakukan pendaftaran akun, log in, atau logout, sistem harus memproses permintaan tersebut melalui pengecekan validitas kredensial |
| KF05 | R03 | Setelah pengguna membuat kata sandi, sistem harus menyimpan password dalam bentuk hash adaptif bersalt sebelum disimpan di database |
| KF06 | R04 | Sistem harus menampilkan dokumen Terms & Conditions kepada pengguna saat membuat akun |
| KF07 | R04 | Bila pengguna belum memberikan persetujuan eksplisit terhadap Terms & Conditions, sistem harus menolak menyelesaikan pembuatan akun |
| KF08 | R05 | Sistem harus menampilkan antarmuka materi dan latihan sesuai fitur pembelajaran yang dipilih pengguna |
| KF09 | R06 | Ketika pengguna berada di halaman utama, sistem harus menampilkan daftar materi secara terurut berdasarkan jenis aksara atau tingkat kesulitan |
| KF10 | R08 | Jika tersedia opsi menampilkan outline, sistem harus menampilkan tampilan antarmuka fitur menggambar aksara sesuai dengan opsi outline yang dipilih pengguna |
| KF11 | R08 | Ketika pengguna menggambar aksara, sistem harus merekam dan memproses urutan goresan secara berkelanjutan |
| KF12 | R09 | Ketika pengguna menggoreskan aksara di layar, sistem harus menilai akurasi goresan tersebut terhadap template dengan algoritma yang sesuai |
| KF13 | R11 | Sistem harus menampilkan tampilan antarmuka riwayat pengerjaan latihan pelajar bagi pengajar |
| KF14 | R12 | Setelah pelajar melakukan aktivitas pembelajaran, sistem harus mencatat dan menyimpan riwayat aktivitas tersebut agar dapat diakses pengajar |
| KF15 | R13 | Ketika Tim Materi mengunggah modul atau latihan, sistem harus menyimpan konten tersebut pada penyimpanan terpusat sebagai draf |
| KF16 | R14 | Ketika Tim Materi memilih tindakan yang diizinkan, sistem harus mengubah atau menghapus konten sesuai hak aksesnya |
| KF17 | R16 | Dalam interval waktu yang rutin, sistem harus mencatat dan menyimpan log aktivitas dan error yang terjadi |
| KF18 | R18, R28 | Saat Pelajar atau Pengajar memiliki keluhan atau feedback, sistem harus menerima feedback tersebut melalui form yang tersedia |
| KF19 | R18, R28 | Ketika sistem menerima feedback yang valid dari Pelajar atau Pengajar, sistem harus memvalidasi dan menyimpannya pada server |
| KF20 | R18, R28 | Setelah server menyimpan feedback, sistem harus memberikan konfirmasi kepada pengirim dan menyediakan data feedback untuk pengelolaan sistem |
| KF21 | R19 | Ketika Pelajar memulai latihan bunyi, sistem harus memutar atau merekam pelafalan dan mencocokkannya dengan aksara target |
| KF22 | R20 | Ketika Pelajar mengirim susunan aksara, sistem harus mengevaluasi urutan tersebut terhadap urutan yang benar |
| KF23 | R21 | Ketika Pelajar mengirim hasil transliterasi, sistem harus mengevaluasi kesesuaian aksara dan teks latin |
| KF24 | R22 | Ketika Pelajar membuka halaman progres, sistem harus menampilkan progres, streak, poin, lencana, dan scoreboard |
| KF25 | R23 | Ketika Pengajar mengirim data kelas yang valid, sistem harus membuat kelas dan menghasilkan kode bergabung unik |
| KF26 | R24 | Ketika Pelajar mengirim kode bergabung yang aktif, sistem harus menambahkan Pelajar sebagai anggota kelas |
| KF27 | R25 | Ketika Pengajar menerbitkan tugas, sistem harus menyimpan komponen tugas, instruksi, dan tenggat |
| KF28 | R26 | Ketika Pelajar membuka atau mengirim tugas, sistem harus menampilkan tugas aktif dan menyimpan status pengumpulan termasuk keterlambatan |


## 3.2 Kebutuhan Non-Fungsional (KNF)

Tabel 3.2. Kebutuhan Non-Fungsional

| ID KNF | ID Kebutuhan | Parameter | Deskripsi Kebutuhan |
| :--- | :--- | :--- | :--- |
| KNF01 | R16 | Maintainabiliy | Aplikasi harus otomatis mencatat seluruh masalah ke dalam log agar administrator mudah mencari letak masalahnya. |
| KNF02 | R03 | Security | Sistem harus mengamankan kata sandi pengguna dengan cara dienkripsi sebelum disimpan ke database. |
| KNF03 | R07 | Response time | Sistem harus bisa memuat dan menampilkan halaman daftar materi beserta gambarnya degan waktu kurang dari 3 detik saat koneksi internet stabil. |
| KNF04 | R10 | Response time | Saat pelajar berlatih menggambar aksara di layar, coretan tidak boleh delay lebih dari 50 milidetik agar terasa lancar dan nyaman. |
| KNF05 | R18 | Reliability | Fitur penerimaan feedback kendala harus berhasil terkirim dan tersimpan. Jika tidak, harus memberikan pesan error (jika internet terputus). |

---

# BAB 4: Pemodelan Use Case

## 4.1 Identifikasi Aktor

| Aktor | Deskripsi |
| :--- | :--- |
| Pelajar | Pengguna yang login ke aplikasi untuk belajar dan berlatih aksara Jawa dan Sunda. Pengguna ini dapat mengirim feedback kepada sistem jika mengalami kendala atau memiliki saran.|
| Pengajar | Pengguna yang login sebagai fasilitator pembelajaran. Pengguna ini memiliki akses untuk melihat rekam jejak latihan Pelajar, menganalisis kelemahan mereka, serta mengirim feedback kepada sistem.|
| Tim Materi | Pengguna yang bertindak sebagai pengelola konten materi. Tim materi membutuhkan akses untuk mengelola modul. |   

## 4.2 Identifikasi Use Case

| ID | Nama | Aktor | Tujuan | KF |
|---|---|---|---|---|
| UC-01 | Melakukan Pendaftaran Akun | Pelajar; Pengajar | Membuat akun sesuai peran untuk dapat menggunakan perangkat lunak. | KF01–KF07, KF17 |
| UC-02 | Mempelajari Materi | Pelajar | Memahami materi terbit untuk aksara dan tingkat yang dipilih. | KF02–KF04, KF08–KF09, KF17 |
| UC-03 | Mengerjakan Latihan Aksara | Pelajar | Menyelesaikan mode latihan dan memperoleh hasil serta umpan balik. | KF02–KF03, KF10–KF12, KF14, KF17, KF21–KF23 |
| UC-04 | Meninjau Progres dan Motivasi | Pelajar | Mengetahui perkembangan, materi yang perlu diulang, dan status motivasi. | KF02–KF03, KF14, KF17, KF24 |
| UC-05 | Mengikuti Pembelajaran Kelas | Pelajar | Bergabung ke kelas dan menyelesaikan tugas yang diberikan Pengajar. | KF02–KF03, KF14, KF17, KF26, KF28 |
| UC-06 | Memperbaharui Materi | Tim Materi | Memperbarui atau memperbaiki materi pembelajaran yang tersedia. | KF02–KF03, KF15–KF17, KF29 |
| UC-07 | Menyiapkan Pembelajaran Kelas | Pengajar | Membentuk kelas, mengendalikan akses, mengelola anggota, dan menyediakan tugas. | KF02–KF03, KF17, KF25, KF27 |
| UC-08 | Memantau dan Menindaklanjuti Progres | Pengajar | Memahami perkembangan anggota dan memberikan tindak lanjut. | KF02–KF04, KF13–KF14, KF17 |
| UC-09 | Menyampaikan Feedback | Pelajar; Pengajar | Menyampaikan feedback aplikasi, materi, atau pengalaman penggunaan melalui form. | KF02–KF03, KF17–KF20 |

## 4.3 Use Case Diagram
Salin ulang Use Case Diagram dari BAB 3.3 dokumen *Use Case & Scenario Use Case* atau *Class Diagram* (gunakan versi paling akhir/terbaru apabila terdapat perubahan).

<p align="center">
<img alt="Contoh Use Case Diagram" src="./assets/diagram/contoh-uc-diagram.webp" width="70%">
</p>
<p align="center">
<i>Gambar 2. Contoh Use Case Diagram</i>
</p>

## 4.4 Skenario Use Case

### 3.4.1 Skenario UC01

**Nama Use Case:** Melakukan Pendaftaran Akun

**Skenario Normal**

| No. | Aksi Aktor | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Pengguna memilih opsi pendaftaran akun (pelajar, pengajar) | Sistem menampilkan antarmuka opsi pemilihan jenis akun yang didaftarkan |
| 2 | Pengguna memasukkan kredensial akun (email, password, username) | Sistem menampilkan antarmuka pendaftaran akun dan memverifikasi kredensial yang digunakan |
| 3 | Pengguna membaca Terms & Conditions aplikasi | Sistem menerima afirmasi bahwa pengguna sudah membaca Terms & Conditions yang berlaku |

**Skenario Alternatif 1: Otorisasi Pembayaran Gagal**

| No. | Aksi Aktor | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Pengguna memilih opsi pendaftaran akun (pelajar, pengajar) | Sistem menampilkan antarmuka opsi pemilihan jenis akun yang didaftarkan |
| 2 | Pengguna memasukkan kredensial akun (email, password, username) | Sistem menampilkan antarmuka pendaftaran akun dan memverifikasi kredensial yang digunakan |
| 3 | Pengguna memastikan kredensial yang digunakan sesuai | Kembali ke langkah 2 Skenario Normal |

### 3.4.2 Skenario UC02

**Nama Use Case:** Mempelajari Materi

**Skenario Normal**

| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Memilih jenis aksara dan tingkat kesulitan | Sistem menampilkan daftar materi yang sesuai beserta status progres |
| 2 | Memilih satu materi | Sistem memuat paket materi yang sesuai dengan versi perangkat lunak |
| 3 | Membaca bentuk, aturan, dan contoh | Sistem menampilkan isi materi secara utuh |
| 4 | Meminta contoh bunyi pengucapan | Sistem memutar audio yang terkait serta sesuai dengan versi perangkat lunak |

**Skenario Alternatif 1: Materi belum tersedia untuk kombinasi yang dipilih**

| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Memilih jenis aksara dan tingkat kesulitan | Sistem tidak menemukan materi yang sesuai dan menampilkan pesan "materi belum tersedia untuk kombinasi ini" |
| 2 | Memilih kombinasi jenis aksara dan tingkat kesulitan lain | Sistem kembali ke langkah 1 Skenario Normal |

**Skenario Alternatif 2: Audio pengucapan gagal diputar**

| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Membaca bentuk, aturan, dan contoh | Sistem menampilkan isi materi secara utuh |
| 2 | Meminta contoh bunyi pengucapan | Sistem gagal memuat berkas audio (misal karena koneksi terputus) dan menampilkan pesan "audio tidak dapat diputar" |
| 3 | Meminta ulang contoh bunyi pengucapan | Sistem kembali ke langkah 4 Skenario Normal |

### 3.4.3 Skenario UC03

**Nama Use Case:** Mengerjakan Latihan Aksara

**Skenario Normal**

| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Memilih materi, lalu memilih salah satu mode latihan (menulis, mencocokkan bunyi, atau merangkai) | Sistem menyiapkan soal latihan sesuai mode dan target aksara yang dipilih |
| 2 | Mengerjakan latihan sesuai mode yang dipilih | Sistem menangkap dan menampilkan interaksi pelajar secara langsung |
| 3 | Mengirimkan jawaban/hasil latihan | Sistem mengevaluasi jawaban sesuai kriteria mode latihan dan menampilkan skor/status beserta feedback |
| 4 | Menyelesaikan latihan | Sistem menyimpan hasil ke riwayat latihan, serta memperbarui streak dan poin pelajar |

**Skenario Alternatif 1: Latihan mencocokkan bunyi**
 
| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Memilih mode mencocokkan bunyi | Sistem memutar audio pengucapan dan menampilkan pilihan aksara |
| 2 | Memilih aksara yang sesuai dengan bunyi yang diputar | Sistem menampilkan status jawaban (benar/salah) beserta penjelasannya |
| 3 | Menyelesaikan latihan | Sistem menyimpan hasil ke riwayat latihan serta memperbarui streak dan poin |
 
**Skenario Alternatif 2: Latihan merangkai aksara**
 
| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Memilih mode merangkai aksara | Sistem menampilkan komponen aksara dan tanda baca yang dapat disusun |
| 2 | Menyusun komponen menjadi suku kata/kata dan mengirimkannya | Sistem memvalidasi rangkaian berdasarkan aturan aksara yang berlaku dan menampilkan hasil |
| 3 | Menyelesaikan latihan | Sistem menyimpan hasil ke riwayat latihan serta memperbarui streak dan poin |
 
**Skenario Alternatif 3: Gambar aksara kurang akurat (mode menulis)**
 
| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Memilih mode menulis dan target aksara | Sistem menampilkan outline aksara sesuai materi yang dipilih |
| 2 | Menggambarkan aksara sesuai outline yang tampil | Sistem menampilkan goresan sesuai interaksi pelajar |
| 3 | Mengirimkan hasil gambar | Sistem menilai keakuratan penulisan berada di bawah ambang batas |
| 4 | Mengulang latihan pada aksara yang sama | Sistem menampilkan kembali outline aksara yang sama untuk diulang, tanpa menambah streak/poin baru |

### 3.4.4 Skenario UC04

**Nama Use Case:** Meninjau Progres dan Motivasi
 
**Skenario Normal**
 
| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Membuka halaman progres | Sistem menampilkan riwayat aktivitas dan tingkat penguasaan per materi |
| 2 | Memilih salah satu materi pada riwayat | Sistem menampilkan detail hasil latihan dan umpan balik untuk materi tersebut |
| 3 | Meminta rekomendasi materi lanjutan | Sistem menampilkan materi dengan tingkat penguasaan terendah yang masih relevan untuk dipelajari |
| 4 | Meninjau indikator motivasi (streak, poin, badge) | Sistem menampilkan streak harian, total poin, dan badge yang telah diperoleh pelajar |
 
**Skenario Alternatif 1: Belum ada riwayat pembelajaran**
 
| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Membuka halaman progres | Sistem menampilkan keadaan kosong dan merekomendasikan materi awal untuk mulai dipelajari |
| 2 | Membuka rekomendasi materi awal | Sistem mengarahkan pelajar ke halaman mempelajari materi yang direkomendasikan |
 
**Skenario Alternatif 2: Melihat papan peringkat kelas**
 
| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Membuka papan peringkat pada salah satu kelas yang diikuti | Sistem menampilkan urutan poin anggota kelas menggunakan nama tampilan masing-masing |

### 3.4.5 Skenario UC05

**Nama Use Case:** Mengikuti Pembelajaran Kelas
 
**Skenario Normal**
 
| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Memasukkan kode kelas yang diberikan pengajar | Sistem memvalidasi kode dan mendaftarkan pelajar sebagai anggota kelas |
| 2 | Membuka kelas yang diikuti | Sistem menampilkan daftar tugas beserta tenggat waktu dan status pengerjaannya |
| 3 | Membuka salah satu tugas sebelum tenggat | Sistem menampilkan komponen materi atau latihan yang harus diselesaikan |
| 4 | Menyelesaikan seluruh komponen wajib pada tugas | Sistem mencatat waktu penyelesaian dan menetapkan status "tepat waktu" |
 
**Skenario Alternatif 1: Pelajar sudah menjadi anggota kelas**
 
| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Membuka kelas tanpa memasukkan kode ulang | Sistem menampilkan daftar tugas kelas tanpa membuat keanggotaan baru |
 
**Skenario Alternatif 2: Tugas diselesaikan terlambat**
 
| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Membuka tugas yang telah melewati tenggat | Sistem menampilkan status "terlambat" pada tugas tersebut |
| 2 | Menyelesaikan seluruh komponen wajib | Sistem mencatat waktu penyelesaian dan menetapkan status "terlambat" |
 
**Skenario Alternatif 3: Kode kelas tidak valid**
 
| No. | Aksi Pelajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Memasukkan kode kelas | Sistem tidak menemukan kode aktif yang cocok dan menampilkan pesan "kode tidak valid atau tidak aktif" |
| 2 | Memasukkan kode kelas yang benar | Sistem kembali ke langkah 1 Skenario Normal |

### 3.4.6 Skenario UC06

**Nama Use Case:** Memperbaharui Materi

**Skenario Normal**

| No. | Aksi Aktor | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Tim materi memilih menu pengelolaan materi | Sistem menampilkan daftar materi yang dapat diedit |
| 2 | Tim materi memilih opsi menambahkan materi baru | Sistem mengarahkan pelanggan ke halaman menambahkan materi |
| 3 | Tim materi mengunggah file materi dan mengisi detail (nama, kategori, tingkat kesulitan) | Sistem memvalidasi dan menyimpan materi baru ke database terpusat |
| 4 | Tim materi menyimpan hasil perbaruan materi | Sistem menampilkan pesan "materi berhasil ditambahkan" |

**Skenario Alternatif 1: Penambahan materi gagal**

| No. | Aksi Aktor | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Tim materi memilih menu memperbarui materi | Sistem menampilkan ringkasan pesanan dan pilihan metode pembayaran |
| 2 | Tim materi menambahkan materi baru | Sistem menerima respons penambahan materi gagal (misal: jenis file tidak didukung website). Materi tidak berubah, sistem menampilkan pesan error dan meminta tim materi memilih ulang file materi baru |
| 3 | Tim materi menambahkan materi ulang | Sistem kembali ke langkah 2 Skenario Normal |

### 3.4.7 Skenario UC07
 
**Nama Use Case:** Menyiapkan Pembelajaran Kelas
 
**Skenario Normal**
 
| No. | Aksi Pengajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Membuat kelas baru dengan mengisi nama kelas | Sistem membuat kelas milik pengajar tersebut beserta satu kode kelas aktif yang unik |
| 2 | Membagikan kode kelas kepada pelajar | Sistem menampilkan daftar anggota yang bergabung |
| 3 | Memilih materi atau latihan dan menetapkan tenggat waktu | Sistem menyusun paket tugas sesuai materi/latihan dan tenggat yang dipilih |
| 4 | Menerbitkan tugas | Sistem menyediakan tugas tersebut hanya bagi anggota kelas yang bersangkutan |
 
**Skenario Alternatif 1: Mengganti kode kelas**
 
| No. | Aksi Pengajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Meminta pergantian kode kelas | Sistem menonaktifkan kode lama dan membuatkan satu kode aktif baru yang unik |
 
**Skenario Alternatif 2: Mengeluarkan anggota kelas**
 
| No. | Aksi Pengajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Memilih salah satu anggota untuk dikeluarkan | Sistem meminta konfirmasi pengeluaran anggota |
| 2 | Mengonfirmasi pengeluaran anggota | Sistem mengakhiri akses anggota tersebut ke kelas tanpa menghapus riwayat belajar pribadinya |
 
**Skenario Alternatif 3: Mengaktifkan papan peringkat kelas**
 
| No. | Aksi Pengajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Mengaktifkan papan peringkat pada pengaturan kelas | Sistem menyimpan status aktif dan menampilkan papan peringkat bagi anggota |

### 3.4.8 Skenario UC08

**Nama Use Case:** Memantau dan Menindaklanjuti Progres

**Skenario Normal**

| No. | Aksi Pengajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Membuka halaman pemantauan salah satu kelas yang diampu | Sistem menampilkan agregat tingkat penguasaan dan pola kesalahan umum anggota kelas |
| 2 | Memilih salah satu anggota kelas | Sistem menampilkan detail progres dan riwayat pengerjaan tugas anggota tersebut |
| 3 | Menuliskan umpan balik atau rekomendasi materi lanjutan | Sistem memeriksa isi pesan dan penerima yang dituju |
| 4 | Mengirimkan umpan balik | Sistem menyimpan pesan tersebut dan menyediakannya hanya bagi anggota yang dituju |

**Skenario Alternatif 1: Belum ada hasil belajar anggota**

| No. | Aksi Pengajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Membuka halaman pemantauan | Sistem menampilkan keadaan kosong beserta daftar anggota yang belum memiliki riwayat latihan |
| 2 | Mengirimkan rekomendasi materi awal kepada anggota tersebut | Sistem menyimpan rekomendasi dan menyediakannya bagi anggota yang dituju |

**Skenario Alternatif 2: Anggota yang dipilih bukan anggota aktif kelas**

| No. | Aksi Pengajar | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | Mencoba membuka progres pengguna yang bukan anggota kelas | Sistem menolak permintaan dan tidak menampilkan data progres pengguna tersebut |

### 3.4.9 Skenario UC09

**Nama Use Case:** Menyampaikan Feedback

**Skenario Normal**

| No. | Aksi Aktor | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | User (pelajar dan pengajar) memilih menu feedback di bagian samping pada menu utama | Sistem menampilkan form feedback dengan beberapa pertanyaan terbuka dan tertutup |
| 2 | User memilih pilihan feedback (performa/tampilan/fitur/materi) | Sistem menampilkan pilihan-pilihan feedback yang dapat diisi oleh user |
| 3 | User mengirim/submit feedback setelah mengisi | Sistem menampilkan pesan bahwa feedback berhasil terkirim |

**Skenario Alternatif 1: Feedback gagal terkirim (karena server down/kendala jaringan)**

| No. | Aksi Aktor | Reaksi Perangkat Lunak |
| :--- | :--- | :--- |
| 1 | User (pelajar dan pengajar) memilih menu feedback di bagian samping pada menu utama | Sistem menampilkan form feedback dengan beberapa pertanyaan terbuka dan tertutup |
| 2 | User memilih pilihan feedback (performa/tampilan/fitur/materi) | Sistem menampilkan pilihan-pilihan feedback yang dapat diisi oleh user |
| 3 | User mengirim/submit feedback setelah mengisi | Sistem menampilkan pesan bahwa feedback gagal terkirim |

---

# BAB 5: Pemodelan Kelas

## 5.1 Identifikasi Kelas
Salin ulang seluruh kelas yang telah diidentifikasi dari BAB 4.1 dokumen *Class Diagram*.

| ID Kelas | Nama Kelas | Deskripsi Kelas | ID Use Case |
| :--- | :--- | :--- | :--- |
| *C01* | *Pelanggan* | *Menyimpan data akun pelanggan yang membuat pesanan.* | *UC01, UC05* |
| *C02* | *Pesanan* | *Menyimpan data pesanan beserta status pembayarannya.* | *UC01, UC03, UC05* |
| *C03* | *Keranjang* | *Menyimpan sementara item yang dipilih sebelum checkout.* | *UC01, UC02* |
| *...* | *...* | *...* | *...* |

## 5.2 Diagram Kelas per Use Case
Salin ulang diagram kelas untuk setiap use case dari BAB 4.2 dokumen *Class Diagram*, lengkap dengan tabel atribut dan metode/operasinya.

### 5.2.1 Use Case UC01

**Nama Use Case:** *Memesan Produk*

<p align="center">
<img alt="Contoh Class Diagram" src="./assets/diagram/contoh-class-diagram.webp" width="70%">
</p>
<p align="center">
<i>Gambar 3. Contoh Diagram Kelas Use Case UC01</i>
</p>

| ID Kelas | Nama Kelas | Atribut | Metode/Operasi |
| :--- | :--- | :--- | :--- |
| *C02* | *Pesanan* | *idPesanan, total, status* | *buatPesanan(), hitungTotal()* |
| *C03* | *Keranjang* | *daftarItem* | *tambahItem(), checkout()* |
| *...* | *...* | *...* | *...* |

> Lanjutkan pola **5.2.x** untuk setiap use case pada 4.2.

## 5.3 Diagram Kelas Keseluruhan
Gabungkan seluruh kelas dan hubungan antarkelas dari BAB 4.3 dokumen *Class Diagram* menjadi satu diagram kelas keseluruhan. Pastikan tidak ada kelas yang terduplikasi atau tertinggal.

<p align="center">
<img alt="Contoh Class Diagram Keseluruhan" src="./assets/diagram/contoh-class-diagram.webp" width="70%">
</p>
<p align="center">
<i>Gambar 4. Contoh Diagram Kelas Keseluruhan</i>
</p>

| ID Kelas | Nama Kelas | Atribut | Metode/Operasi |
| :--- | :--- | :--- | :--- |
| *C01* | *Pelanggan* | *idPelanggan, nama, email* | *lihatRiwayatPesanan()* |
| *C02* | *Pesanan* | *idPesanan, total, status* | *hitungTotal(), perbaruiStatus()* |
| *...* | *...* | *...* | *...* |

---

# BAB 6: Traceability
Salin ulang tabel Traceability dari BAB 5 dokumen *Class Diagram*, cocokkan setiap Kebutuhan Fungsional, Use Case, dan Kelas yang saling terkait.

| ID Kelas | ID Use Case | ID KF |
| :--- | :--- | :--- |
| *C01* | *UC01, UC05* | *KF01, KF06* |
| *C02* | *UC01, UC03, UC05* | *KF01, KF02, KF05, KF06* |
| *C03* | *UC01, UC02* | *KF01, KF02* |
| *...* | *...* | *...* |

---

# Referensi
- Diagram UML: [https://www.drawio.com/](https://www.drawio.com/), [https://staruml.io/](https://staruml.io/)
