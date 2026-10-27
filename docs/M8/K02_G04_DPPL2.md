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
<img alt="Penerapan Arsitektur MVC pada Ngaksara" src="./assets/diagram/LOGO.png" width="40%">
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
| *A* | Revisi diagram sekuens dan kelas menyesuaikan dengan operasi dan atribut yang tersedia |
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
### 2.4.1 Logical View

Logical View dipilih untuk menggambarkan Ngaksara karena view ini menunjukkan pembagian tanggung jawab antarkomponen secara langsung yang merupakan inti dari pola MVC. Selain itu, Logical View juga cocok dengan Ngaksara karena Ngaksara dipakai oleh peran Pelajar, Pengajar, juga Tim Materi dengan halaman dan akses yang berbeda. Logical View dapat memperlihatkan perbedaan halaman yang melayani tiap peran.

<p align="center">
<img alt="Contoh Logical View pada P/L E-Commerce" src="./assets/diagram/DiagramFinal.jpg" width="50%">
</p>
<p align="center">
<i>Gambar 2. Contoh Logical View pada P/L E-Commerce</i>
</p>

Hubungan Antarkomponen pada diagram tersebut adalah sebagai berikut.

| Label | Arah | Makna |
| :--- | :--- | :--- |
| Memanggil | *View* → *Controller* | Halaman meneruskan permintaan pengguna ke Controller yang menangani fiturnya |
| Akses | *Controller* → *Model* | Controller membaca atau mengubah data melalui Model |
| Menyimpan data | *Model* → `Database` | Model menyimpan datanya pada MySQL |
| Komposisi | antar-*Model* | Satu Model menjadi bagian yang tidak berdiri sendiri dari Model lain |
| Agregasi | antar-*Model* | Satu Model terhubung ke Model lain, tetapi keduanya tetap dapat berdiri sendiri |

Hubungan mengalir dari lapisan atas ke bawah: *View*, *Controller*, *Model*, lalu `Database`. *View* tidak mengakses *Model* secara langsung, dan *Model* tidak memanggil *Controller*. Dengan begitu, perubahan tampilan tidak memengaruhi cara data disimpan, sesuai alasan pemilihan MVC pada Bab 1.

Hubungan antar-*Model* pada diagram adalah sebagai berikut.
- **Komposisi:** `AkunPengguna` dengan `RiwayatLatihan` dan `MotivasiBelajar`, karena riwayat dan motivasi selalu milik satu akun. `MateriAksara` dengan `KontenMateri`, karena isi materi tidak berdiri tanpa metadatanya. `KelasBelajar` dengan `KeanggotaanKelas` dan `TugasKelas`, serta `TugasKelas` dengan `PenyelesaianTugas`.
- **Agregasi:** `RiwayatLatihan` dengan `MateriAksara`, serta `AkunPengguna` dengan `KeanggotaanKelas` dan `DataFeedback`.

Alur Interaksi 

1. `HalamanPendaftaran` dan `HalamanLogin` memanggil `OtentikasiController`. Controller memvalidasi kredensial dan mengakses `AkunPengguna`. Akun yang tersimpan menjadi dasar hak akses ketiga peran pada alur berikutnya.
2. `HalamanMateri` memanggil `MateriController`, yang mengakses `MateriAksara` untuk daftar dan metadata materi serta `KontenMateri` untuk isi dan audio pengucapan. `HalamanUtama` berperan sebagai navigasi menuju halaman-halaman tersebut.
3. `HalamanLatihan` memanggil `LatihanController`. Controller mengakses `MateriAksara` dan `KontenMateri` untuk menyiapkan soal, menilai jawaban, lalu mengakses `RiwayatLatihan` untuk menyimpan hasilnya dan `MotivasiBelajar` untuk memperbarui streak dan poin.
4. `HalamanProgres` memanggil `ProgressController`, yang mengakses `RiwayatLatihan` untuk menghitung penguasaan, `MateriAksara` untuk rekomendasi materi, dan `MotivasiBelajar` untuk streak, poin, badge, dan papan peringkat.
5. `HalamanKelolaKelas` (Pengajar) dan `HalamanKelasPelajar` (Pelajar) sama-sama memanggil `KelasController`. Controller ini mengakses `KelasBelajar`, `KeanggotaanKelas`, `TugasKelas`, dan `PenyelesaianTugas` untuk membuat kelas, memproses kode bergabung, menerbitkan tugas, dan mencatat status pengumpulan.
6. `HalamanKelolaMateri` memanggil `MateriController`, yang mengakses `MateriAksara` dan `KontenMateri` untuk menambah atau memperbarui materi.
7. `HalamanFeedback` memanggil `FeedbackController`, yang mengakses `DataFeedback` untuk menyimpan masukan.

Pada semua alur tersebut, data pada setiap Model akhirnya disimpan ke `Database` melalui hubungan "Menyimpan data".

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
<p align="center"><i>Gambar 3. Diagram sekuens UC-01 - Skenario Normal</i></p>

- **Skenario Alternatif 1: Validasi Pendaftaran Gagal**
 
<p align="center">
<img src="./assets/diagram/UC01_Alternatif1.png" alt="Sequence diagram UC-01 alternatif 1 validasi pendaftaran gagal" width="100%">
</p>
<p align="center"><i>Gambar 4. Diagram sekuens UC-01 - Alternatif 1: Validasi Pendaftaran Gagal</i></p>

### 3.1.3 Diagram Kelas
 
<p align="center">
<img alt="Class Diagram UC01" src="./assets/diagram/ClassDiagram_UC01.png" width="100%">
</p>
<p align="center">
<i>Gambar 5. Diagram Kelas Melakukan Pendaftaran Akun</i>
</p>

## 3.2 Use Case Mempelajari Materi

**ID Use Case:** UC-02
**Nama Use Case:** Mempelajari Materi

### 3.2.1 Identifikasi Kelas

Tabel 3.2.1 Identifikasi Kelas Use Case UC-02

| No | Nama Kelas Perancangan | Nama Kelas Analisis Terkait |
| :--- | :--- | :--- |
| 1 | C13 — HalamanMateri | C13 — HalamanMateri |
| 2 | C17 — MateriController | C17 — MateriController |
| 3 | C02 — MateriAksara | C02 — MateriAksara |
| 4 | C21 — KontenMateri | C21 — KontenMateri |
| 5 | C03 — RiwayatLatihan | C03 — RiwayatLatihan |
| 6 | C01 — AkunPengguna | C01 — AkunPengguna |

### 3.2.2 Sequence Diagram

- **Skenario Normal**

<p align="center">
<img src="./assets/diagram/UC-02-seq.png" alt="Sequence diagram UC-02 skenario normal" width="100%">
</p>
<p align="center"><i>Gambar 6. Diagram sekuens UC-02 — Skenario Normal</i></p>

- **Skenario Alternatif 1: Materi Belum Tersedia untuk Kombinasi yang Dipilih**

<p align="center">
<img src="./assets/diagram/UC-02-seq-alt1.png" alt="Sequence diagram UC-02 alternatif 1 materi belum tersedia" width="100%">
</p>
<p align="center"><i>Gambar 7. Diagram sekuens UC-02 — Alternatif 1: Materi Belum Tersedia</i></p>

- **Skenario Alternatif 2: Audio Pengucapan Gagal Diputar**

<p align="center">
<img src="./assets/diagram/UC-02-seq-alt2.png" alt="Sequence diagram UC-02 alternatif 2 audio gagal diputar" width="100%">
</p>
<p align="center"><i>Gambar 8. Diagram sekuens UC-02 — Alternatif 2: Audio Pengucapan Gagal Diputar</i></p>

### 3.2.3 Diagram Kelas

<p align="center">
<img src="./assets/diagram/UC-02-class.png" alt="Diagram kelas UC-02 Mempelajari Materi" width="100%">
</p>
<p align="center"><i>Gambar 9. Diagram kelas UC-02 — Mempelajari Materi</i></p>

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
<i>Gambar 10. Diagram sekuens UC-03 - Skenario Normal</i>
</p>

- **Skenario Alternatif 1: Latihan Mencocokkan Bunyi**
 
<p align="center">
<img alt="Sequence Diagram UC-03 - Latihan Mencocokkan Bunyi" src="./assets/diagram/UC03SeqAlt-1.png" width="90%">
</p>
<p align="center">
<i>Gambar 11. Diagram sekuens UC-03 - Latihan Mencocokkan Bunyi</i>
</p>

- **Skenario Alternatif 2: Latihan Merangkai Aksara**
 
<p align="center">
<img alt="Sequence Diagram UC-03 - Latihan Merangkai Aksara" src="./assets/diagram/UC03SeqAlt-2.png" width="90%">
</p>
<p align="center">
<i>Gambar 12. Diagram sekuens UC-03 - Latihan Merangkai Aksara</i>
</p>

- **Skenario Alternatif 3: Gambar Aksara Kurang Akurat (Mode Menulis)**
 
<p align="center">
<img alt="Sequence Diagram UC-03 - Gambar Aksara Kurang Akurat" src="./assets/diagram/UC03SeqAlt-3.png" width="90%">
</p>
<p align="center">
<i>Gambar 13. Diagram sekuens UC-03 - Gambar Aksara Kurang Akurat</i>
</p>

### 3.3.3 Diagram Kelas
 
<p align="center">
<img alt="Diagram Kelas Use Case UC-03" src="./assets/diagram/UC03ClassDiagram.png" width="60%">
</p>
<p align="center">
<i>Gambar 14. Diagram Kelas Use Case UC-03</i>
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
<p align="center"><i>Gambar 15. Diagram sekuens UC-04 - Skenario Normal</i></p>


- **Skenario Alternatif 1: Belum ada riwayat pembelajaran**

<p align="center">
<img alt="Gambar 16. Sequence Diagram UC-04 - Skenario Alternatif 1: Belum ada riwayat pembelajaran" src="./assets/diagram/UC04 Alternatif 1.png" width="100%">
</p>
<p align="center"><i>Gambar 16. Diagram sekuens UC-04 - Skenario Alternatif 1: Belum ada riwayat pembelajaran</i></p>


- **Skenario Alternatif 2: Melihat papan peringkat kelas**

<p align="center">
<img alt="Gambar 17. Sequence Diagram UC-04 - Skenario Alternatif 2: Melihat papan peringkat kelas" src="./assets/diagram/UC04 Alternatif 2.png" width="100%">
</p>
<p align="center"><i>Gambar 17. Diagram sekuens UC-04 - Skenario Alternatif 2: Melihat papan peringkat kelas</i></p>


### 3.4.3 Diagram Kelas

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
<p align="center"><i>Gambar 19. Diagram sekuens UC-05 — Skenario Normal</i></p>

- **Skenario Alternatif 1: Pelajar Sudah Menjadi Anggota Kelas**

<p align="center">
<img src="./assets/diagram/UC05_Alternatif1.png" alt="Sequence diagram UC-05 alternatif 1 anggota aktif" width="100%">
</p>
<p align="center"><i>Gambar 20. Diagram sekuens UC-05 — Alternatif 1: Pelajar Sudah Menjadi Anggota Kelas</i></p>

