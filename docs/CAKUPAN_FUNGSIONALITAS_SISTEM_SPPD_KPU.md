# CAKUPAN FUNGSIONALITAS SISTEM SPPD KPU KABUPATEN CIREBON

## BAB I. PENDAHULUAN

Sistem SPPD (Surat Perintah Perjalanan Dinas) KPU Kabupaten Cirebon merupakan sistem informasi yang dirancang untuk mengelola seluruh proses pengajuan, approval, dan administrasi SPPD secara digital. Sistem ini menggantikan proses manual yang sebelumnya menggunakan kertas dan memerlukan waktu yang lama untuk mendapatkan persetujuan. Dengan implementasi sistem digital ini, seluruh proses SPPD dapat dilakukan secara efisien, transparan, dan dapat dilacak dengan mudah.

Sistem SPPD KPU dibangun menggunakan teknologi modern dengan framework Laravel 11 sebagai backend, Blade template engine dengan Alpine.js untuk frontend, dan database MySQL/PostgreSQL untuk penyimpanan data. Sistem ini mengadopsi arsitektur web-based yang memungkinkan akses dari berbagai perangkat dengan antarmuka yang responsif dan user-friendly.

## BAB II. CAKUPAN FUNGSIONALITAS SISTEM

### 2.1 Manajemen SPPD (Surat Perintah Perjalanan Dinas)

Sistem SPPD KPU menyediakan fungsionalitas lengkap untuk mengelola seluruh siklus hidup SPPD, mulai dari pembuatan hingga penyelesaian. Fungsionalitas pembuatan SPPD memungkinkan pengguna dengan role KASUBBAG untuk membuat pengajuan SPPD baru dengan mengisi informasi lengkap seperti tujuan perjalanan, keperluan, tanggal berangkat dan kembali, transportasi yang digunakan, serta perhitungan anggaran yang diperlukan.

Proses pembuatan SPPD dimulai dengan pengisian informasi dasar yang mencakup tempat berangkat, tujuan, keperluan perjalanan, dan periode waktu perjalanan. Sistem kemudian memungkinkan pemilihan peserta perjalanan dari daftar staff yang tersedia, dengan validasi otomatis untuk memastikan hanya staff yang dapat dipilih sebagai peserta. Perhitungan anggaran dilakukan secara otomatis berdasarkan komponen biaya transport, penginapan, uang harian, dan biaya lainnya yang diinput oleh pengusul.

Fitur edit SPPD tersedia untuk pengusul sebelum SPPD disubmit untuk approval, memungkinkan perubahan pada detail perjalanan, peserta, atau anggaran jika diperlukan. Setelah SPPD disubmit, sistem akan mengunci editing dan memulai proses approval yang melibatkan multiple level approver sesuai dengan hierarki organisasi.

### 2.2 Sistem Approval Multi-Level

Sistem approval SPPD KPU mengimplementasikan workflow multi-level yang terdiri dari dua tahap approval. Tahap pertama melibatkan Sekretaris KPU sebagai level 1 approver yang bertanggung jawab untuk memeriksa kelengkapan dokumen, validasi anggaran, dan kesesuaian dengan kebijakan organisasi. Tahap kedua melibatkan PPK (Pejabat Pembuat Komitmen) sebagai level 2 approver yang memberikan persetujuan final.

Setiap level approval memiliki hak untuk approve, reject, atau request revision berdasarkan pertimbangan masing-masing. Jika SPPD disetujui di level 1, sistem akan otomatis melanjutkan ke level 2 untuk approval final. Jika terjadi rejection, proses akan berakhir dan pengusul akan diberi notifikasi beserta alasan penolakan. Jika terjadi request revision, SPPD akan dikembalikan ke pengusul untuk perbaikan sebelum dapat disubmit ulang.

Sistem tracking approval menyediakan informasi real-time tentang status SPPD, current approval level, dan progress approval yang memungkinkan semua pihak terkait untuk memantau perkembangan pengajuan SPPD. Riwayat approval disimpan secara lengkap termasuk timestamp, approver, dan komentar yang diberikan.

### 2.3 Manajemen Role dan Hak Akses

Sistem SPPD KPU mengimplementasikan role-based access control (RBAC) yang membagi pengguna menjadi lima kategori role utama dengan hak akses yang berbeda-beda. Role ADMIN memiliki akses penuh ke seluruh sistem untuk keperluan administrasi dan monitoring, namun tidak dapat mengajukan SPPD atau melakukan approval. Role KASUBBAG merupakan pengusul utama yang memiliki hak eksklusif untuk membuat dan mengajukan SPPD, serta dapat memilih peserta dari daftar staff yang tersedia.

Role SEKRETARIS dan PPK berfungsi sebagai approver dengan level yang berbeda. SEKRETARIS sebagai level 1 approver dapat melakukan approval, rejection, atau request revision untuk SPPD yang diajukan. PPK sebagai level 2 approver memberikan persetujuan final dan memiliki hak yang sama dengan SEKRETARIS. Role STAFF berfungsi sebagai peserta SPPD yang dapat melihat detail SPPD dimana mereka terlibat dan mengunduh dokumen terkait.

