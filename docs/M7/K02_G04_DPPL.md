<h1>
IF2150 REKAYASA PERANGKAT LUNAK
<br>
TUGAS 5
<br>
DESKRIPSI PERANCANGAN PERANGKAT LUNAK (DPPL)
</h1>
<br>

## Ngaksara
<p align="center">
<img alt="Penerapan Arsitektur MVC pada Ngaksara" src="./assets/diagram/LOGO.png" width="70%">
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

## Daftar Perubahan

| Revisi | Deskripsi |
| :--- | :--- |
| *A* | *Menambahkan 2 eksepsi baru di UC5 yang belum ada di SKPL* |
| *B* |  |
| *C* |  |
| ... |  |

<br>

---

# BAB 1: Pendahuluan

## 1.1 Tujuan Penulisan Dokumen
Dokumen Deskripsi Perancangan Perangkat Lunak (DPPL) ini menjelaskan rancangan perangkat lunak Ngaksara sebagai kelanjutan dari dokumen SKPL dan APL. Dokumen ini mencakup lingkungan implementasi, komponen dan modul dalam arsitektur, realisasi setiap use case melalui sequence diagram dan diagram kelas, diagram kelas keseluruhan, serta kerunutan antara kelas dan use case.

Pengguna dokumen ini meliputi:
1. Kelompok Pengembang (Kelompok 4 Kelas K2): sebagai acuan dalam mengimplementasikan kode dan menyusun pengujian, sehingga perangkat lunak dibangun sesuai rancangan.
2. Pemelihara sistem di masa depan: sebagai sarana memahami struktur kelas dan alur interaksi antarkomponen dalam perangkat lunak
3. Asisten penilai (Amanda Aurellia Salsabilla) selaku pemangku kepentingan: sebagai dokumen evaluasi dan validasi kesesuaian rancangan dengan SKPL dan APL.

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

 Hal/Bagian | Penomoran | Keterangan |
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
| Gambar | Gambar N | Nomor urut gambar di seluruh dokumen. |