- **Skenario Alternatif 2: Tugas Diselesaikan Terlambat**

<p align="center">
<img src="./assets/diagram/UC05_Alternatif2.png" alt="Sequence diagram UC-05 alternatif 2 penyelesaian terlambat" width="100%">
</p>
<p align="center"><i>Gambar 21. Diagram sekuens UC-05 — Alternatif 2: Tugas Diselesaikan Terlambat</i></p>

- **Skenario Alternatif 3: Kode Kelas Tidak Valid**

<p align="center">
<img src="./assets/diagram/UC05_Alternatif3.png" alt="Sequence diagram UC-05 alternatif 3 kode tidak valid" width="100%">
</p>
<p align="center"><i>Gambar 22. Diagram sekuens UC-05 — Alternatif 3: Kode Kelas Tidak Valid</i></p>

- **Eksepsi: Penyimpanan Hasil Gagal**

<p align="center">
<img src="./assets/diagram/UC05_Eksepsi1.png" alt="Sequence diagram UC-05 usulan eksepsi transaksi penyimpanan" width="100%">
</p>
<p align="center"><i>Gambar 23. Diagram sekuens UC-05 — Eksepsi Penyimpanan Hasil Gagal</i></p>

- **Eksepsi: Akses atau Bukti Penyelesaian Tidak Memenuhi Syarat**

<p align="center">
<img src="./assets/diagram/UC05_Eksepsi2.png" alt="Sequence diagram UC-05 usulan eksepsi akses dan bukti" width="100%">
</p>
<p align="center"><i>Gambar 24. Diagram sekuens UC-05 — Eksepsi Akses dan Bukti Penyelesaian</i></p>

### 3.5.3 Diagram Kelas

<p align="center">
<img alt="Class Diagram UC5" src="./assets/diagram/ClassDiagram_UC5.png" width="100%">
</p>
<p align="center">
<i>Gambar 25. Diagram Kelas Mengikuti Pembelajaran Kelas </i>
</p>

<br>

## 3.6 Use Case Memperbaharui Materi

**ID Use Case:** UC-06
**Nama Use Case:** Memperbaharui Materi

### 3.6.1 Identifikasi Kelas

Tabel 3.6.1 Identifikasi Kelas Use Case UC-06

| No | Nama Kelas Perancangan | Nama Kelas Analisis Terkait |
| :--- | :--- | :--- |
| 1 | C09 — HalamanKelolaMateri | C09 — HalamanKelolaMateri |
| 2 | C17 — MateriController | C17 — MateriController |
| 3 | C02 — MateriAksara | C02 — MateriAksara |
| 4 | C21 — KontenMateri | C21 — KontenMateri |
| 5 | C01 — AkunPengguna | C01 — AkunPengguna |


### 3.6.2 Sequence Diagram

- **Skenario Normal**

<p align="center">
<img src="./assets/diagram/UC-06-seq.png" alt="Sequence diagram UC-06 skenario normal" width="100%">
</p>
<p align="center"><i>Gambar 26. Diagram sekuens UC-06 — Skenario Normal</i></p>

- **Skenario Alternatif 1: Penambahan Materi Gagal**

<p align="center">
<img src="./assets/diagram/UC-06-seq-alt1.png" alt="Sequence diagram UC-06 alternatif 1 penambahan materi gagal" width="100%">
</p>
<p align="center"><i>Gambar 27. Diagram sekuens UC-06 — Alternatif 1: Penambahan Materi Gagal</i></p>


### 3.6.3 Diagram Kelas

<p align="center">
<img src="./assets/diagram/UC-06-class.png" alt="Diagram kelas UC-06 Memperbaharui Materi" width="100%">
</p>
<p align="center"><i>Gambar 28. Diagram kelas UC-06 — Memperbaharui Materi</i></p>


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
<p align="center"><i>Gambar 29. Diagram sekuens UC-07 - Skenario Normal</i></p>


- **Skenario Alternatif 1: Mengganti Kode Kelas**

<p align="center">
<img alt="Gambar xx. Sequence Diagram UC-07 - Skenario Alternatif 1: Mengganti Kode Kelas" src="./assets/diagram/SkenarioAlt01 UC_07.drawio.png" width="100%">
</p>
<p align="center"><i>Gambar 30. Diagram sekuens UC-07 - Skenario Alternatif 1: Mengganti Kode Kelas</i></p>


- **Skenario Alternatif 2: Mengeluarkan Anggota Kelas**

<p align="center">
<img alt="Gambar xx. Sequence Diagram UC-07 - Skenario Alternatif 2: Mengeluarkan Anggota Kelas" src="./assets/diagram/SkenarioAlt2 UC_07.drawio.png" width="100%">
</p>
<p align="center"><i>Gambar 31. Diagram sekuens UC-07 - Skenario Alternatif 2: Mengeluarkan Anggota Kelas</i></p>

- **Skenario Alternatif 3: Mengaktifkan Papan Peringkat Kelas**

<p align="center">
<img alt="Gambar xx. Sequence Diagram UC-07 - Skenario Alternatif 3: Mengaktifkan Papan Peringkat Kelas" src="./assets/diagram/SkenarioAlt03 UC_07.drawio.png" width="100%">
</p>
<p align="center"><i>Gambar 32. Diagram sekuens UC-07 - Skenario Alternatif 3: Mengaktifkan Papan Peringkat Kelas</i></p>

### 3.7.3 Diagram Kelas

<p align="center">
<img alt="Gambar xx. Diagram Kelas Menyiapkan Pembelajaran Kelas (UC-07)" src="./assets/diagram/DiagramKelas UC_07.drawio.png" width="100%">
</p>
<p align="center"><i>Gambar 33. Diagram Kelas Menyiapkan Pembelajaran Kelas (UC-07)</i></p>

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
<p align="center"><i>Gambar 34. Diagram sekuens UC-08 - Skenario Normal</i></p>


- **Skenario Alternatif 1: Belum ada hasil belajar anggota**

<p align="center">
<img alt="Gambar 33. Sequence Diagram UC-08 - Skenario Alternatif 1: Belum ada hasil belajar anggota" src="./assets/diagram/UC08 Alternatif 1.png" width="100%">
</p>
<p align="center"><i>Gambar 35. Diagram sekuens UC-08 - Skenario Alternatif 1: Belum ada hasil belajar anggota</i></p>


- **Skenario Alternatif 2: Anggota yang dipilih bukan anggota aktif kelas**

<p align="center">
<img alt="Gambar 34. Sequence Diagram UC-08 - Skenario Alternatif 2: Anggota yang dipilih bukan anggota aktif kelas" src="./assets/diagram/UC08 Alternatif 2.png" width="100%">
</p>
<p align="center"><i>Gambar 36.Diagram sekuens UC-08 - Skenario Alternatif 2: Anggota yang dipilih bukan anggota aktif kelas</i></p>


### 3.8.3 Diagram Kelas

<p align="center">
<img alt="Gambar 35. Diagram Kelas Memantau dan Menindaklanjuti Progres (UC-08)" src="./assets/diagram/UC08 Diagram Kelas.png" width="100%">
</p>
<p align="center"><i>Gambar 37. Diagram Kelas Memantau dan Menindaklanjuti Progres (UC-08)</i></p>



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
<p align="center"><i>Gambar 38. Diagram sekuens UC-09 — Skenario Normal</i></p>

- **Skenario Alternatif 1: Feedback Gagal Terkirim**
 
<p align="center">
<img src="./assets/diagram/UC09_Alternatif1.png" alt="Sequence diagram UC-09 alternatif 1 feedback gagal terkirim" width="100%">
</p>
<p align="center"><i>Gambar 39. Diagram sekuens UC-09 — Alternatif 1: Feedback Gagal Terkirim</i></p>

### 3.9.3 Diagram Kelas
 
<p align="center">
<img alt="Class Diagram UC09" src="./assets/diagram/ClassDiagram_UC09.png" width="100%">
</p>
<p align="center">
<i>Gambar 40. Diagram Kelas Menyampaikan Feedback</i>
</p>

--- 

# BAB 4: Diagram Kelas Keseluruhan

## 4.1 Diagram Kelas

<p align="center">
<img alt="Diagram Kelas Keseluruhan" src="./assets/diagram/ClassDiagram_Keseluruhan.png" width="100%">
</p>
<p align="center">
<i>Gambar 41. Diagram Kelas Perancangan Keseluruhan Ngaksara</i>
</p>

Tabel 4.1. Daftar Kelas Perancangan Keseluruhan