Setiap role memiliki menu dan fitur yang berbeda sesuai dengan tanggung jawab dan hak akses masing-masing. Sistem mengimplementasikan middleware untuk memvalidasi hak akses pada setiap request, memastikan keamanan dan integritas data. Policy-based permissions diterapkan untuk mengontrol aksi yang dapat dilakukan oleh setiap role terhadap data SPPD.

### 2.4 Sistem Notifikasi Multi-Channel

Sistem notifikasi SPPD KPU mengimplementasikan pendekatan multi-channel untuk memastikan informasi penting dapat diterima oleh semua pihak terkait secara tepat waktu. Notifikasi dikirim melalui empat channel utama: WhatsApp untuk notifikasi real-time, email untuk informasi detail, dashboard untuk notifikasi in-app, dan SMS sebagai backup notification.

Tipe notifikasi yang disediakan mencakup notifikasi SPPD submitted yang dikirim ke Sekretaris saat pengajuan baru, notifikasi SPPD approved untuk setiap level approval, notifikasi SPPD completed yang dikirim ke pengusul dan semua peserta saat approval final, serta notifikasi rejection dan revision yang memberikan informasi lengkap tentang keputusan approver.

Sistem notifikasi terintegrasi dengan workflow approval, sehingga setiap perubahan status SPPD akan otomatis memicu pengiriman notifikasi yang sesuai. Notifikasi WhatsApp menggunakan API WhatsApp Business untuk pengiriman pesan otomatis, sementara email menggunakan SMTP server untuk pengiriman email terstruktur dengan template yang profesional.

### 2.5 Manajemen Dokumen Digital

Sistem manajemen dokumen SPPD KPU menyediakan infrastruktur lengkap untuk penyimpanan, pengelolaan, dan distribusi dokumen digital. Jenis dokumen yang dikelola meliputi SPPD PDF yang digenerate otomatis setelah approval final, dokumen pendukung yang diupload oleh pengusul, surat persetujuan yang dibuat untuk setiap level approval, dan template dokumen yang dapat dikelola oleh administrator.

Proses upload dokumen mendukung berbagai format file termasuk PDF, DOCX, XLSX, JPG, dan PNG dengan validasi ukuran file maksimal 10MB per file. Sistem menyimpan metadata lengkap untuk setiap dokumen termasuk informasi uploader, timestamp, file size, dan mime type. Access control diterapkan berdasarkan role pengguna, memastikan hanya pihak yang berwenang yang dapat mengakses dokumen tertentu.

Generasi dokumen otomatis terjadi saat approval final, dimana sistem akan menghasilkan kode SPPD dengan format SPD/YYYY/MM/001, nomor surat tugas dengan format ST/YYYY/001, dan tanggal surat tugas sesuai tanggal approval. Dokumen PDF yang dihasilkan mengandung semua informasi SPPD termasuk kode dan nomor yang telah digenerate, serta dapat diunduh oleh pengusul, peserta, dan approver.

### 2.6 Sistem Pelaporan dan Analytics

Sistem pelaporan SPPD KPU menyediakan berbagai jenis laporan yang dapat digunakan untuk monitoring, evaluasi, dan pengambilan keputusan. Laporan SPPD mencakup analisis berdasarkan status (completed, rejected, in_review), periode waktu, approver, dan range anggaran. Laporan approval menyediakan statistik approval, analisis waktu approval, alasan rejection, dan pola revision.

Laporan anggaran menyediakan analisis total anggaran per periode, anggaran berdasarkan destinasi, jenis transportasi, dan tren anggaran dari waktu ke waktu. Laporan user menyediakan informasi aktivitas pengguna, jumlah SPPD yang dibuat per user, performa approval, dan statistik partisipasi staff.

Dashboard analytics menyediakan visualisasi data real-time dengan grafik interaktif yang memungkinkan analisis mendalam terhadap data SPPD. Fitur export data tersedia dalam berbagai format termasuk Excel, PDF, dan CSV untuk keperluan backup dan analisis lanjutan. Filter options yang fleksibel memungkinkan pengguna untuk menyesuaikan laporan sesuai kebutuhan spesifik.

### 2.7 Manajemen Pengguna dan Keamanan

Sistem manajemen pengguna SPPD KPU menyediakan fungsionalitas lengkap untuk administrasi pengguna dengan fitur create, edit, activate/deactivate user, dan assignment role. Setiap user memiliki profil yang dapat dikelola termasuk informasi pribadi, kontak, dan preferensi notifikasi. Sistem password management menyediakan fitur change password dengan validasi keamanan yang ketat.

Keamanan sistem diimplementasikan dalam tiga layer utama: authentication, authorization, dan data protection. Authentication menggunakan sistem login dengan email/password yang dilengkapi dengan session management dan CSRF protection. Authorization menggunakan role-based access control dan policy-based permissions untuk mengontrol akses ke fitur dan data.