## 1.5 Referensi
1. Kelompok 4 K2, *Spesifikasi Kebutuhan Perangkat Lunak (SKPL) – Ngaksara*, Tugas 5 IF2150 Rekayasa Perangkat Lunak.
2. Kelompok 4 K2, *Arsitektur Perangkat Lunak (APL) – Ngaksara*, Tugas 6 IF2150 Rekayasa Perangkat Lunak.
3. Sommerville, I. (2016). *Software Engineering* (10th ed.). Pearson. Chapter 6: *Architectural Design*. https://software-engineering-book.com/slides/
4. Perangkat pembuat diagram: draw.io (https://www.drawio.com/), StarUML (https://staruml.io/), dan Mermaid (https://mermaid.js.org/).

## 1.6 Deskripsi Umum Dokumen (Ikhtisar)
Dokumen ini terdiri atas lima bab. Bab 1 menguraikan tujuan, lingkup masalah, istilah, aturan penomoran, dan referensi. Bab 2 memaparkan perancangan arsitektur, yaitu lingkungan implementasi, pola arsitektur acuan, identifikasi komponen, dan model arsitektur yang diturunkan dari APL. Bab 3 berisi realisasi use case UC-01 sampai UC-09 berupa identifikasi kelas, sequence diagram untuk setiap skenario, dan diagram kelas per use case. Bab 4 menyajikan diagram kelas perancangan keseluruhan beserta daftar atribut dan operasinya. Bab 5 memuat matriks kerunutan yang memetakan setiap kelas ke use case yang merealisasikannya.

<br>

---

# BAB 2: Perancangan Arsitektur

## 2.1 Rancangan Lingkungan Implementasi

Lingkungan implementasi pada Tabel 2.1 kami turunkan dari lingkungan operasi yang sudah ditetapkan pada dokumen APL. Bedanya, tabel ini tidak hanya menyebut tempat web Ngaksara akan berjalan, tetapi juga perangkat, bahasa, dan pustaka yang kami gunakan untuk membangunnya.

Tabel 2.1. Rancangan Lingkungan Implementasi

| Aspek | Rancangan |
| :--- | :--- |
| *Operating system* | Windows 11 64-bit dan Linux untuk pengembangan dan demonstrasi. Rencana jika dipublikasikan: Ubuntu Server 24.04 LTS dengan Nginx dan PHP-FPM 8.4. |
| DBMS | MySQL 8.4 dengan `utf8mb4`. Model mengakses basis data melalui Eloquent ORM bawaan Laravel. |
| Bahasa pemrograman | PHP 8.4 pada sisi server, JavaScript pada sisi browser (Canvas API dan Pointer Events), HTML dan CSS, serta SQL. |
| *Framework* dan pustaka | Laravel 13 untuk Model, Controller, dan routing. Blade dan Bootstrap 5.3 untuk View. |
| *Filing system* | Laravel Filesystem pada disk lokal server untuk materi, audio, gambar, dan template aksara. Dikelola oleh komponen `PenyimpananBerkas`. |
| Pencatatan log | Fasilitas log Laravel yang menulis log aktivitas dan error ke berkas log di server. Dikelola oleh komponen `LogAplikasi`. |
| *Development tools* | Composer untuk pustaka PHP, Git dan GitHub untuk pengelolaan kode, draw.io atau StarUML serta Mermaid untuk diagram, dan editor kode sesuai pilihan anggota kelompok. |
| *Client* | Chrome, Edge, atau Firefox versi stabil dengan JavaScript aktif. |

## 2.2 Style/Pattern Arsitektur Acuan

Ngaksara dibangun dengan arsitektur *client-server* yang dipadukan dengan pola *Model-View-Controller* (MVC). Pengguna mengakses Ngaksara sebagai web melalui browser, sementara pengolahan data berlangsung di server yang menjalankan satu proyek web berbasis Laravel. Di dalam proyek tersebut, pekerjaan dibagi ke tiga bagian. Model mengurus data dan aturan bisnis, misalnya akun, materi, hasil latihan, dan kelas. View adalah halaman yang dilihat Pelajar, Pengajar, dan Tim Materi. Controller berada di antara keduanya: komponen ini menerima permintaan pengguna, mengambil data yang diperlukan dari Model, lalu menyerahkan hasilnya ke View. Di luar ketiga lapisan tersebut, data Ngaksara tersimpan di `Database` (MySQL 8.4), berkas materi dan audio di `PenyimpananBerkas`, sedangkan log aktivitas dan error dicatat oleh `LogAplikasi`.

Pertimbangan utama kami memilih *client-server* adalah keterkaitan data antarperan. Hasil latihan seorang Pelajar tidak hanya ditampilkan kepada dirinya di halaman progres, tetapi juga perlu dapat dipantau oleh Pengajar kelasnya. Kebutuhan seperti ini lebih mudah dipenuhi jika data disimpan dan dikendalikan di satu tempat, termasuk pembatasan akses pada KF02 dan pencatatan riwayat pada KF14. MVC kami pilih karena alasan yang lebih praktis. Setiap lapisan memiliki tugas yang jelas sehingga pekerjaan mudah dibagi di antara anggota kelompok. Tampilan halaman latihan, misalnya, dapat diubah tanpa menyentuh cara riwayat belajar disimpan.

Ada satu bagian yang sengaja tidak menunggu server, yaitu goresan saat Pelajar menggambar aksara. Goresan langsung digambar oleh Canvas di browser agar tidak terasa tertunda, sesuai batas 50 milidetik pada KNF04. Server baru terlibat ketika jawaban dikirim, yaitu untuk menilai hasil akhir, menyimpannya ke riwayat, dan memperbarui streak serta poin. Hal yang menyangkut keamanan dan pemeliharaan, seperti pemeriksaan hak akses, *hashing* kata sandi (KF05 dan KNF02), serta pencatatan log (KF17 dan KNF01), juga tetap ditangani server.

<p align="center">
<img alt="Penerapan Arsitektur MVC pada Ngaksara" src="./assets/diagram/DiagramArsitektur.png" width="70%">
</p>
<p align="center">
<i>Gambar 1. Penerapan Arsitektur Client-Server dan MVC pada Ngaksara</i>
</p>

Pada Gambar 1, komponen dikelompokkan ke sisi *Client* dan sisi *Server*. View memang dirender oleh Blade di server, tetapi hasilnya dikirim ke browser sebagai *HTTP response*, sehingga pada diagram View ditempatkan di sisi *Client*. Setiap kali pengguna menekan tombol atau mengirim form, browser mengirim *HTTP request* ke Controller yang bersangkutan. Controller lalu meminta Model membaca atau mengubah data dan mencatat aktivitasnya ke `LogAplikasi`. Model sendiri yang berhubungan dengan `Database` dan `PenyimpananBerkas`. Arah hubungan ini tidak pernah dibalik: View tidak langsung mengakses Model, dan Model tidak memanggil Controller.

## 2.3 Identifikasi Komponen / Modul / Subsistem

Identifikasi komponen, modul, atau subsistem penyusun aplikasi berdasarkan *pattern* yang telah ditetapkan. Jelaskan tanggung jawab masing-masing komponen. Pengelompokan dapat mengikuti lapisan arsitektur atau fungsi/peran komponen dalam sistem.

Ambil dari **Tabel 2.1 dokumen APL**, lalu kelompokkan berdasarkan lapisan (Model/View/Controller). Kolom **Jenis** diisi sesuai pattern, misalnya View, Controller, Model, Service, Repository. Satu komponen merepresentasikan saatu tanggung jawab utama yang jelas,

Tabel 2.3. Identifikasi Komponen/Modul/Subsistem

**View**

| Nama Komponen/Modul/Subsistem | Jenis | Penjelasan |
| :--- | :--- | :--- |
| `HalamanPendaftaran` | View | Menampilkan form pendaftaran, pemilihan peran pengguna, dan persetujuan Terms & Conditions. |
| `HalamanLogin` | View | Menampilkan form untuk memasukkan kredensial login. |
| `HalamanUtama` | View | Menampilkan navigasi utama setelah pengguna berhasil masuk ke sistem. |
| `HalamanMateri` | View | Menampilkan antarmuka bagi Pelajar untuk memilih dan mempelajari materi, termasuk memutar audio pengucapan. |
| `HalamanLatihan` | View | Menampilkan antarmuka latihan menulis, mencocokkan bunyi, merangkai aksara, dan transliterasi. |
| `HalamanProgres` | View | Menampilkan riwayat, progres, rekomendasi materi, dan indikator motivasi belajar Pelajar. |
| `HalamanKelasPelajar` | View | Menampilkan antarmuka bagi Pelajar untuk bergabung ke kelas, melihat tugas, dan mengumpulkan tugas. |
| `HalamanKelolaKelas` | View | Menampilkan antarmuka bagi Pengajar untuk mengelola kelas dan memantau anggotanya. |
| `HalamanKelolaMateri` | View | Menampilkan antarmuka bagi Tim Materi untuk mengunggah dan menyunting materi. |
| `HalamanFeedback` | View | Menampilkan form untuk mengirim feedback aplikasi, materi, atau pengalaman penggunaan. |

**Controller**

| Nama Komponen/Modul/Subsistem | Jenis | Penjelasan |
| :--- | :--- | :--- |
| `OtentikasiController` | Controller | Memvalidasi pendaftaran dan login, serta memproses autentikasi pengguna. |
| `MateriController` | Controller | Mengambil, mengurutkan, memuat, dan memperbarui data materi. |
| `LatihanController` | Controller | Menyiapkan soal latihan, menilai jawaban, serta memperbarui riwayat dan motivasi belajar. |
| `ProgresController` | Controller | Mengolah riwayat dan progres belajar, menyiapkan rekomendasi materi serta papan peringkat. |
| `KelasController` | Controller | Memproses keanggotaan kelas, data kelas, tugas, dan pengambilan data kelas. |
| `FeedbackController` | Controller | Memvalidasi dan menyimpan feedback, serta menyiapkan konfirmasi pengiriman. |

**Model**

| Nama Komponen/Modul/Subsistem | Jenis | Penjelasan |
| :--- | :--- | :--- |
| `AkunPengguna` | Model | Menyimpan kredensial, profil, dan peran Pelajar, Pengajar, atau Tim Materi. |
| `MateriAksara` | Model | Menyimpan metadata materi, jenis aksara, dan tingkat kesulitan. |
| `KontenMateri` | Model | Menyimpan isi materi, contoh, aturan, template aksara, referensi audio, dan versi konten. |
| `RiwayatLatihan` | Model | Menyimpan hasil latihan, status, dan waktu pengerjaan Pelajar. |
| `MotivasiBelajar` | Model | Menyimpan streak, poin, dan badge Pelajar untuk motivasi dan papan peringkat. |
| `KelasBelajar` | Model | Menyimpan data kelas, pemilik kelas, kode bergabung, dan pengaturan kelas. |
| `KeanggotaanKelas` | Model | Menyimpan hubungan Pelajar dengan kelas dan status keanggotaannya. |
| `TugasKelas` | Model | Menyimpan instruksi, komponen, dan tenggat tugas. |
| `PenyelesaianTugas` | Model | Menyimpan status dan waktu pengumpulan tugas oleh Pelajar. |
| `DataFeedback` | Model | Menyimpan feedback, pengirim, waktu kirim, dan status tindak lanjut. |

**Penyimpanan Data dan Komponen Pendukung**

| Nama Komponen/Modul/Subsistem | Jenis | Penjelasan |
| :--- | :--- | :--- |
| `Database` | Penyimpanan Data | Menyimpan seluruh data Model secara persisten pada MySQL 8.4 yang digunakan aplikasi. |
| `PenyimpananBerkas` | Penyimpanan Data | Menyimpan berkas materi, gambar, audio pengucapan, dan template aksara pada disk lokal server melalui Laravel Filesystem. |
| `LogAplikasi` | Komponen Pendukung | Mencatat log aktivitas dan error aplikasi untuk keperluan pemeliharaan oleh pengembang. |

## 2.4 Model Arsitektur Perangkat Lunak

Buat model arsitektur dalam bentuk satu atau lebih *view* yang memperlihatkan interaksi dan kolaborasi komponen, modul, serta subsistem dalam menjalankan fungsi sistem. Pilih notasi yang sesuai. Contoh *view*: *Logical View*, *Process View*, *Development View*, dan *Physical View*.

Gunakan hasil **BAB 3 Model Arsitektur Perangkat Lunak pada dokumen APL**. 

### 2.4.1 View [Nama View]

Tuliskan secara singkat model arsitektur yang dipilih dan alasan model tersebut cocok untuk aplikasi.

<p align="center">
<img alt="Contoh Logical View pada P/L E-Commerce" src="./assets/diagram/contoh-logical-view.webp" width="50%">
</p>
<p align="center">
<i>Gambar 2. Contoh Logical View pada P/L E-Commerce</i>
</p>

### 2.4.X View [Nama View]
*[Lakukan hal yang sama dengan bagian sebelumnya]*

<br>

---


# BAB 3: Realisasi Use Case

## 3.1 Use Case Melakukan Pendaftaran Akun
 
**ID Use Case:** UC-01
**Nama Use Case:** Melakukan Pendaftaran Akun
 
### 3.1.1 Identifikasi Kelas
 
Tabel 3.1.1 Identifikasi Kelas Use Case UC-01
 
| No | Nama Kelas Perancangan | Nama Kelas Analisis Terkait |
| :--- | :--- | :--- |
| 1 | C05 - HalamanPendaftaran | C05 - HalamanPendaftaran |
| 2 | C06 - HalamanLogin | C06 - HalamanLogin |
| 3 | C15 - OtentikasiController | C15 - OtentikasiController |
| 4 | C01 - AkunPengguna | C01 - AkunPengguna |
 
### 3.1.2 Sequence Diagram
 
- **Skenario Normal**
 
<p align="center">
<img src="./assets/diagram/UC01_Skenario_Normal.png" alt="Sequence diagram UC-01 skenario normal" width="100%">
</p>
<p align="center"><i>Diagram sekuens UC-01 - Skenario Normal</i></p>

- **Skenario Alternatif 1: Validasi Pendaftaran Gagal**
 
<p align="center">
<img src="./assets/diagram/UC01_Alternatif1.png" alt="Sequence diagram UC-01 alternatif 1 validasi pendaftaran gagal" width="100%">
</p>
<p align="center"><i>Diagram sekuens UC-01 - Alternatif 1: Validasi Pendaftaran Gagal</i></p>

### 3.1.3 Diagram Kelas
 
<p align="center">
<img alt="Class Diagram UC01" src="./assets/diagram/ClassDiagram_UC01.png" width="100%">
</p>
<p align="center">
<i>Gambar X. Diagram Kelas Melakukan Pendaftaran Akun</i>
</p>

## 3.3 Use Case Mengerjakan Latihan Aksara
 
**ID Use Case:** UC-03  
**Nama Use Case:** Mengerjakan Latihan Aksara
 
### 3.3.1 Identifikasi Kelas
 
Tabel 3.3. Identifikasi Kelas Use Case UC-03
 
| No | Nama Kelas Perancangan | Nama Kelas Analisis Terkait |
| :--- | :--- | :--- |
| 1 | `HalamanLatihan` | C08 - `HalamanLatihan` |
| 2 | `LatihanController` | C16 - `LatihanController` |
| 3 | `MateriController` | C17 - `MateriController` |
| 4 | `AkunPengguna` | C01 - `AkunPengguna` |
| 5 | `MateriAksara` | C02 - `MateriAksara` |
| 6 | `KontenMateri` | C21 - `KontenMateri` |
| 7 | `RiwayatLatihan` | C03 - `RiwayatLatihan` |
| 8 | `MotivasiBelajar` | C22 - `MotivasiBelajar` |
 
### 3.3.2 Sequence Diagram
 
- **Skenario Normal**
 
<p align="center">
<img alt="Sequence Diagram UC-03 - Skenario Normal" src="./assets/diagram/UC03SeqNormal.png" width="90%">
</p>
<p align="center">
<i>Gambar X. Sequence Diagram UC-03 - Skenario Normal</i>
</p>

- **Skenario Alternatif 1: Latihan Mencocokkan Bunyi**
 
<p align="center">
<img alt="Sequence Diagram UC-03 - Latihan Mencocokkan Bunyi" src="./assets/diagram/UC03SeqAlt-1.png" width="90%">
</p>
<p align="center">
<i>Gambar X. Sequence Diagram UC-03 - Latihan Mencocokkan Bunyi</i>
</p>

- **Skenario Alternatif 2: Latihan Merangkai Aksara**
 
<p align="center">
<img alt="Sequence Diagram UC-03 - Latihan Merangkai Aksara" src="./assets/diagram/UC03SeqAlt-2.png" width="90%">
</p>
<p align="center">
<i>Gambar X. Sequence Diagram UC-03 - Latihan Merangkai Aksara</i>
</p>

- **Skenario Alternatif 3: Gambar Aksara Kurang Akurat (Mode Menulis)**
 
<p align="center">
<img alt="Sequence Diagram UC-03 - Gambar Aksara Kurang Akurat" src="./assets/diagram/UC03SeqAlt-3.png" width="90%">
</p>
<p align="center">
<i>Gambar X. Sequence Diagram UC-03 - Gambar Aksara Kurang Akurat</i>
</p>

### 3.3.3 Diagram Kelas
 
<p align="center">
<img alt="Diagram Kelas Use Case UC-03" src="./assets/diagram/UC03ClassDiagram.png" width="60%">
</p>
<p align="center">
<i>Gambar X. Diagram Kelas Use Case UC-03</i>
</p>

## 3.4 Use Case UC-04: Meninjau Progres dan Motivasi

**ID Use Case:** UC-04  
**Nama Use Case:** Meninjau Progres dan Motivasi

### 3.4.1 Identifikasi Kelas

Tabel 3.4. Identifikasi Kelas Use Case UC-04

| No | Nama Kelas Perancangan | Nama Kelas Analisis Terkait |
| :--- | :--- | :--- |
| 1 | `HalamanMateri` (C13) | HalamanMateri (C13) |
| 2 | `HalamanProgres` (C14) | HalamanProgres (C14) |
| 3 | `ProgresController` (C19) | ProgresController (C19) |
| 4 | `AkunPengguna` (C01) | AkunPengguna (C01) |
| 5 | `KeanggotaanKelas` (C24) | KeanggotaanKelas (C24) |
| 6 | `KelasBelajar` (C23) | KelasBelajar (C23) |
| 7 | `MateriAksara` (C02) | MateriAksara (C02) |
| 8 | `MotivasiBelajar` (C22) | MotivasiBelajar (C22) |
| 9 | `RiwayatLatihan` (C03) | RiwayatLatihan (C03) |

### 3.4.2 Sequence Diagram

- **Skenario Normal**

<p align="center">
<img alt="Gambar 15. Sequence Diagram UC-04 - Skenario Normal" src="./assets/diagram/UC04 Sequence Normal.png" width="100%">
</p>
<p align="center"><i>Gambar 15. Sequence Diagram UC-04 - Skenario Normal</i></p>


- **Skenario Alternatif 1: Belum ada riwayat pembelajaran**

<p align="center">
<img alt="Gambar 16. Sequence Diagram UC-04 - Skenario Alternatif 1: Belum ada riwayat pembelajaran" src="./assets/diagram/UC04 Alternatif 1.png" width="100%">
</p>
<p align="center"><i>Gambar 16. Sequence Diagram UC-04 - Skenario Alternatif 1: Belum ada riwayat pembelajaran</i></p>


- **Skenario Alternatif 2: Melihat papan peringkat kelas**

<p align="center">
<img alt="Gambar 17. Sequence Diagram UC-04 - Skenario Alternatif 2: Melihat papan peringkat kelas" src="./assets/diagram/UC04 Alternatif 2.png" width="100%">
</p>
<p align="center"><i>Gambar 17. Sequence Diagram UC-04 - Skenario Alternatif 2: Melihat papan peringkat kelas</i></p>


### 3.4.3 Diagram Kelas

`ProgresController` mengolah data dari `RiwayatLatihan`, `MateriAksara`, dan `MotivasiBelajar`. Untuk papan peringkat, `ProgresController` memeriksa pengaturan kelas pada `KelasBelajar` dan mengambil anggota aktif dari `KeanggotaanKelas`.

<p align="center">
<img alt="Gambar 18. Diagram Kelas Meninjau Progres dan Motivasi (UC-04)" src="./assets/diagram/UC04 Diagram Kelas.png" width="100%">
</p>
<p align="center"><i>Gambar 18. Diagram Kelas Meninjau Progres dan Motivasi (UC-04)</i></p>

## 3.5 Use Case Mengikuti Pembelajaran Kelas

**ID Use Case:** *UC05*  
**Nama Use Case:** *Mengikuti Pembelajaran Kelas*

### 3.5.1 Identifikasi Kelas

Tabel 3.5.1 Identifikasi Kelas Use Case UC05

| No | Nama Kelas Perancangan | Nama Kelas Analisis Terkait |
| :--- | :--- | :--- |
| 1 | C11 — HalamanKelasPelajar | C11 — HalamanKelasPelajar |
| 2 | C20 — KelasController | C20 — KelasController |
| 3 | C01 — AkunPengguna | C01 — AkunPengguna |
| 4 | C23 — KelasBelajar | C23 — KelasBelajar |
| 5 | C24 — KeanggotaanKelas | C24 — KeanggotaanKelas |
| 6 | C25 — TugasKelas | C25 — TugasKelas |
| 7 | C03 — RiwayatLatihan | C03 — RiwayatLatihan |
| 8 | C26 — PenyelesaianTugas | C26 — PenyelesaianTugas |

### 3.5.2 Sequence Diagram

- **Skenario Normal**

<p align="center">
<img src="./assets/diagram/UC05_Skenario_Normal.png" alt="Sequence diagram UC-05 skenario normal" width="100%">
</p>
<p align="center"><i>Diagram sekuens UC-05 — Skenario Normal</i></p>

- **Skenario Alternatif 1: Pelajar Sudah Menjadi Anggota Kelas**

<p align="center">
<img src="./assets/diagram/UC05_Alternatif1.png" alt="Sequence diagram UC-05 alternatif 1 anggota aktif" width="100%">
</p>
<p align="center"><i>Diagram sekuens UC-05 — Alternatif 1: Pelajar Sudah Menjadi Anggota Kelas</i></p>

- **Skenario Alternatif 2: Tugas Diselesaikan Terlambat**

<p align="center">
<img src="./assets/diagram/UC05_Alternatif2.png" alt="Sequence diagram UC-05 alternatif 2 penyelesaian terlambat" width="100%">
</p>
<p align="center"><i>Diagram sekuens UC-05 — Alternatif 2: Tugas Diselesaikan Terlambat</i></p>

- **Skenario Alternatif 3: Kode Kelas Tidak Valid**

<p align="center">
<img src="./assets/diagram/UC05_Alternatif3.png" alt="Sequence diagram UC-05 alternatif 3 kode tidak valid" width="100%">
</p>
<p align="center"><i>Diagram sekuens UC-05 — Alternatif 3: Kode Kelas Tidak Valid</i></p>

- **Eksepsi: Penyimpanan Hasil Gagal**

<p align="center">
<img src="./assets/diagram/UC05_Eksepsi1.png" alt="Sequence diagram UC-05 usulan eksepsi transaksi penyimpanan" width="100%">
</p>
<p align="center"><i>Diagram sekuens UC-05 — Eksepsi Penyimpanan Hasil Gagal</i></p>

- **Eksepsi: Akses atau Bukti Penyelesaian Tidak Memenuhi Syarat**

<p align="center">
<img src="./assets/diagram/UC05_Eksepsi2.png" alt="Sequence diagram UC-05 usulan eksepsi akses dan bukti" width="100%">
</p>
<p align="center"><i>Diagram sekuens UC-05 — Eksepsi Akses dan Bukti Penyelesaian</i></p>

`ref UC-02/UC-03` merujuk realisasi pengerjaan materi/latihan. Finalisasi menggunakan bukti penyelesaian di server; kriteria selesai materi masih perlu ditetapkan pada UC-02.

### 3.5.3 Diagram Kelas

<p align="center">
<img alt="Class Diagram UC5" src="./assets/diagram/ClassDiagram_UC5.png" width="100%">
</p>
<p align="center">
<i>Gambar X. Diagram Kelas Mengikuti Pembelajaran Kelas </i>
</p>

<br>

## 3.7 Use Case UC-07: Menyiapkan Pembelajaran Kelas

**ID Use Case:** UC-07
**Nama Use Case:** Menyiapkan Pembelajaran Kelas

### 3.7.1 Identifikasi Kelas

Tabel 3.7. Identifikasi Kelas Use Case UC-7

| No | Nama Kelas Perancangan | Nama Kelas Analisis Terkait |
| :--- | :--- | :--- |
| 1 | `HalamanKelolaKelas` | HalamanKelolaKelas (C12) |
| 2 | `KelasController` | KelasController (C20) |
| 3 | `TugasKelas` | ProgresController (C25) |
| 4 | `AkunPengguna` | AkunPengguna (C01) |
| 5 | `KeanggotaanKelas` | KeanggotaanKelas (C24) |
| 6 | `KelasBelajar` | KelasBelajar (C23) |
| 7 | `MotivasiBelajar` | RiwayatLatihan (C22) |

### 3.7.2 Sequence Diagram

- **Skenario Normal**

<p align="center">
<img alt="Gambar xx. Sequence Diagram UC-07 - Skenario Normal" src="./assets/diagram/SkenarioNormal UC_07-Page-1.drawio.png" width="100%">
</p>
<p align="center"><i>Gambar xx. Sequence Diagram UC-07 - Skenario Normal</i></p>


- **Skenario Alternatif 1: Mengganti Kode Kelas**

<p align="center">
<img alt="Gambar xx. Sequence Diagram UC-07 - Skenario Alternatif 1: Mengganti Kode Kelas" src="./assets/diagram/SkenarioAlt01 UC_07.drawio.png" width="100%">
</p>
<p align="center"><i>Gambar xx. Sequence Diagram UC-07 - Skenario Alternatif 1: Mengganti Kode Kelas</i></p>


- **Skenario Alternatif 2: Mengeluarkan Anggota Kelas**

<p align="center">
<img alt="Gambar xx. Sequence Diagram UC-07 - Skenario Alternatif 2: Mengeluarkan Anggota Kelas" src="./assets/diagram/SkenarioAlt2 UC_07.drawio.png" width="100%">
</p>
<p align="center"><i>Gambar xx. Sequence Diagram UC-07 - Skenario Alternatif 2: Mengeluarkan Anggota Kelas</i></p>

- **Skenario Alternatif 3: Mengaktifkan Papan Peringkat Kelas**

<p align="center">
<img alt="Gambar xx. Sequence Diagram UC-07 - Skenario Alternatif 3: Mengaktifkan Papan Peringkat Kelas" src="./assets/diagram/SkenarioAlt03 UC_07.drawio.png" width="100%">
</p>
<p align="center"><i>Gambar xx. Sequence Diagram UC-07 - Skenario Alternatif 2: Mengaktifkan Papan Peringkat Kelas</i></p>

### 3.7.3 Diagram Kelas

<p align="center">
<img alt="Gambar xx. Diagram Kelas Menyiapkan Pembelajaran Kelas (UC-07)" src="./assets/diagram/DiagramKelas UC_07.drawio.png" width="100%">
</p>
<p align="center"><i>Gambar xx. Diagram Kelas Menyiapkan Pembelajaran Kelas (UC-07)</i></p>

## 3.8 Use Case UC-08: Memantau dan Menindaklanjuti Progres

**ID Use Case:** UC-08  
**Nama Use Case:** Memantau dan Menindaklanjuti Progres

### 3.8.1 Identifikasi Kelas

Tabel 3.8. Identifikasi Kelas Use Case UC-08

| No | Nama Kelas Perancangan | Nama Kelas Analisis Terkait |
| :--- | :--- | :--- |
| 1 | `HalamanKelolaKelas` (C12) | HalamanKelolaKelas (C12) |
| 2 | `KelasController` (C20) | KelasController (C20) |
| 3 | `ProgresController` (C19) | ProgresController (C19) |
| 4 | `AkunPengguna` (C01) | AkunPengguna (C01) |
| 5 | `KeanggotaanKelas` (C24) | KeanggotaanKelas (C24) |
| 6 | `KelasBelajar` (C23) | KelasBelajar (C23) |
| 7 | `RiwayatLatihan` (C03) | RiwayatLatihan (C03) |

### 3.8.2 Sequence Diagram

- **Skenario Normal**

<p align="center">
<img alt="Gambar 32. Sequence Diagram UC-08 - Skenario Normal" src="./assets/diagram/UC08 Normal.png" width="100%">
</p>
<p align="center"><i>Gambar 32. Sequence Diagram UC-08 - Skenario Normal</i></p>


- **Skenario Alternatif 1: Belum ada hasil belajar anggota**

<p align="center">
<img alt="Gambar 33. Sequence Diagram UC-08 - Skenario Alternatif 1: Belum ada hasil belajar anggota" src="./assets/diagram/UC08 Alternatif 1.png" width="100%">
</p>
<p align="center"><i>Gambar 33. Sequence Diagram UC-08 - Skenario Alternatif 1: Belum ada hasil belajar anggota</i></p>


- **Skenario Alternatif 2: Anggota yang dipilih bukan anggota aktif kelas**

<p align="center">
<img alt="Gambar 34. Sequence Diagram UC-08 - Skenario Alternatif 2: Anggota yang dipilih bukan anggota aktif kelas" src="./assets/diagram/UC08 Alternatif 2.png" width="100%">
</p>
<p align="center"><i>Gambar 34. Sequence Diagram UC-08 - Skenario Alternatif 2: Anggota yang dipilih bukan anggota aktif kelas</i></p>


### 3.8.3 Diagram Kelas

`KelasController` meminta `ProgresController` mengolah riwayat anggota aktif, lalu menampilkan hasilnya kepada Pengajar melalui `HalamanKelolaKelas`. Pengajar dapat membuka detail setiap anggota aktif. Akses ke data anggota dicek dengan `KelasBelajar` (kelas milik Pengajar) dan `KeanggotaanKelas` (keanggotaan aktif). Pada skenario alternatif 2, anggota yang bukan anggota aktif ditolak dan datanya tidak ditampilkan. Umpan balik dan rekomendasi dari Pengajar tidak dirancang pada tahap ini, karena belum ada kelas untuk menyimpannya dan keputusan kelompok soal pesan antarpengguna belum diambil.

<p align="center">
<img alt="Gambar 35. Diagram Kelas Memantau dan Menindaklanjuti Progres (UC-08)" src="./assets/diagram/UC08 Diagram Kelas.png" width="100%">
</p>
<p align="center"><i>Gambar 35. Diagram Kelas Memantau dan Menindaklanjuti Progres (UC-08)</i></p>



## 3.9 Use Case Menyampaikan Feedback
 
**ID Use Case:** UC-09
**Nama Use Case:** Menyampaikan Feedback
 
### 3.9.1 Identifikasi Kelas
 
Tabel 3.9.1 Identifikasi Kelas Use Case UC-09
 
| No | Nama Kelas Perancangan | Nama Kelas Analisis Terkait |
| :--- | :--- | :--- |
| 1 | C10 - HalamanFeedback | C10 - HalamanFeedback |
| 2 | C18 - FeedbackController | C18 - FeedbackController |
| 3 | C04 - DataFeedback | C04 - DataFeedback |
| 4 | C01 - AkunPengguna | C01 - AkunPengguna |
 
### 3.9.2 Sequence Diagram

- **Skenario Normal**
 
<p align="center">
<img src="./assets/diagram/UC09_Skenario_Normal.png" alt="Sequence diagram UC-09 skenario normal" width="100%">
</p>
<p align="center"><i>Diagram sekuens UC-09 — Skenario Normal</i></p>

- **Skenario Alternatif 1: Feedback Gagal Terkirim**
 
<p align="center">
<img src="./assets/diagram/UC09_Alternatif1.png" alt="Sequence diagram UC-09 alternatif 1 feedback gagal terkirim" width="100%">
</p>
<p align="center"><i>Diagram sekuens UC-09 — Alternatif 1: Feedback Gagal Terkirim</i></p>

### 3.9.3 Diagram Kelas
 
<p align="center">
<img alt="Class Diagram UC09" src="./assets/diagram/ClassDiagram_UC09.png" width="100%">
</p>
<p align="center">
<i>Gambar X. Diagram Kelas Menyampaikan Feedback</i>
</p>

---

# BAB 4: Diagram Kelas Keseluruhan

## 4.1 Diagram Kelas

**Bagian ini diisi dengan diagram kelas keseluruhan.**

<p align="center">
<img alt="Contoh Logical View pada P/L E-Commerce" src="./assets/diagram/contoh-class_diargam.png" width="35%">
</p>
<p align="center">
<i>Gambar 4.1. Diagram Kelas Perancangan Keseluruhan [Nama P/L]</i>
</p>

Tabel 4.1. Daftar Kelas Perancangan Keseluruhan

| ID Kelas | Nama Kelas | Atribut | Metode/Operasi |
| :--- | :--- | :--- | :--- |
| *[C01]* | *[Nama kelas]* | *[Daftar atribut]* | *[Daftar metode/operasi]* |
| *[C02]* | *[Nama kelas]* | *[Daftar atribut]* | *[Daftar metode/operasi]* |
| *[C03]* | *[Nama kelas]* | *[Daftar atribut]* | *[Daftar metode/operasi]* |
| *...* | *...* | *...* | *...* |

<br>

---

# BAB 5: Matriks Kerunutan

Tabel 5.1. Matriks Kerunutan Kelas terhadap Use Case

| Kelas | Use Case Terkait |
| :--- | :--- |
| C01 - AkunPengguna | UC-01 - Melakukan Pendaftaran Akun<br>UC-02 - Mempelajari Materi<br>UC-03 - Mengerjakan Latihan Aksara<br>UC-04 - Meninjau Progres dan Motivasi<br>UC-05 - Mengikuti Pembelajaran Kelas<br>UC-06 - Memperbaharui Materi<br>UC-07 - Menyiapkan Pembelajaran Kelas<br>UC-08 - Memantau dan Menindaklanjuti Progres<br>UC-09 - Menyampaikan Feedback |
| C02 - MateriAksara | UC-02 - Mempelajari Materi<br>UC-03 - Mengerjakan Latihan Aksara<br>UC-04 - Meninjau Progres dan Motivasi<br>UC-06 - Memperbaharui Materi |
| C03 - RiwayatLatihan | UC-02 - Mempelajari Materi<br>UC-03 - Mengerjakan Latihan Aksara<br>UC-04 - Meninjau Progres dan Motivasi<br>UC-05 - Mengikuti Pembelajaran Kelas<br>UC-08 - Memantau dan Menindaklanjuti Progres |
| C04 - DataFeedback | UC-09 - Menyampaikan Feedback |
| C05 - HalamanPendaftaran | UC-01 - Melakukan Pendaftaran Akun |
| C06 - HalamanLogin | UC-01 - Melakukan Pendaftaran Akun |
| C07 - HalamanUtama | UC-02 - Mempelajari Materi<br>UC-03 - Mengerjakan Latihan Aksara<br>UC-04 - Meninjau Progres dan Motivasi<br>UC-05 - Mengikuti Pembelajaran Kelas<br>UC-06 - Memperbaharui Materi<br>UC-07 - Menyiapkan Pembelajaran Kelas<br>UC-08 - Memantau dan Menindaklanjuti Progres<br>UC-09 - Menyampaikan Feedback |
| C08 - HalamanLatihan | UC-03 - Mengerjakan Latihan Aksara |
| C09 - HalamanKelolaMateri | UC-06 - Memperbaharui Materi |
| C10 - HalamanFeedback | UC-09 - Menyampaikan Feedback |
| C11 - HalamanKelasPelajar | UC-05 - Mengikuti Pembelajaran Kelas |
| C12 - HalamanKelolaKelas | UC-07 - Menyiapkan Pembelajaran Kelas<br>UC-08 - Memantau dan Menindaklanjuti Progres |
| C13 - HalamanMateri | UC-02 - Mempelajari Materi |
| C14 - HalamanProgres | UC-04 - Meninjau Progres dan Motivasi |
| C15 - OtentikasiController | UC-01 - Melakukan Pendaftaran Akun |
| C16 - LatihanController | UC-03 - Mengerjakan Latihan Aksara |
| C17 - MateriController | UC-02 - Mempelajari Materi<br>UC-03 - Mengerjakan Latihan Aksara<br>UC-06 - Memperbaharui Materi |
| C18 - FeedbackController | UC-09 - Menyampaikan Feedback |
| C19 - ProgresController | UC-04 - Meninjau Progres dan Motivasi<br>UC-08 - Memantau dan Menindaklanjuti Progres |
| C20 - KelasController | UC-05 - Mengikuti Pembelajaran Kelas<br>UC-07 - Menyiapkan Pembelajaran Kelas<br>UC-08 - Memantau dan Menindaklanjuti Progres |
| C21 - KontenMateri | UC-02 - Mempelajari Materi<br>UC-03 - Mengerjakan Latihan Aksara<br>UC-06 - Memperbaharui Materi |
| C22 - MotivasiBelajar | UC-03 - Mengerjakan Latihan Aksara<br>UC-04 - Meninjau Progres dan Motivasi<br>UC-07 - Menyiapkan Pembelajaran Kelas |
| C23 - KelasBelajar | UC-05 - Mengikuti Pembelajaran Kelas<br>UC-07 - Menyiapkan Pembelajaran Kelas<br>UC-08 - Memantau dan Menindaklanjuti Progres |
| C24 - KeanggotaanKelas | UC-05 - Mengikuti Pembelajaran Kelas<br>UC-07 - Menyiapkan Pembelajaran Kelas<br>UC-08 - Memantau dan Menindaklanjuti Progres |
| C25 - TugasKelas | UC-05 - Mengikuti Pembelajaran Kelas<br>UC-07 - Menyiapkan Pembelajaran Kelas |
| C26 - PenyelesaianTugas | UC-05 - Mengikuti Pembelajaran Kelas |