| ID Kelas | Nama Kelas | Atribut | Metode/Operasi |
| :--- | :--- | :--- | :--- |
| C01 | AkunPengguna | `- idAkun: Integer`<br>`- email: String`<br>`- username: String`<br>`- passwordHash: String`<br>`- peran: String`<br>`- namaTampilan: String` | `+ buatAkunBaru(data: DataPendaftaran): HasilOperasi`<br>`+ getProfil(): Data`<br>`+ verifikasiHakAkses(aksi: String): Boolean`<br>`+ getDetailAkun(): Data` |
| C02 | MateriAksara | `- idMateri: Integer`<br>`- namaMateri: String`<br>`- jenisAksara: JenisAksara`<br>`- tingkatKesulitan: Integer`<br>`- statusPublikasi: StatusPublikasi` | `+ ambilMetadata(): MetadataMateri`<br>`+ ambilTargetLatihan(mode: ModeLatihan): TargetLatihan`<br>`+ perbaruiData(data: MetadataMateri): HasilOperasi`<br>`+ simpan(data: MetadataMateri): HasilOperasi`<br>`+ ambilDaftarMateri(filter: FilterMateri): List<MateriAksara>`<br>`+ ambilMateriRekomendasi(penguasaan: Data): List<MateriAksara>` |
| C03 | RiwayatLatihan | `- idRiwayat: Integer`<br>`- idPelajar: Integer`<br>`- idMateri: Integer`<br>`- mode: ModeLatihan?`<br>`- skor: Decimal?`<br>`- tanggalPengerjaan: DateTime`<br>`- status: String`<br>`- idTugas: Integer?`<br>`- idKomponen: Integer?`<br>`- modeAktivitas: String`<br>`- waktuSelesai: DateTime?` | `+ simpanHasil(hasil: HasilLatihan): HasilOperasi`<br>`+ catatAktivitas(aktivitas: AktivitasBelajar): HasilOperasi`<br>`+ ambilRiwayat(idPelajar: Integer, periode: Periode): List<RiwayatLatihan>`<br>`+ ambilBuktiPenyelesaian(idPelajar: Integer, idTugas: Integer): BuktiPenyelesaian`<br>`+ ambilStatusProgres(idPelajar: Integer): Data`<br>`+ hitungPenguasaan(idPelajar: Integer): Data`<br>`+ ambilRiwayatPelajar(idPelajar: Integer): List<RiwayatLatihan>`<br>`+ hitungPolaKesalahan(idPelajar: Integer): Data` |
| C04 | DataFeedback | `- idFeedback: Integer`<br>`- idAkun: Integer`<br>`- kategori: KategoriFeedback`<br>`- pesan: String`<br>`- waktuKirim: DateTime`<br>`- status: StatusFeedback` | `+ simpan(data: MasukanFeedback): HasilOperasi`<br>`+ perbaruiStatus(status: StatusFeedback): HasilOperasi` |
| C05 | HalamanPendaftaran | `- formInputData: DataPendaftaran`<br>`- statusPersetujuan: Boolean` | `+ tampilkanTnC(): void`<br>`+ kirimPendaftaran(data: DataPendaftaran): void`<br>`+ tampilkanOpsiPeran(): void` |
| C06 | HalamanLogin | `- emailInput: String`<br>`- statusTampilan: String` | `+ kirimKredensial(email: String, password: String): void`<br>`+ tampilkanStatusLogin(hasil: HasilOperasi): void` |
| C07 | HalamanUtama | `- profil: Profil`<br>`- menuTersedia: List<String>` | `+ tampilkanMenu(peran: Peran): void`<br>`+ pilihMenu(kode: String): void` |
| C08 | HalamanLatihan | `- modeLatihan: ModeLatihan`<br>`- jawabanInput: JawabanLatihan`<br>`- goresan: List<Goresan>`<br>`- hasilTampilan: HasilPenilaian?` | `+ pilihModeLatihan(mode: ModeLatihan): void`<br>`+ rekamGoresan(titik: Titik): void`<br>`+ kirimJawaban(jawaban: JawabanLatihan): void`<br>`+ tampilkanHasil(hasil: HasilPenilaian): void` |
| C09 | HalamanKelolaMateri | `- daftarMateri: List<MetadataMateri>`<br>`- detailInput: DataKonten`<br>`- fileMateriInput: Berkas?` | `+ unggahFileMateri(berkas: Berkas, detail: DataKonten): void`<br>`+ suntingMateri(idMateri: Integer): void`<br>`+ tampilkanStatus(hasil: HasilOperasi): void`<br>`+ tampilkanDaftarMateri(data: List<MateriAksara>): void` |
| C10 | HalamanFeedback | `- kategoriFeedback: KategoriFeedback`<br>`- pesanFeedback: String`<br>`- statusKirim: StatusKirim` | `+ kirimFeedback(data: MasukanFeedback): void`<br>`+ tampilkanKonfirmasi(hasil: HasilOperasi): void`<br>`+ tampilkanFormFeedback(): void` |
| C11 | HalamanKelasPelajar | `- kodeKelasInput: String`<br>`- tugasAktif: Integer?`<br>`- detailTugas: DetailTugas?`<br>`- idKelasAktif: Integer` | `+ kirimKodeBergabung(kode: String): void`<br>`+ bukaTugas(idKelas: Integer, idTugas: Integer): void`<br>`+ kerjakanTugas(idKelas: Integer, idTugas: Integer): void`<br>`- finalisasiTugas(idKelas: Integer, idTugas: Integer): HasilOperasi`<br>`+ bukaKelas(idKelas: Integer): void`<br>`- tampilkanPesan(pesan: String): void`<br>`- tampilkanTugas(data: Data): void`<br>`- tampilkanDetail(data: Data): void`<br>`- tampilkanHasil(data: Data): void`<br>`+ periksaPenyelesaian(idKelas: Integer, idTugas: Integer): void` |
| C12 | HalamanKelolaKelas | `- idKelasTerpilih: Integer?`<br>`- daftarAnggota: List<Profil>`<br>`- ringkasanProgres: RingkasanProgres?`<br>`- pesanInput: String` | `+ buatKelas(data: DataKelas): void`<br>`+ buatTugas(data: DataTugas): void`<br>`+ pilihAnggota(idPelajar: Integer): void`<br>`+ kirimUmpanBalik(data: DataUmpanBalik): void`<br>`+ kelolaAnggota(idKelas: Integer): void`<br>`+ aturScoreboard(idKelas: Integer, aktif: Boolean): void`<br>`+ tampilkanAnggota(data: List<Profil>): void`<br>`+ tampilkanProgres(data: RingkasanProgres): void` |
| C13 | HalamanMateri | `- materiTerpilih: Integer?`<br>`- statusTampilan: String`<br>`- statusAudio: String` | `+ pilihMateri(idMateri: Integer): void`<br>`+ tampilkanIsiMateri(paket: PaketMateri): void`<br>`+ putarAudioPengucapan(sumber: ReferensiBerkas): void` |
| C14 | HalamanProgres | `- filterPeriode: Periode`<br>`- dataTampilan: RingkasanProgres?`<br>`- idKelasTerpilih: Integer?` | `+ tampilkanProgres(data: RingkasanProgres): void`<br>`+ pilihPeriode(periode: Periode): void`<br>`+ tampilkanScoreboard(data: List<Peringkat>): void` |
| C15 | OtentikasiController | `- konteks: KonteksPermintaan` | `+ verifikasiFormatData(data: DataPendaftaran): HasilValidasi`<br>`- hashPassword(password: String): String`<br>`+ simpanAkun(data: DataPendaftaran): HasilOperasi`<br>`+ validasiLogin(email: String, password: String): HasilOperasi` |
| C16 | LatihanController | `- konteks: KonteksPermintaan` | `+ siapkanSoal(idMateri: Integer, mode: ModeLatihan): SoalLatihan`<br>`+ nilaiJawaban(jawaban: JawabanLatihan): HasilPenilaian`<br>`+ simpanHasil(hasil: HasilLatihan): HasilOperasi` |
| C17 | MateriController | `- konteks: KonteksPermintaan` | `+ ambilDaftarMateri(filter: FilterMateri): List<MetadataMateri>`<br>`+ muatPaketMateri(idMateri: Integer, versiAplikasi: String): PaketMateri`<br>`+ ambilAudioPengucapan(idMateri: Integer): HasilBerkas`<br>`+ ambilKontenLatihan(idMateri: Integer, mode: ModeLatihan): TargetLatihan`<br>`+ tambahMateri(data: DataKonten, berkas: Berkas): HasilOperasi`<br>`+ perbaruiMateri(idMateri: Integer, data: DataKonten): HasilOperasi`<br>`+ validasiFormatFile(berkas: Berkas): HasilValidasi`<br>`+ hapusMateri(idMateri: Integer): HasilOperasi` |
| C18 | FeedbackController | `- konteks: KonteksPermintaan` | `+ validasiFeedback(data: MasukanFeedback): HasilValidasi`<br>`+ simpanFeedback(data: MasukanFeedback): HasilOperasi`<br>`+ kirimKonfirmasi(hasil: HasilOperasi): Konfirmasi` |
| C19 | ProgresController | `- konteks: KonteksPermintaan` | `+ ambilRiwayat(idPelajar: Integer, periode: Periode): List<RiwayatLatihan>`<br>`+ hitungProgres(idPelajar: Integer): RingkasanProgres`<br>`+ siapkanRekomendasi(idPelajar: Integer): List<MetadataMateri>`<br>`+ ambilScoreboard(idKelas: Integer): HasilScoreboard`<br>`+ agregasiProgres(anggotaAktif: List<Integer>, periode: Periode): RingkasanProgres`<br>`+ hitungPolaKesalahan(idPelajar: Integer): Data` |
| C20 | KelasController | `- konteks: KonteksPermintaan`<br>`- idPelajar: Integer`<br>`- idKelas: Integer` | `+ validasiKodeBergabung(kode: String): HasilOperasi`<br>`+ ambilTugasAktif(idKelas: Integer): HasilOperasi`<br>`+ simpanPengumpulan(idKelas: Integer, idTugas: Integer): HasilOperasi`<br>`+ buatKelas(data: DataKelas): HasilOperasi`<br>`+ terbitkanTugas(idKelas: Integer, data: DataTugas): HasilOperasi`<br>`+ ambilDaftarAnggota(idKelas: Integer): List<Profil>`<br>`+ kirimUmpanBalik(idKelas: Integer, data: DataUmpanBalik): HasilOperasi`<br>`+ bukaTugas(idKelas: Integer, idTugas: Integer): HasilOperasi`<br>`+ hasilkanKodeUnik(): String`<br>`+ gabungkanPelajar(idKelas: Integer): HasilOperasi`<br>`+ kelolaAnggota(idKelas: Integer, idPelajar: Integer, status: String): HasilOperasi`<br>`+ ambilKelasPengajar(): List<KelasBelajar>`<br>`+ gantiKodeKelas(idKelas: Integer): HasilOperasi`<br>`+ aturScoreboard(idKelas: Integer, aktif: Boolean): HasilOperasi`<br>`+ ambilProgresAnggota(idKelas: Integer, idPelajar: Integer): Data` |
| C21 | KontenMateri | `- idKonten: Integer`<br>`- idMateri: Integer`<br>`- bentukAksara: String`<br>`- aturan: String`<br>`- contoh: String`<br>`- pathAudio: String?`<br>`- versiKonten: String`<br>`- versiAplikasiMinimum: String` | `+ ambilKonten(versiAplikasi: String): PaketMateri`<br>`+ ambilAudio(): HasilBerkas`<br>`+ perbaruiKonten(data: DataKonten): HasilOperasi`<br>`+ arsipkanVersi(): HasilOperasi`<br>`+ simpanKonten(data: DataKonten): HasilOperasi` |
| C22 | MotivasiBelajar | `- idPelajar: Integer`<br>`- streak: Integer`<br>`- poin: Integer`<br>`- tanggalAktifTerakhir: Date?`<br>`- daftarBadge: List<String>` | `+ perbaruiStreak(tanggal: Date): void`<br>`+ tambahPoin(idAktivitas: Integer, nilai: Integer): HasilOperasi`<br>`+ berikanBadge(kode: String): HasilOperasi`<br>`+ susunScoreboard(anggotaAktif: List<Profil>): List<Peringkat>`<br>`+ ambilMotivasi(idPelajar: Integer): Data`<br>`+ ambilPoinKelas(anggotaAktif: List<Integer>): List<Peringkat>` |
| C23 | KelasBelajar | `- idKelas: Integer`<br>`- nama: String`<br>`- pemilikId: Integer`<br>`- kodeBergabung: String`<br>`- status: String`<br>`- statusScoreboard: Boolean` | `+ validasiKode(kode: String): KelasBelajar?`<br>`+ gabungkanPelajar(idPelajar: Integer): HasilOperasi`<br>`+ gantiKodeBergabung(idPengajar: Integer): HasilOperasi`<br>`+ finalisasiTugas(idPelajar: Integer, idTugas: Integer, bukti: BuktiPenyelesaian): HasilOperasi`<br>`+ periksaPemilik(idPengajar: Integer): Boolean`<br>`+ ambilDaftarTugas(idPelajar: Integer): HasilOperasi`<br>`+ bukaTugas(idPelajar: Integer, idTugas: Integer): HasilOperasi`<br>`+ simpan(data: DataKelas): HasilOperasi`<br>`+ buatKodeBergabung(): String`<br>`+ daftarKelasPengajar(idPengajar: Integer): List<KelasBelajar>`<br>`+ aturScoreboard(aktif: Boolean): HasilOperasi` |
| C24 | KeanggotaanKelas | `- idKeanggotaan: Integer`<br>`- idKelas: Integer`<br>`- idPelajar: Integer`<br>`- status: String` | `+ simpanKeanggotaan(idPelajar: Integer, idKelas: Integer): HasilOperasi`<br>`+ periksaAkses(idPelajar: Integer, idKelas: Integer): Boolean`<br>`+ daftarAnggotaAktif(idKelas: Integer): List<KeanggotaanKelas>`<br>`+ ubahStatus(status: String): HasilOperasi`<br>`- simpanAtomik(): HasilOperasi`<br>`+ daftarAnggota(idKelas: Integer): List<KeanggotaanKelas>` |
| C25 | TugasKelas | `- idTugas: Integer`<br>`- idKelas: Integer`<br>`- instruksi: String`<br>`- tenggat: DateTime`<br>`- status: StatusTugas`<br>`- komponenTugas: List<KomponenTugas>` | `+ ambilTugasAktif(idKelas: Integer): List`<br>`+ terbitkan(): HasilOperasi`<br>`+ verifikasiDanSimpan(idKelas: Integer, idTugas: Integer, idPelajar: Integer, bukti: BuktiPenyelesaian): HasilOperasi`<br>`+ ambilStatus(idPelajar: Integer, daftarTugas: List): List`<br>`+ ambilDetail(idKelas: Integer, idTugas: Integer): Data?`<br>`- periksaKomponenWajib(bukti: BuktiPenyelesaian): HasilVerifikasi`<br>`+ simpan(data: DataTugas): HasilOperasi` |
| C26 | PenyelesaianTugas | `- idPenyelesaian: Integer`<br>`- idTugas: Integer`<br>`- idPelajar: Integer`<br>`- waktuKumpul: DateTime`<br>`- status: String` | `+ simpanPengumpulan(idPelajar: Integer, idTugas: Integer, waktuSelesai: DateTime, tenggat: DateTime): HasilOperasi`<br>`- tentukanStatusKetepatan(waktuSelesai: DateTime, tenggat: DateTime): String`<br>`+ ambilStatus(idPelajar: Integer, daftarTugas: List): List`<br>`- simpanAtomik(): HasilOperasi`<br>`- batalkanTransaksi(): void` |