Data protection mencakup password hashing menggunakan bcrypt, enkripsi data sensitif, audit logging untuk semua aktivitas penting, dan prosedur backup yang terstruktur. Sistem juga mengimplementasikan rate limiting untuk mencegah abuse dan brute force attack.

### 2.8 Dashboard dan Monitoring

Dashboard SPPD KPU menyediakan interface yang informatif dan user-friendly untuk monitoring real-time terhadap seluruh aktivitas sistem. Dashboard menampilkan statistik overview yang mencakup jumlah SPPD dalam berbagai status, pending approvals, dan recent activities. Setiap role memiliki dashboard yang disesuaikan dengan kebutuhan dan hak akses masing-masing.

Fitur monitoring sistem mencakup user activity monitoring yang melacak semua aktivitas pengguna, system performance monitoring yang memantau performa aplikasi, error logging yang mencatat semua error dan exception, serta health checks yang memastikan sistem berjalan dengan optimal.

Dashboard juga menyediakan quick access ke fitur-fitur penting seperti pending approvals, recent SPPD, dan system notifications. Real-time updates memastikan informasi yang ditampilkan selalu akurat dan up-to-date.

### 2.9 Integrasi dan Konectivitas

Sistem SPPD KPU mengintegrasikan berbagai layanan eksternal untuk memastikan fungsionalitas yang komprehensif. Integrasi WhatsApp API memungkinkan pengiriman notifikasi real-time melalui WhatsApp Business, sementara integrasi email service memastikan pengiriman email notifikasi yang reliable. Integrasi SMS service berfungsi sebagai backup notification channel.

Integrasi teknis mencakup database integration yang menggunakan MySQL/PostgreSQL untuk penyimpanan data, file storage system untuk penyimpanan dokumen, caching system untuk optimasi performa, dan queue system untuk menangani task yang memerlukan waktu lama seperti generasi PDF dan pengiriman notifikasi.

Sistem juga mendukung integrasi dengan layanan eksternal lainnya seperti cloud storage untuk backup dokumen, monitoring service untuk system health, dan analytics service untuk analisis data lanjutan.

### 2.10 Business Logic dan Compliance

Business logic SPPD KPU mengimplementasikan aturan bisnis yang spesifik untuk pengelolaan SPPD sesuai dengan regulasi dan kebijakan organisasi. Kode SPPD digenerate otomatis saat approval final dengan format yang standar, memastikan konsistensi dan traceability. Perhitungan anggaran dilakukan secara otomatis berdasarkan komponen biaya yang telah ditentukan.

Sistem compliance memastikan kepatuhan terhadap regulasi yang berlaku dengan mengimplementasikan audit trail yang mencatat semua aktivitas penting, data integrity checks yang memvalidasi konsistensi data, dan process validation yang memastikan workflow berjalan sesuai aturan yang ditetapkan.

Business rules yang diterapkan mencakup validasi peserta yang memastikan hanya staff yang dapat dipilih sebagai peserta, validasi anggaran yang memastikan perhitungan biaya sesuai standar, dan validasi approval sequence yang memastikan urutan approval sesuai hierarki organisasi.

## BAB III. KESIMPULAN

Sistem SPPD KPU Kabupaten Cirebon merupakan sistem informasi yang komprehensif dengan cakupan fungsionalitas yang lengkap untuk mengelola seluruh proses SPPD secara digital. Sistem ini mengintegrasikan berbagai aspek penting termasuk manajemen SPPD, sistem approval multi-level, role-based access control, sistem notifikasi multi-channel, manajemen dokumen digital, pelaporan dan analytics, manajemen pengguna dan keamanan, dashboard dan monitoring, integrasi dan konectivitas, serta business logic dan compliance.

Dengan implementasi sistem ini, proses SPPD yang sebelumnya manual dan memakan waktu lama dapat dilakukan secara efisien, transparan, dan dapat dilacak dengan mudah. Sistem ini tidak hanya menggantikan proses manual tetapi juga meningkatkan kualitas layanan, mengurangi kesalahan manusia, dan memberikan data yang akurat untuk pengambilan keputusan.

Keunggulan sistem ini terletak pada arsitektur yang modular, keamanan yang robust, dan user experience yang baik. Penggunaan teknologi modern seperti Laravel 11, responsive design, dan multi-channel notifications memastikan sistem dapat diakses dari berbagai perangkat dan memberikan pengalaman pengguna yang optimal.

Implementasi sistem SPPD KPU ini merupakan langkah penting dalam transformasi digital organisasi, yang tidak hanya meningkatkan efisiensi operasional tetapi juga mendukung transparansi dan akuntabilitas dalam pengelolaan SPPD.

---

**Dokumen ini dibuat untuk keperluan skripsi pada Juli 2025**
**Status: DOKUMEN LENGKAP DAN SIAP UNTUK SKRIPSI** ✅ 