<br>

---

# BAB 5: Perancangan Rinci Kelas

Bagian ini diisi dengan daftar seluruh kelas dalam tabel berikut:

Tabel 5.1. Daftar Kelas Perancangan dan Kelas Analisis Terkait

| No | Nama Kelas Perancangan | Nama Kelas Analisis Terkait |
| :--- | :--- | :--- |
| 1 | `AkunPengguna` (C01) | AkunPengguna (C01) |
| 2 | `MateriAksara` (C02) | MateriAksara (C02) |
| 3 | `RiwayatLatihan` (C03) | RiwayatLatihan (C03) |
| 4 | `DataFeedback` (C04) | DataFeedback (C04) |
| 5 | `HalamanPendaftaran` (C05) | HalamanPendaftaran (C05) |
| 6 | `HalamanLogin` (C06) | HalamanLogin (C06) |
| 7 | `HalamanUtama` (C07) | HalamanUtama (C07) |
| 8 | `HalamanLatihan` (C08) | HalamanLatihan (C08) |
| 9 | `HalamanKelolaMateri` (C09) | HalamanKelolaMateri (C09) |
| 10 | `HalamanFeedback` (C10) | HalamanFeedback (C10) |
| 11 | `HalamanKelasPelajar` (C11) | HalamanKelasPelajar (C11) |
| 12 | `HalamanKelolaKelas` (C12) | HalamanKelolaKelas (C12) |
| 13 | `HalamanMateri` (C13) | HalamanMateri (C13) |
| 14 | `HalamanProgres` (C14) | HalamanProgres (C14) |
| 15 | `OtentikasiController` (C15) | OtentikasiController (C15) |
| 16 | `LatihanController` (C16) | LatihanController (C16) |
| 17 | `MateriController` (C17) | MateriController (C17) |
| 18 | `FeedbackController` (C18) | FeedbackController (C18) |
| 19 | `ProgresController` (C19) | ProgresController (C19) |
| 20 | `KelasController` (C20) | KelasController (C20) |
| 21 | `KontenMateri` (C21) | KontenMateri (C21) |
| 22 | `MotivasiBelajar` (C22) | MotivasiBelajar (C22) |
| 23 | `KelasBelajar` (C23) | KelasBelajar (C23) |
| 24 | `KeanggotaanKelas` (C24) | KeanggotaanKelas (C24) |
| 25 | `TugasKelas` (C25) | TugasKelas (C25) |
| 26 | `PenyelesaianTugas` (C26) | PenyelesaianTugas (C26) |

Untuk **setiap kelas**, identifikasi operasi berdasarkan tanggung jawab kelas beserta *visibility*-nya, serta atribut beserta *visibility* dan tipenya. Buat subbab tersendiri untuk setiap kelas.

## 5.1 Kelas AkunPengguna

**Nama Kelas:** *[Nama kelas]*  
**ID Kelas:** *[ID kelas]*

Tabel 5.1.1. Operasi Kelas [Nama Kelas 1]

| Nama Operasi | Visibility (private, public) | Keterangan |
| :--- | :--- | :--- |
| *[Signature operasi, termasuk parameter dan tipe kembalian]* | *[Visibility]* | *[Tanggung jawab operasi]* |
| *...* | *...* | *...* |

Tabel 5.1.2. Atribut Kelas [Nama Kelas 1]

| Nama Atribut | Visibility (private, public) | Tipe |
| :--- | :--- | :--- |
| *[Nama atribut]* | *[Visibility]* | *[Tipe sesuai bahasa pemrograman yang digunakan]* |
| *...* | *...* | *...* |

### 5.1.1 Algoritma/Query

Bagian ini diisi untuk kerangka algoritma proses yang dianggap cukup penting. Algoritma dapat dituliskan sebagai *pseudocode*. Implementasi *skeleton code* juga dapat digunakan untuk kelas yang sudah terdefinisi pada bahasa pemrograman tertentu.

**Nama Kelas:** *[Nama kelas]*  
**Nama Operasi:** *[Signature operasi]*  
**ID Algoritma:** *[Algo-001]*

```text
[Tuliskan pseudocode algoritma operasi di sini.]
```

Jika operasi mengacu pada query tertentu, lengkapi tabel berikut. 

Tabel 5.1.1. Query Operasi [Nama Operasi]

| No Query | Query | Keterangan |
| :--- | :--- | :--- |
| *[Q-001]* | *[Tuliskan query]* | *[Fungsi query]* |
| *...* | *...* | *...* |


### 5.1.2 Algoritma/Query

*[Ulangi pola 5.1.1 untuk operasi penting berikutnya pada kelas yang sama, jika ada.]*

## 5.2 Kelas MateriAksara

**Nama Kelas:** MateriAksara  
**ID Kelas:** C02

Tabel 5.2.1. Operasi Kelas MateriAksara

| Nama Operasi | Visibility (private, public) | Keterangan |
| :--- | :--- | :--- |
| `ambilMetadata(): MetadataMateri` | public | Mengemas `idMateri`, `namaMateri`, `jenisAksara`, `tingkatKesulitan`, dan `statusPublikasi` milik objek ini menjadi `MetadataMateri` untuk daftar materi. |
| `ambilTargetLatihan(mode: ModeLatihan): TargetLatihan` | public | Menyusun daftar aksara/kata target latihan dari konten materi sesuai mode (menulis, bunyi, merangkai). |
| `perbaruiData(data: MetadataMateri): HasilOperasi` | public | Memperbarui nama, jenis aksara, tingkat kesulitan, dan status publikasi materi. |
| `simpan(data: MetadataMateri): HasilOperasi` | public | Menyimpan metadata materi baru dan mengembalikan `idMateri` yang terbentuk. |
| `ambilDaftarMateri(filter: FilterMateri): List<MateriAksara>` | public | Mengambil materi yang belum dihapus sesuai filter jenis aksara, tingkat, dan cakupan status publikasi. |
| `ambilMateriRekomendasi(penguasaan: Data): List<MateriAksara>` | public | Memilih materi terbit yang belum dikuasai pelajar berdasarkan data penguasaan. |

Tabel 5.2.2. Atribut Kelas MateriAksara

| Nama Atribut | Visibility (private, public) | Tipe |
| :--- | :--- | :--- |
| `idMateri` | private | Integer |
| `namaMateri` | private | String |
| `jenisAksara` | private | JenisAksara |
| `tingkatKesulitan` | private | Integer |
| `statusPublikasi` | private | StatusPublikasi |

### 5.2.1 Algoritma/Query

**Nama Kelas:** MateriAksara  
**Nama Operasi:** `ambilDaftarMateri(filter: FilterMateri): List<MateriAksara>`  
**ID Algoritma:** Algo-...

```text
FUNCTION ambilDaftarMateri(filter: FilterMateri): List<MateriAksara>
  // filter.jenisAksara (nullable), filter.tingkatKesulitan (nullable),
  // filter.termasukDraf (Boolean; true hanya untuk Tim Materi)
  baris ← jalankan Q-040.1 dengan parameter filter
  daftar ← list kosong
  FOR EACH b IN baris DO
    daftar.tambah( bentuk MateriAksara dari b )
  RETURN daftar            // daftar kosong BUKAN galat; tampilan menampilkan pesan "belum tersedia"
```

Tabel 5.2.3. Query Operasi ambilDaftarMateri

| No Query | Query | Keterangan |
| :--- | :--- | :--- |
| Q-040.1 | `SELECT id_materi, nama_materi, jenis_aksara, tingkat_kesulitan, status_publikasi FROM materi_aksara WHERE deleted_at IS NULL AND (:jenis IS NULL OR jenis_aksara = :jenis) AND (:tingkat IS NULL OR tingkat_kesulitan = :tingkat) AND (:termasuk_draf = 1 OR status_publikasi = 'TERBIT') ORDER BY jenis_aksara, tingkat_kesulitan, nama_materi;` | Mengambil materi sesuai filter; pelajar hanya melihat `TERBIT`, Tim Materi dapat melihat `DRAF`. |

### 5.2.2 Algoritma/Query

**Nama Kelas:** MateriAksara  
**Nama Operasi:** `ambilMateriRekomendasi(penguasaan: Data): List<MateriAksara>`  
**ID Algoritma:** Algo-...

```text
FUNCTION ambilMateriRekomendasi(penguasaan: Data): List<MateriAksara>
  // penguasaan: pemetaan idMateri → skor 0..100 (hasil RiwayatLatihan.hitungPenguasaan)
  AMBANG ← 70
  semua ← jalankan Q-041        // materi TERBIT, urut jenis, tingkat
  kandidat ← list kosong

  FOR EACH m IN semua DO
    skor ← penguasaan[m.idMateri]            // null bila belum pernah dilatih
    IF skor = null OR skor < AMBANG THEN
      // tingkat terbuka bila seluruh materi tingkat di bawahnya (jenis sama) sudah ≥ AMBANG
      IF semuaTingkatDiBawahTuntas(m, semua, penguasaan, AMBANG) THEN
        kandidat.tambah(m)

  urutkan kandidat berdasarkan (tingkatKesulitan ASC, skor ASC; skor null dianggap 0)
  RETURN kandidat.ambil(3 teratas)
```

Tabel 5.2.4. Query Operasi ambilMateriRekomendasi

| No Query | Query | Keterangan |
| :--- | :--- | :--- |
| Q-041 | `SELECT id_materi, nama_materi, jenis_aksara, tingkat_kesulitan, status_publikasi FROM materi_aksara WHERE deleted_at IS NULL AND status_publikasi = 'TERBIT' ORDER BY jenis_aksara, tingkat_kesulitan, id_materi;` | Mengambil seluruh materi terbit sebagai kandidat; penyaringan skor dilakukan pada kode. |

### 5.2.3 Algoritma/Query

**Nama Kelas:** MateriAksara  
**Nama Operasi:** `ambilTargetLatihan(mode: ModeLatihan): TargetLatihan`  
**ID Algoritma:** Algo-...

```text
FUNCTION ambilTargetLatihan(mode: ModeLatihan): TargetLatihan
  baris ← jalankan Q-042 dengan idMateri = this.idMateri
  IF baris = null THEN
    RETURN TargetLatihan.kosong()            // materi belum punya konten

  SWITCH mode
    CASE MENULIS, BUNYI:
      butir ← pecah(baris.bentuk_aksara)      // satu aksara per elemen
    CASE MERANGKAI:
      butir ← pecah(baris.contoh)             // kata/rangkaian contoh

  RETURN TargetLatihan(idMateri = this.idMateri, mode = mode, butir = butir)
```

Tabel 5.2.5. Query Operasi ambilTargetLatihan

| No Query | Query | Keterangan |
| :--- | :--- | :--- |
| Q-042 | `SELECT bentuk_aksara, contoh FROM konten_materi WHERE id_materi = :id_materi;` | Mengambil bahan target latihan dari konten materi (relasi komposisi MateriAksara–KontenMateri). |

### 5.2.4 Algoritma/Query

**Nama Kelas:** MateriAksara  
**Nama Operasi:** `simpan(data: MetadataMateri): HasilOperasi`  
**ID Algoritma:** Algo-...

```text
FUNCTION simpan(data: MetadataMateri): HasilOperasi
  IF kosong(trim(data.namaMateri)) OR panjang(data.namaMateri) > 100 THEN
    RETURN HasilOperasi.gagal("NAMA_TIDAK_VALID", "Nama materi wajib diisi (maks. 100 karakter)")
  IF data.jenisAksara NOT IN {JAWA, SUNDA} OR data.tingkatKesulitan NOT IN {1,2,3} THEN
    RETURN HasilOperasi.gagal("DATA_TIDAK_VALID", "Jenis aksara atau tingkat tidak valid")

  jumlah ← jalankan Q-043.1
  IF jumlah > 0 THEN
    RETURN HasilOperasi.gagal("DUPLIKAT", "Materi dengan nama, jenis, dan tingkat sama sudah ada")

  status ← IF data.terbitkan THEN TERBIT ELSE DRAF
  idBaru ← jalankan Q-043.2
  RETURN HasilOperasi.sukses(idMateri = idBaru)
```

Tabel 5.2.6. Query Operasi simpan

| No Query | Query | Keterangan |
| :--- | :--- | :--- |
| Q-043.1 | `SELECT COUNT(*) FROM materi_aksara WHERE nama_materi = :nama AND jenis_aksara = :jenis AND tingkat_kesulitan = :tingkat AND deleted_at IS NULL;` | Mencegah materi ganda. |
| Q-043.2 | `INSERT INTO materi_aksara (nama_materi, jenis_aksara, tingkat_kesulitan, status_publikasi) VALUES (:nama, :jenis, :tingkat, :status);` | Menyimpan metadata materi baru. |

### 5.2.5 Algoritma/Query

**Nama Kelas:** MateriAksara  
**Nama Operasi:** `perbaruiData(data: MetadataMateri): HasilOperasi`  
**ID Algoritma:** Algo-...

```text
FUNCTION perbaruiData(data: MetadataMateri): HasilOperasi
  jumlah ← jalankan Q-044.1 (kecuali baris milik this.idMateri)
  IF jumlah > 0 THEN
    RETURN HasilOperasi.gagal("DUPLIKAT", "Materi dengan nama, jenis, dan tingkat sama sudah ada")

  baris ← jalankan Q-044.2
  IF baris = 0 THEN
    RETURN HasilOperasi.gagal("TIDAK_DITEMUKAN", "Materi tidak ditemukan")

  this.namaMateri ← data.namaMateri
  this.jenisAksara ← data.jenisAksara
  this.tingkatKesulitan ← data.tingkatKesulitan
  this.statusPublikasi ← data.statusPublikasi
  RETURN HasilOperasi.sukses(idMateri = this.idMateri)
```

Tabel 5.2.7. Query Operasi perbaruiData

| No Query | Query | Keterangan |
| :--- | :--- | :--- |
| Q-044.1 | `SELECT COUNT(*) FROM materi_aksara WHERE nama_materi = :nama AND jenis_aksara = :jenis AND tingkat_kesulitan = :tingkat AND id_materi <> :id AND deleted_at IS NULL;` | Cek duplikat selain materi ini. |
| Q-044.2 | `UPDATE materi_aksara SET nama_materi = :nama, jenis_aksara = :jenis, tingkat_kesulitan = :tingkat, status_publikasi = :status WHERE id_materi = :id AND deleted_at IS NULL;` | Memperbarui metadata. |

## 5.3 Kelas RiwayatLatihan

## 5.4 Kelas DataFeedback

## 5.5 Kelas HalamanPendaftaran

## 5.6 Kelas HalamanLogin

## 5.7 Kelas HalamanUtama

## 5.8 Kelas HalamanLatihan

## 5.9 Kelas HalamanKelolaMateri

**Nama Kelas:** HalamanKelolaMateri  
**ID Kelas:** C09

Tabel 5.9.1. Operasi Kelas HalamanKelolaMateri

| Nama Operasi | Visibility (private, public) | Keterangan |
| :--- | :--- | :--- |
| `unggahFileMateri(berkas: Berkas, detail: DataKonten): void` | public | Menerima berkas dan detail dari form I-19, memeriksa berkas di sisi tampilan, lalu meneruskannya ke `MateriController.tambahMateri`. |
| `suntingMateri(idMateri: Integer): void` | public | Memuat data materi terpilih ke form, lalu meneruskan perubahan ke `MateriController.perbaruiMateri`. |
| `tampilkanStatus(hasil: HasilOperasi): void` | public | Menampilkan notifikasi berhasil/gagal dari hasil operasi. |
| `tampilkanDaftarMateri(data: List<MateriAksara>): void` | public | Merender tabel daftar materi pada I-18. |

Tabel 5.9.2. Atribut Kelas HalamanKelolaMateri

| Nama Atribut | Visibility (private, public) | Tipe |
| :--- | :--- | :--- |
| `daftarMateri` | private | List<MetadataMateri> |
| `detailInput` | private | DataKonten |
| `fileMateriInput` | private | Berkas? |

### 5.9.1 Algoritma/Query

**Nama Kelas:** HalamanKelolaMateri  
**Nama Operasi:** `unggahFileMateri(berkas: Berkas, detail: DataKonten): void`  
**ID Algoritma:** Algo-...

```text
PROCEDURE unggahFileMateri(berkas: Berkas, detail: DataKonten)
  IF berkas = null THEN
    tampilkanStatus(HasilOperasi.gagal("BERKAS_KOSONG", "Pilih berkas materi terlebih dahulu"))
    RETURN
  IF ekstensi(berkas.nama) <> "zip" THEN                 // pemeriksaan cepat di tampilan (I-19 Alt 1)
    fileMateriInput ← null
    tampilkanStatus(HasilOperasi.gagal("FORMAT_TIDAK_DIDUKUNG", "Jenis berkas tidak didukung"))
    RETURN

  fileMateriInput ← berkas
  detailInput ← detail
  hasil ← MateriController.tambahMateri(detailInput, fileMateriInput)   // validasi server di dalamnya

  tampilkanStatus(hasil)
  IF hasil.berhasil THEN
    fileMateriInput ← null
    detailInput ← kosong
    // kembali ke I-18 dan memuat ulang daftar
    tampilkanDaftarMateri( MateriController.ambilDaftarMateri(FilterMateri.semuaTermasukDraf()) )
```

### 5.9.2 Algoritma/Query

**Nama Kelas:** HalamanKelolaMateri  
**Nama Operasi:** `suntingMateri(idMateri: Integer): void`  
**ID Algoritma:** Algo-...

```text
PROCEDURE suntingMateri(idMateri: Integer)
  target ← cari elemen pada daftarMateri dengan idMateri = idMateri
  IF target = null THEN
    tampilkanStatus(HasilOperasi.gagal("TIDAK_DITEMUKAN", "Materi tidak ditemukan"))
    RETURN

  detailInput ← isi form dari target                       // nama, jenis, tingkat, status
  data ← tunggu pengguna menekan "Simpan" (atau batal → RETURN)
  hasil ← MateriController.perbaruiMateri(idMateri, data)

  tampilkanStatus(hasil)
  IF hasil.berhasil THEN
    tampilkanDaftarMateri( MateriController.ambilDaftarMateri(FilterMateri.semuaTermasukDraf()) )
```

## 5.10 Kelas HalamanFeedback

## 5.11 Kelas HalamanKelasPelajar

## 5.12 Kelas HalamanKelolaKelas

## 5.13 Kelas HalamanMateri

**Nama Kelas:** HalamanMateri  
**ID Kelas:** C13

Tabel 5.13.1. Operasi Kelas HalamanMateri

| Nama Operasi | Visibility (private, public) | Keterangan |
| :--- | :--- | :--- |
| `pilihMateri(idMateri: Integer): void` | public | Menangani pemilihan materi pada daftar, lalu meminta paket materi dari `MateriController`. |
| `tampilkanIsiMateri(paket: PaketMateri): void` | public | Merender bentuk aksara, aturan penulisan, contoh, tombol audio, dan tombol latihan pada I-05. |
| `putarAudioPengucapan(sumber: ReferensiBerkas): void` | public | Memutar audio pengucapan dan mengelola status audio. |

Tabel 5.13.2. Atribut Kelas HalamanMateri

| Nama Atribut | Visibility (private, public) | Tipe |
| :--- | :--- | :--- |
| `materiTerpilih` | private | Integer? |
| `statusTampilan` | private | String |
| `statusAudio` | private | String |

### 5.13.1 Algoritma/Query

**Nama Kelas:** HalamanMateri  
**Nama Operasi:** `tampilkanIsiMateri(paket: PaketMateri): void`  
**ID Algoritma:** Algo-...

```text
PROCEDURE tampilkanIsiMateri(paket: PaketMateri)
  IF paket = null OR paket.galat <> null THEN
    statusTampilan ← "GALAT"
    tampilkan pesan galat paket.galat ("Materi belum dapat dimuat")
    RETURN

  render judul ← paket.namaMateri, chip status ← paket.statusProgres   // "Belum tuntas" / "Tuntas"
  render bentuk aksara ← paket.bentukAksara
  render aturan penulisan ← paket.aturan
  render contoh ← paket.contoh
  render info versi ← "Konten materi versi " + paket.versiKonten

  IF paket.audio = null THEN
    statusAudio ← "TIDAK_TERSEDIA"
    nonaktifkan tombol "Putar Pengucapan"
  ELSE
    statusAudio ← "SIAP"
    aktifkan tombol "Putar Pengucapan"

  statusTampilan ← "SIAP"
```

### 5.13.2 Algoritma/Query

**Nama Kelas:** HalamanMateri  
**Nama Operasi:** `putarAudioPengucapan(sumber: ReferensiBerkas): void`  
**ID Algoritma:** Algo-...

```text
PROCEDURE putarAudioPengucapan(sumber: ReferensiBerkas)
  IF sumber = null THEN
    statusAudio ← "TIDAK_TERSEDIA"
    tampilkan alert galat "Audio pengucapan belum tersedia"      // I-05 Alt 2
    RETURN

  statusAudio ← "MEMUAT"
  hasil ← MateriController.ambilAudioPengucapan(materiTerpilih)

  IF hasil.berhasil = false THEN
    statusAudio ← "GALAT"
    tampilkan alert galat "Audio tidak dapat diputar, coba lagi"
    RETURN

  putar hasil.berkas pada pemutar audio peramban
  statusAudio ← "MEMUTAR"
  SAAT pemutaran selesai: statusAudio ← "SIAP"
```

## 5.14 Kelas HalamanProgres

## 5.15 Kelas OtentikasiController

## 5.16 Kelas LatihanController

## 5.17 Kelas MateriController

**Nama Kelas:** MateriController  
**ID Kelas:** C17

Tabel 5.17.1. Operasi Kelas MateriController

| Nama Operasi | Visibility (private, public) | Keterangan |
| :--- | :--- | :--- |
| `ambilDaftarMateri(filter: FilterMateri): List<MetadataMateri>` | public | Mengambil daftar materi dari `MateriAksara`, mengubahnya menjadi `MetadataMateri`, dan melampirkan status progres pelajar. |
| `muatPaketMateri(idMateri: Integer, versiAplikasi: String): PaketMateri` | public | Menyusun `PaketMateri` (isi materi, versi, status progres) untuk layar Detail Materi. |
| `ambilAudioPengucapan(idMateri: Integer): HasilBerkas` | public | Mengambil berkas audio pengucapan materi dari `KontenMateri`. |
| `ambilKontenLatihan(idMateri: Integer, mode: ModeLatihan): TargetLatihan` | public | Meneruskan permintaan ke `MateriAksara.ambilTargetLatihan(mode)` untuk UC-03. |
| `tambahMateri(data: DataKonten, berkas: Berkas): HasilOperasi` | public | Menambah materi baru (metadata + konten) dari berkas yang diunggah. |
| `perbaruiMateri(idMateri: Integer, data: DataKonten): HasilOperasi` | public | Memperbarui metadata dan/atau konten materi. |
| `validasiFormatFile(berkas: Berkas): HasilValidasi` | public | Memeriksa ekstensi, tipe, ukuran, dan isi berkas materi. |
| `hapusMateri(idMateri: Integer): HasilOperasi` | public | Menghapus materi (penghapusan lunak). |

Tabel 5.17.2. Atribut Kelas MateriController

| Nama Atribut | Visibility (private, public) | Tipe |
| :--- | :--- | :--- |
| `konteks` | private | KonteksPermintaan |

### 5.17.1 Algoritma/Query

**Nama Kelas:** MateriController  
**Nama Operasi:** `ambilDaftarMateri(filter: FilterMateri): List<MetadataMateri>`  
**ID Algoritma:** Algo-...

```text
FUNCTION ambilDaftarMateri(filter: FilterMateri): List<MetadataMateri>
  IF filter.termasukDraf = true AND NOT AkunPengguna.verifikasiHakAkses("kelola_materi") THEN
    filter.termasukDraf ← false                     // pelajar tidak boleh melihat draf

  daftarEntitas ← MateriAksara.ambilDaftarMateri(filter)              
  progres ← null
  IF konteks.peran = "PELAJAR" THEN
    progres ← RiwayatLatihan.ambilStatusProgres(konteks.idPengguna)     // idMateri → status

  hasil ← list kosong
  FOR EACH m IN daftarEntitas DO
    meta ← m.ambilMetadata()
    meta.statusProgres ← IF progres = null THEN null ELSE progres[m.idMateri] ?? "BELUM"
    hasil.tambah(meta)
  RETURN hasil
```

### 5.17.2 Algoritma/Query

**Nama Kelas:** MateriController  
**Nama Operasi:** `muatPaketMateri(idMateri: Integer, versiAplikasi: String): PaketMateri`  
**ID Algoritma:** Algo-...

```text
FUNCTION muatPaketMateri(idMateri: Integer, versiAplikasi: String): PaketMateri
  materi ← MateriAksara.find(idMateri)                    
  IF materi = null OR materi.statusPublikasi <> TERBIT THEN
    RETURN PaketMateri.galat("MATERI_TIDAK_TERSEDIA")

  paket ← materi.konten.ambilKonten(versiAplikasi)           
  IF paket.galat <> null THEN
    RETURN paket

  paket.namaMateri ← materi.ambilMetadata().namaMateri
  progres ← RiwayatLatihan.ambilStatusProgres(konteks.idPengguna)
  paket.statusProgres ← progres[idMateri] ?? "BELUM"
  RETURN paket
```

### 5.17.3 Algoritma/Query

**Nama Kelas:** MateriController  
**Nama Operasi:** `ambilAudioPengucapan(idMateri: Integer): HasilBerkas`  
**ID Algoritma:** Algo-...

```text
FUNCTION ambilAudioPengucapan(idMateri: Integer): HasilBerkas
  materi ← MateriAksara.find(idMateri)
  IF materi = null OR materi.statusPublikasi <> TERBIT THEN
    RETURN HasilBerkas.gagal("MATERI_TIDAK_TERSEDIA")
  RETURN materi.konten.ambilAudio()                         
```

### 5.17.4 Algoritma/Query

**Nama Kelas:** MateriController  
**Nama Operasi:** `validasiFormatFile(berkas: Berkas): HasilValidasi`  
**ID Algoritma:** Algo-...

```text
FUNCTION validasiFormatFile(berkas: Berkas): HasilValidasi
  BATAS ← 20 * 1024 * 1024                                   // 20 MB
  IF berkas = null THEN
    RETURN HasilValidasi.tidakValid("Berkas belum dipilih")
  IF ekstensi(berkas.nama) <> "zip" OR berkas.mime <> "application/zip" THEN
    RETURN HasilValidasi.tidakValid("Jenis berkas tidak didukung")
  IF berkas.ukuran > BATAS THEN
    RETURN HasilValidasi.tidakValid("Ukuran berkas melebihi 20 MB")

  arsip ← buka berkas sebagai zip
  IF arsip gagal dibuka THEN
    RETURN HasilValidasi.tidakValid("Berkas zip rusak")
  IF "konten.json" TIDAK ADA di arsip THEN
    RETURN HasilValidasi.tidakValid("konten.json tidak ditemukan dalam paket")
  isi ← urai JSON konten.json
  IF isi.bentukAksara, isi.aturan, atau isi.contoh kosong THEN
    RETURN HasilValidasi.tidakValid("Isi materi belum lengkap")
  RETURN HasilValidasi.valid()
```

### 5.17.5 Algoritma/Query

**Nama Kelas:** MateriController  
**Nama Operasi:** `tambahMateri(data: DataKonten, berkas: Berkas): HasilOperasi`  
**ID Algoritma:** Algo-...

```text
FUNCTION tambahMateri(data: DataKonten, berkas: Berkas): HasilOperasi
  IF NOT AkunPengguna.verifikasiHakAkses("kelola_materi") THEN
    RETURN HasilOperasi.gagal("AKSES_DITOLAK", "Anda tidak berhak mengelola materi")

  validasi ← validasiFormatFile(berkas)                     
  IF validasi.valid = false THEN
    RETURN HasilOperasi.gagal("FORMAT_TIDAK_VALID", validasi.pesan)

  isi ← ekstrak konten.json dan audio/* dari berkas ke direktori sementara
  pathAudio ← null
  TRY
    DB::transaction:
      hasilMateri ← new MateriAksara().simpan(data.metadata)            
      IF hasilMateri.berhasil = false THEN
        ROLLBACK; RETURN hasilMateri

      IF isi.audio ada THEN
        pathAudio ← Storage.simpan(isi.audio, "materi/" + hasilMateri.idMateri + "/")
      dataKonten ← data dengan idMateri = hasilMateri.idMateri, bentukAksara/aturan/contoh dari isi,
                   pathAudio = pathAudio
      hasilKonten ← new KontenMateri().simpanKonten(dataKonten)        
      IF hasilKonten.berhasil = false THEN
        ROLLBACK; RETURN hasilKonten
    COMMIT
  CATCH galat
    hapus pathAudio dari Storage bila sudah tersimpan
    RETURN HasilOperasi.gagal("SIMPAN_GAGAL", "Materi gagal disimpan, coba lagi")

  RETURN HasilOperasi.sukses(idMateri = hasilMateri.idMateri)
```

### 5.17.6 Algoritma/Query

**Nama Kelas:** MateriController  
**Nama Operasi:** `perbaruiMateri(idMateri: Integer, data: DataKonten): HasilOperasi`  
**ID Algoritma:** Algo-...

```text
FUNCTION perbaruiMateri(idMateri: Integer, data: DataKonten): HasilOperasi
  IF NOT AkunPengguna.verifikasiHakAkses("kelola_materi") THEN
    RETURN HasilOperasi.gagal("AKSES_DITOLAK", "Anda tidak berhak mengelola materi")

  materi ← MateriAksara.find(idMateri)
  IF materi = null THEN
    RETURN HasilOperasi.gagal("TIDAK_DITEMUKAN", "Materi tidak ditemukan")

  DB::transaction:
    hasil ← materi.perbaruiData(data.metadata)                          
    IF hasil.berhasil = false THEN ROLLBACK; RETURN hasil END IF

    IF data.memuatKonten THEN
      hasil ← materi.konten.perbaruiKonten(data)                       
      IF hasil.berhasil = false THEN ROLLBACK; RETURN hasil END IF
  COMMIT

  RETURN HasilOperasi.sukses(idMateri = idMateri)
```

### 5.17.7 Algoritma/Query

**Nama Kelas:** MateriController  
**Nama Operasi:** `hapusMateri(idMateri: Integer): HasilOperasi`  
**ID Algoritma:** Algo-...

```text
FUNCTION hapusMateri(idMateri: Integer): HasilOperasi
  IF NOT AkunPengguna.verifikasiHakAkses("kelola_materi") THEN
    RETURN HasilOperasi.gagal("AKSES_DITOLAK", "Anda tidak berhak mengelola materi")

  materi ← MateriAksara.find(idMateri)
  IF materi = null THEN
    RETURN HasilOperasi.gagal("TIDAK_DITEMUKAN", "Materi tidak ditemukan")

  baris ← jalankan Q-055                                      // penghapusan lunak
  IF baris = 0 THEN
    RETURN HasilOperasi.gagal("HAPUS_GAGAL", "Materi gagal dihapus")
  RETURN HasilOperasi.sukses(idMateri = idMateri)
```

Tabel 5.17.3. Query Operasi hapusMateri

| No Query | Query | Keterangan |
| :--- | :--- | :--- |
| Q-055 | `UPDATE materi_aksara SET deleted_at = NOW(), status_publikasi = 'DRAF' WHERE id_materi = :id AND deleted_at IS NULL;` | Penghapusan lunak: materi hilang dari daftar tetapi `riwayat_latihan` pelajar tetap utuh. |

## 5.18 Kelas FeedbackController

## 5.19 Kelas ProgresController

## 5.20 Kelas KelasController

## 5.21 Kelas KontenMateri

**Nama Kelas:** KontenMateri  
**ID Kelas:** C21

Tabel 5.21.1. Operasi Kelas KontenMateri

| Nama Operasi | Visibility (private, public) | Keterangan |
| :--- | :--- | :--- |
| `ambilKonten(versiAplikasi: String): PaketMateri` | public | Mengambil konten versi terbaru yang kompatibel dengan versi aplikasi pemanggil. |
| `ambilAudio(): HasilBerkas` | public | Mengambil berkas audio pengucapan dari penyimpanan. |
| `perbaruiKonten(data: DataKonten): HasilOperasi` | public | Mengarsipkan versi lama lalu menimpa konten dengan versi baru. |
| `arsipkanVersi(): HasilOperasi` | public | Menyalin konten saat ini ke tabel arsip. |
| `simpanKonten(data: DataKonten): HasilOperasi` | public | Menyimpan konten materi baru sebagai versi 1.0. |

Tabel 5.21.2. Atribut Kelas KontenMateri

| Nama Atribut | Visibility (private, public) | Tipe |
| :--- | :--- | :--- |
| `idKonten` | private | Integer |
| `idMateri` | private | Integer |
| `bentukAksara` | private | String |
| `aturan` | private | String |
| `contoh` | private | String |
| `pathAudio` | private | String? |
| `versiKonten` | private | String |
| `versiAplikasiMinimum` | private | String |

### 5.21.1 Algoritma/Query

**Nama Kelas:** KontenMateri  
**Nama Operasi:** `ambilKonten(versiAplikasi: String): PaketMateri`  
**ID Algoritma:** Algo-...

```text
FUNCTION ambilKonten(versiAplikasi: String): PaketMateri
  // Konten terkini dipakai bila kompatibel; bila tidak, pakai versi arsip terbaru yang kompatibel
  IF version_compare(this.versiAplikasiMinimum, versiAplikasi, "<=") THEN
    RETURN PaketMateri(bentukAksara, aturan, contoh, audio = referensi(pathAudio), versiKonten)

  daftarArsip ← jalankan Q-056 dengan idMateri = this.idMateri       // urut terbaru
  FOR EACH a IN daftarArsip DO
    IF version_compare(a.versi_aplikasi_minimum, versiAplikasi, "<=") THEN
      RETURN PaketMateri(a.bentuk_aksara, a.aturan, a.contoh, referensi(a.path_audio), a.versi_konten)

  RETURN PaketMateri.galat("KONTEN_TIDAK_KOMPATIBEL")          // pengguna diminta memperbarui aplikasi
```

Tabel 5.21.3. Query Operasi ambilKonten

| No Query | Query | Keterangan |
| :--- | :--- | :--- |
| Q-056 | `SELECT bentuk_aksara, aturan, contoh, path_audio, versi_konten, versi_aplikasi_minimum FROM konten_materi_arsip WHERE id_materi = :id_materi ORDER BY diarsipkan_pada DESC;` | Mencari versi arsip terbaru yang masih cocok dengan versi aplikasi. |

### 5.21.2 Algoritma/Query

**Nama Kelas:** KontenMateri  
**Nama Operasi:** `simpanKonten(data: DataKonten): HasilOperasi`  
**ID Algoritma:** Algo-...

```text
FUNCTION simpanKonten(data: DataKonten): HasilOperasi
  IF kosong(data.bentukAksara) OR kosong(data.aturan) OR kosong(data.contoh) THEN
    RETURN HasilOperasi.gagal("KONTEN_TIDAK_LENGKAP", "Bentuk aksara, aturan, dan contoh wajib diisi")

  versi ← "1.0"
  idBaru ← jalankan Q-057
  this.idKonten ← idBaru;  this.versiKonten ← versi
  RETURN HasilOperasi.sukses(idKonten = idBaru)
```

Tabel 5.21.4. Query Operasi simpanKonten

| No Query | Query | Keterangan |
| :--- | :--- | :--- |
| Q-057 | `INSERT INTO konten_materi (id_materi, bentuk_aksara, aturan, contoh, path_audio, versi_konten, versi_aplikasi_minimum) VALUES (:id_materi, :bentuk, :aturan, :contoh, :path_audio, '1.0', :versi_min);` | Menyimpan konten pertama sebuah materi. |

### 5.21.3 Algoritma/Query

**Nama Kelas:** KontenMateri  
**Nama Operasi:** `perbaruiKonten(data: DataKonten): HasilOperasi`  
**ID Algoritma:** Algo-...

```text
FUNCTION perbaruiKonten(data: DataKonten): HasilOperasi
  IF kosong(data.bentukAksara) OR kosong(data.aturan) OR kosong(data.contoh) THEN
    RETURN HasilOperasi.gagal("KONTEN_TIDAK_LENGKAP", "Bentuk aksara, aturan, dan contoh wajib diisi")
  IF data sama dengan isi saat ini THEN
    RETURN HasilOperasi.sukses(idKonten = this.idKonten)       // tidak ada perubahan

  hasilArsip ← arsipkanVersi()                                 
  IF hasilArsip.berhasil = false THEN
    RETURN hasilArsip                                          // jangan menimpa tanpa arsip

  versiBaru ← naikkanMinor(this.versiKonten)                   // "1.0" → "1.1"
  baris ← jalankan Q-058
  IF baris = 0 THEN
    RETURN HasilOperasi.gagal("PERBARUI_GAGAL", "Konten gagal diperbarui")

  perbarui atribut this.* dari data;  this.versiKonten ← versiBaru
  RETURN HasilOperasi.sukses(idKonten = this.idKonten)
```

Tabel 5.21.5. Query Operasi perbaruiKonten

| No Query | Query | Keterangan |
| :--- | :--- | :--- |
| Q-058 | `UPDATE konten_materi SET bentuk_aksara = :bentuk, aturan = :aturan, contoh = :contoh, path_audio = :path_audio, versi_konten = :versi_baru, versi_aplikasi_minimum = :versi_min WHERE id_konten = :id_konten;` | Menimpa konten dengan versi baru. |

### 5.21.4 Algoritma/Query

**Nama Kelas:** KontenMateri  
**Nama Operasi:** `arsipkanVersi(): HasilOperasi`  
**ID Algoritma:** Algo-...

```text
FUNCTION arsipkanVersi(): HasilOperasi
  baris ← jalankan Q-059 dengan idKonten = this.idKonten
  IF baris = 0 THEN
    RETURN HasilOperasi.gagal("ARSIP_GAGAL", "Versi konten gagal diarsipkan")
  RETURN HasilOperasi.sukses(idKonten = this.idKonten)
```

Tabel 5.21.6. Query Operasi arsipkanVersi

| No Query | Query | Keterangan |
| :--- | :--- | :--- |
| Q-059 | `INSERT INTO konten_materi_arsip (id_konten, id_materi, bentuk_aksara, aturan, contoh, path_audio, versi_konten, versi_aplikasi_minimum, diarsipkan_pada) SELECT id_konten, id_materi, bentuk_aksara, aturan, contoh, path_audio, versi_konten, versi_aplikasi_minimum, NOW() FROM konten_materi WHERE id_konten = :id_konten;` | Menyalin versi konten saat ini ke arsip sebelum ditimpa. |

## 5.22 Kelas MotivasiBelajar

## 5.23 Kelas KelasBelajar

## 5.24 Kelas KeanggotaanKelas

## 5.25 Kelas TugasKelas

## 5.26 Kelas PenyelesaianTugas

---

# BAB 6: Perancangan Antarmuka

## 6.1 Rancangan Antarmuka I-01

Masukkan versi awal prototipe antarmuka, dimana untuk setiap antarmuka/layar, tuliskan spesifikasi rinci objek yang ditampilkan dan reaksi sistem terhadap interaksi pengguna.

**Antarmuka:** *[ID layar]*  
**Nama Layar:** *[Nama layar]*

<p align="center">
<img alt="Contoh Logical View pada P/L E-Commerce" src="./assets/diagram/contoh-design.png" width="50%">
</p>
<p align="center">
<i>Gambar 6.1. Rancangan Antarmuka [ID Layar] - [Nama Layar]</i>
</p>

Tabel 6.1. Spesifikasi Objek Antarmuka [ID Layar]

| Id_Objek | Jenis | Konten | Keterangan |
| :--- | :--- | :--- | :--- |
| *[ID objek]* | *[Jenis objek, misalnya Button, RTF Box, dll]* | *[String yang tampil pada layar]* | *[Reaksi sistem, layar tujuan, tautan, atau ID algoritma yang dijalankan]* |
| *[ID objek]* | *[Jenis objek]* | *[Konten yang ditampilkan]* | *[Reaksi sistem atau nama file terkait beserta deskripsi ringkasnya]* |
| *...* | *...* | *...* | *...* |

## 6.2 Rancangan Antarmuka I-02

## 6.3 Rancangan Antarmuka I-03

## 6.4 Rancangan Antarmuka I-04

**Antarmuka:** I-04  
**Nama Layar:** Daftar Materi

<p align="center">
<img alt="Rancangan Antarmuka I-04 Daftar Materi" src="./assets/diagram/I-04 Daftar Materi.png" width="70%">
</p>
<p align="center">
<i>Gambar 6.4. Rancangan Antarmuka I-04 - Daftar Materi</i>
</p>

Tabel 6.4. Spesifikasi Objek Antarmuka I-04

| Id_Objek | Jenis | Konten | Keterangan |
| :--- | :--- | :--- | :--- |
| I-04-01 | Card (header halaman) | Judul "Daftar Materi" dan deskripsi singkat | Menampilkan judul halaman untuk Pelajar; tidak interaktif. |
| I-04-02 | Button (pilihan, aktif) | "Aksara Jawa" | Memilih jenis aksara Jawa (terpilih awal), memuat ulang daftar dengan filter jenis = Jawa (Algo-049 → Algo-040). |
| I-04-03 | Button (pilihan) | "Aksara Sunda" | Memilih jenis aksara Sunda dan memuat ulang daftar (Algo-049 → Algo-040). |
| I-04-04 | Button (pilihan, aktif) | "Dasar" | Memfilter tingkat kesulitan 1 (Algo-049 → Algo-040). |
| I-04-05 | Button (pilihan) | "Menengah" | Memfilter tingkat kesulitan 2 (Algo-049 → Algo-040). |
| I-04-06 | Button (pilihan) | "Lanjut" | Memfilter tingkat kesulitan 3 (Algo-049 → Algo-040). |
| I-04-07 | Label (heading) | "Materi tersedia" | Judul bagian daftar hasil filter. |
| I-04-08 | Chip | "3 materi" (jumlah materi hasil filter) | Diperbarui setiap filter berubah. |
| I-04-09 | Card (item materi) | Nama materi, jenis aksara, dan tingkat | Satu kartu per materi `TERBIT`; data dari `ambilMetadata()` (Algo-049). |
| I-04-10 | Chip (status) | "Lulus" / "Belum tuntas" / "Belum dimulai" | Status progres pelajar per materi dari `RiwayatLatihan.ambilStatusProgres` (Algo-049). |
| I-04-11 | Button (utama) | "Pelajari" | Membuka I-05 Detail Materi untuk materi tersebut; menjalankan `pilihMateri` (Algo-050). |
| I-04-12 | Alert (galat) | "Materi belum tersedia untuk kombinasi ini..." | Tampil hanya bila daftar kosong (alternatif 1 UC-02); bukan galat sistem (Algo-040). |

## 6.5 Rancangan Antarmuka I-05

**Antarmuka:** I-05  
**Nama Layar:** Detail Materi

<p align="center">
<img alt="Rancangan Antarmuka I-05 Detail Materi" src="./assets/diagram/I-05 Detail Materi.png" width="70%">
</p>
<p align="center">
<i>Gambar 6.5. Rancangan Antarmuka I-05 - Detail Materi</i>
</p>

Tabel 6.5. Spesifikasi Objek Antarmuka I-05

| Id_Objek | Jenis | Konten | Keterangan |
| :--- | :--- | :--- | :--- |
| I-05-01 | Card (header halaman) | Nama materi, jenis aksara, tingkat, breadcrumb | Data dari `PaketMateri` (Algo-047, Algo-050). |
| I-05-02 | Chip (status) | "Belum tuntas" / "Lulus" | Status progres pelajar untuk materi ini (Algo-050). |
| I-05-03 | Card (tile aksara) | Bentuk aksara materi (mis. ꦲ ꦤ ꦕ ꦫ ꦏ) | Diisi dari `bentukAksara` (Algo-047). |
| I-05-04 | Card (teks) | Aturan penulisan | Diisi dari `aturan` (Algo-047). |
| I-05-05 | Card (teks) | Contoh penggunaan | Diisi dari `contoh` (Algo-047). |
| I-05-06 | Card (audio) | "Contoh bunyi pengucapan" | Tampil bersama tombol audio. |
| I-05-07 | Button (aksen) | "Putar Pengucapan" | Memutar audio pengucapan (Algo-048 → Algo-051); nonaktif bila audio tidak tersedia (Algo-047). |
| I-05-08 | Alert (galat) | "Audio tidak dapat diputar." | Tampil bila audio gagal dimuat (alternatif 2 UC-02), konten lain tetap terbaca (Algo-048). |
| I-05-09 | Card | "Siap berlatih?" | Ajakan berlatih. |
| I-05-10 | Button (utama) | "Mulai Latihan" | Membuka I-06 Pilih Mode untuk materi ini (UC-03). |
| I-05-11 | Button (sekunder) | "Kembali ke Daftar Materi" | Kembali ke I-04. |
| I-05-12 | Alert (info) | "Konten materi versi 1.0, sesuai versi aplikasi." | Menunjukkan `versiKonten` yang ditampilkan (Algo-056). |

## 6.6 Rancangan Antarmuka I-06

## 6.7 Rancangan Antarmuka I-07

## 6.8 Rancangan Antarmuka I-08

## 6.9 Rancangan Antarmuka I-09

## 6.10 Rancangan Antarmuka I-10

## 6.11 Rancangan Antarmuka I-11

## 6.12 Rancangan Antarmuka I-12

## 6.13 Rancangan Antarmuka I-13

## 6.14 Rancangan Antarmuka I-14

## 6.15 Rancangan Antarmuka I-15

## 6.16 Rancangan Antarmuka I-16

## 6.17 Rancangan Antarmuka I-17

## 6.18 Rancangan Antarmuka I-18

**Antarmuka:** I-18  
**Nama Layar:** Kelola Materi

<p align="center">
<img alt="Rancangan Antarmuka I-18 Kelola Materi" src="./assets/diagram/I-18 Kelola Materi.png" width="70%">
</p>
<p align="center">
<i>Gambar 6.18. Rancangan Antarmuka I-18 - Kelola Materi</i>
</p>

Tabel 6.18. Spesifikasi Objek Antarmuka I-18

| Id_Objek | Jenis | Konten | Keterangan |
| :--- | :--- | :--- | :--- |
| I-18-01 | Card (header halaman) | "Kelola Materi" dan deskripsi | Hanya untuk Tim Materi (`verifikasiHakAkses("kelola_materi")`). |
| I-18-02 | Button (aksen) | "Tambah Materi" | Membuka I-19 Unggah Materi. |
| I-18-03 | Alert (sukses) | "Materi berhasil ditambahkan." | Tampil setelah operasi sukses (`tampilkanStatus`, Algo-045/046). |
| I-18-04 | Button (filter) | "Semua" | Menampilkan semua materi, termasuk draf (Algo-049 → Algo-040). |
| I-18-05 | Button (filter) | "Aksara Jawa" | Memfilter jenis Jawa (Algo-049 → Algo-040). |
| I-18-06 | Button (filter) | "Aksara Sunda" | Memfilter jenis Sunda (Algo-049 → Algo-040). |
| I-18-07 | Table (dalam Card) | Nama materi, jenis aksara, tingkat, status, versi konten, aksi | Diisi oleh `tampilkanDaftarMateri` (Algo-049). |
| I-18-08 | Chip (status) | "Terbit" / "Draf" | Nilai `statusPublikasi`. |
| I-18-09 | Button (teks) | "Sunting" | Membuka form sunting dengan data materi terisi (Algo-046 → Algo-054). |
| I-18-10 | Button (teks, bahaya) | "Hapus" | Meminta konfirmasi, lalu menghapus lunak materi (Algo-055). |

## 6.19 Rancangan Antarmuka I-19

**Antarmuka:** I-19  
**Nama Layar:** Unggah Materi

<p align="center">
<img alt="Rancangan Antarmuka I-19 Unggah Materi" src="./assets/diagram/I-19 Unggah Materi.png" width="70%">
</p>
<p align="center">
<i>Gambar 6.19. Rancangan Antarmuka I-19 - Unggah Materi</i>
</p>

Tabel 6.19. Spesifikasi Objek Antarmuka I-19

| Id_Objek | Jenis | Konten | Keterangan |
| :--- | :--- | :--- | :--- |
| I-19-01 | Card (header halaman) | "Unggah Materi" dan breadcrumb | Kembali ke I-18 lewat breadcrumb. |
| I-19-02 | Text Input | Label "Nama materi" | Wajib, maks. 100 karakter; divalidasi di Algo-043. |
| I-19-03 | Card (pilihan) | "Aksara Jawa" | Memilih jenis aksara `JAWA`. |
| I-19-04 | Card (pilihan) | "Aksara Sunda" | Memilih jenis aksara `SUNDA`. |
| I-19-05 | Button (pilihan) | "Dasar" | Tingkat kesulitan 1. |
| I-19-06 | Button (pilihan) | "Menengah" | Tingkat kesulitan 2. |
| I-19-07 | Button (pilihan) | "Lanjut" | Tingkat kesulitan 3. |
| I-19-08 | Toggle | "Terbitkan setelah disimpan" | Mati = status `DRAF`, hidup = `TERBIT` (Algo-043). |
| I-19-09 | Button (sekunder) | "Batal" | Membuang isian dan kembali ke I-18. |
| I-19-10 | Button (utama) | "Simpan Materi" | Menjalankan `unggahFileMateri` (Algo-045 → Algo-053). |
| I-19-11 | Card | "Berkas materi" | Pembungkus bagian unggah berkas. |
| I-19-12 | Dropzone | "Seret berkas paket materi ke sini" | Menerima berkas yang dijatuhkan. |
| I-19-13 | Button (sekunder) | "Pilih Berkas" | Membuka pemilih berkas peramban. |
| I-19-14 | List item | "aksara-nglegena-dasar.zip", "2,4 MB · berisi konten.json dan folder audio" | Berkas terpilih. |
| I-19-15 | Chip (lulus) | "Format valid" | Hasil pemeriksaan awal (ekstensi) di tampilan (Algo-045). |
| I-19-16 | Alert (galat) | "Jenis berkas tidak didukung." | Tampil bila berkas bukan .zip (alternatif 1 UC-06; Algo-045/052). |
| I-19-17 | Alert (info) | "Berkas diperiksa di server." (format, ukuran maks. 20 MB, isi paket) | Pemeriksaan lengkap oleh server (Algo-052). |

## 6.20 Rancangan Antarmuka I-20

## 6.X Rancangan Antarmuka [ID Layar X]

*[Ulangi pola 6.1 untuk layar berikutnya sampai semua layar teridentifikasi]*

## 6.Y Rancangan Antarmuka Keseluruhan

Tuliskan daftar gambar seluruh antarmuka. Berikan ID unik untuk setiap tampilan dan gunakan ID yang sama pada subbab perancangan antarmuka masing-masing.

Tabel 6.Y. Rancangan Antarmuka Keseluruhan

| No | Tampilan Antarmuka |
| :--- | :--- |
| I-01 | <img src="./assets/diagram/contoh-design.png" alt="Contoh Desain" width="400"> |
| I-02 | ... |
| I-03 | ... |
| I-04 | <img src="./assets/diagram/I-04 Daftar Materi.png" alt="Contoh Desain" width="400"> |
| I-05 | <img src="./assets/diagram/I-05 Detail Materi.png" alt="Contoh Desain" width="400"> |
| I-06 | ... |
| I-07 | ... |
| I-08 | ... |
| I-09 | ... |
| I-10 | ... |
| I-11 | ... |
| I-12 | ... |
| I-13 | ... |
| I-14 | ... |
| I-15 | ... |
| I-16 | ... |
| I-17 | ... |
| I-18 | <img src="./assets/diagram/I-18 Kelola Materi.png" alt="Contoh Desain" width="400"> |
| I-19 | <img src="./assets/diagram/I-19 Unggah Materi.png" alt="Contoh Desain" width="400"> |
| I-20 | ... |

<br>

---

# BAB 7: Matriks Kerunutan

Petakan kelas perancangan dengan use case yang terkait. Gunakan **BAB 6 Traceability pada dokumen SKPL** sebagai acuan keterkaitan kelas analisis dan use case, lalu sesuaikan dengan realisasi use case dan kelas perancangan pada BAB 3–BAB 5 DPPL.

Tabel 7.1. Matriks Kerunutan Kelas terhadap Use Case

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
