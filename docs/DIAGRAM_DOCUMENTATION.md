# Dokumentasi Diagram Sistem SPPD KPU

## DFD Level 3 Sederhana (Sesuai Standar)

### Deskripsi
DFD Level 3 yang telah disederhanakan namun tetap sesuai standar DFD Level 3 dengan proses yang detail dan granular. Diagram ini menggambarkan alur data dengan level detail yang tepat untuk Level 3.

### Karakteristik DFD Level 3:
- **Proses yang sangat detail** dengan 5 sub-proses per kelompok
- **Penomoran yang sistematis** (1.1.1, 1.1.2, dst)
- **Alur data yang granular** antar sub-proses
- **Data store yang spesifik** untuk setiap jenis data
- **External entities yang jelas** dengan peran yang terdefinisi

### Komponen Utama

#### 1. External Entities (Entitas Eksternal)
- **Kasubbag Staff**: Staff yang membuat dan mengelola SPPD
- **Sekretaris**: Pihak yang menyetujui SPPD (Level 1)
- **PPK**: Pejabat Pembuat Komitmen yang menyetujui SPPD (Level 2)
- **Admin**: Administrator sistem

#### 2. Data Stores (Penyimpanan Data)
- **Users Database**: Database pengguna sistem
- **SPPD Database**: Database data SPPD
- **Approvals Database**: Database persetujuan
- **Documents Database**: Database dokumen
- **Notifications Database**: Database notifikasi
- **Templates Database**: Database template dokumen
- **Settings Database**: Database pengaturan sistem

#### 3. Process Groups (Kelompok Proses Level 3)

##### 1. SPPD Creation Process (1.1.x)
- **1.1.1 Input Basic SPPD Data**: Memasukkan data dasar SPPD
- **1.1.2 Validate Required Fields**: Memvalidasi field yang wajib diisi
- **1.1.3 Calculate Travel Budget**: Menghitung anggaran perjalanan
- **1.1.4 Generate SPPD Code**: Menghasilkan kode SPPD
- **1.1.5 Save Draft SPPD**: Menyimpan draft SPPD

##### 2. SPPD Submission Process (2.1.x)
- **2.1.1 Check SPPD Completeness**: Memeriksa kelengkapan SPPD
- **2.1.2 Validate Participant Data**: Memvalidasi data peserta
- **2.1.3 Set Submission Status**: Menetapkan status pengajuan
- **2.1.4 Create Approval Request**: Membuat permintaan persetujuan
- **2.1.5 Send Notification to Approver**: Mengirim notifikasi ke penyetuju

##### 3. Approval Level 1 Process (3.1.x)
- **3.1.1 Check Approval Authority**: Memeriksa otoritas persetujuan
- **3.1.2 Review SPPD Content**: Meninjau isi SPPD
- **3.1.3 Validate Budget Calculation**: Memvalidasi perhitungan anggaran
- **3.1.4 Approve/Reject Level 1**: Menyetujui/menolak Level 1
- **3.1.5 Update Approval Status**: Memperbarui status persetujuan

##### 4. Approval Level 2 Process (4.1.x)
- **4.1.1 Check Final Authority**: Memeriksa otoritas final
- **4.1.2 Review Previous Approval**: Meninjau persetujuan sebelumnya
- **4.1.3 Validate Final Budget**: Memvalidasi anggaran final
- **4.1.4 Approve/Reject Level 2**: Menyetujui/menolak Level 2
- **4.1.5 Finalize Approval Decision**: Memfinalisasi keputusan persetujuan

##### 5. Document Generation Process (5.1.x)
- **5.1.1 Load Document Template**: Memuat template dokumen
- **5.1.2 Fill SPPD Data**: Mengisi data SPPD
- **5.1.3 Generate PDF Document**: Menghasilkan dokumen PDF
- **5.1.4 Save Generated Document**: Menyimpan dokumen yang dihasilkan
- **5.1.5 Send Document to User**: Mengirim dokumen ke pengguna

##### 6. Document Upload Process (6.1.x)
- **6.1.1 Select File for Upload**: Memilih file untuk diunggah
- **6.1.2 Validate File Format**: Memvalidasi format file
- **6.1.3 Check File Size**: Memeriksa ukuran file
- **6.1.4 Process File Upload**: Memproses pengunggahan file
- **6.1.5 Link to SPPD Record**: Menghubungkan ke record SPPD

##### 7. Document Storage Process (7.1.x)
- **7.1.1 Store File in Storage**: Menyimpan file di storage
- **7.1.2 Create File Metadata**: Membuat metadata file
- **7.1.3 Update Document List**: Memperbarui daftar dokumen
- **7.1.4 Set Access Permissions**: Menetapkan izin akses
- **7.1.5 Log Document Activity**: Mencatat aktivitas dokumen

##### 8. User Management Process (8.1.x)
- **8.1.1 Input User Information**: Memasukkan informasi pengguna
- **8.1.2 Validate Email Address**: Memvalidasi alamat email
- **8.1.3 Assign User Role**: Menetapkan peran pengguna
- **8.1.4 Set Initial Password**: Menetapkan password awal
- **8.1.5 Create User Account**: Membuat akun pengguna

##### 9. Notification Creation Process (9.1.x)
- **9.1.1 Create Notification Message**: Membuat pesan notifikasi
- **9.1.2 Set Notification Type**: Menetapkan tipe notifikasi
- **9.1.3 Assign Recipients**: Menetapkan penerima
- **9.1.4 Set Priority Level**: Menetapkan level prioritas
- **9.1.5 Save Notification**: Menyimpan notifikasi

##### 10. Notification Delivery Process (10.1.x)
- **10.1.1 Query User Notifications**: Mengquery notifikasi pengguna
- **10.1.2 Filter by User Role**: Memfilter berdasarkan peran pengguna
- **10.1.3 Update Read Status**: Memperbarui status baca
- **10.1.4 Display in Interface**: Menampilkan di antarmuka
- **10.1.5 Send Email/WhatsApp**: Mengirim email/WhatsApp

##### 11. Analytics Generation Process (11.1.x)
- **11.1.1 Query SPPD Statistics**: Mengquery statistik SPPD
- **11.1.2 Calculate Budget Totals**: Menghitung total anggaran
- **11.1.3 Generate Chart Data**: Menghasilkan data grafik
- **11.1.4 Format Report Data**: Memformat data laporan
- **11.1.5 Create Analytics Dashboard**: Membuat dashboard analitik

##### 12. Report Export Process (12.1.x)
- **12.1.1 Load Report Template**: Memuat template laporan
- **12.1.2 Fill Report Data**: Mengisi data laporan
- **12.1.3 Generate PDF Report**: Menghasilkan laporan PDF
- **12.1.4 Save Report File**: Menyimpan file laporan
- **12.1.5 Provide Download Link**: Menyediakan link download

### Alur Data Utama Level 3
1. **SPPD Creation Flow**: Input → Validate → Calculate → Generate → Save
2. **SPPD Submission Flow**: Check → Validate → Set → Create → Notify
3. **Approval Level 1 Flow**: Check → Review → Validate → Decide → Update
4. **Approval Level 2 Flow**: Check → Review → Validate → Decide → Finalize
5. **Document Generation Flow**: Load → Fill → Generate → Save → Send
6. **Document Upload Flow**: Select → Validate → Check → Process → Link
7. **Document Storage Flow**: Store → Create → Update → Set → Log
8. **User Management Flow**: Input → Validate → Assign → Set → Create
9. **Notification Creation Flow**: Create → Set → Assign → Set → Save
10. **Notification Delivery Flow**: Query → Filter → Update → Display → Send
11. **Analytics Generation Flow**: Query → Calculate → Generate → Format → Create
12. **Report Export Flow**: Load → Fill → Generate → Save → Provide

---

## Use Case Diagram

### Deskripsi
Use case diagram menggambarkan interaksi antara aktor (pengguna) dengan sistem SPPD KPU. Diagram ini menunjukkan semua fitur yang tersedia untuk setiap jenis pengguna.

### Aktor (Actors)
- **Kasubbag Staff**: Staff yang membuat dan mengelola SPPD
- **Sekretaris**: Pihak yang menyetujui SPPD
- **PPK**: Pejabat Pembuat Komitmen
- **Admin**: Administrator sistem

### Use Cases (Kasus Penggunaan)

#### 1. SPPD Management
- **Create SPPD**: Membuat SPPD baru
- **Edit SPPD**: Mengedit SPPD yang ada
- **Submit SPPD**: Mengirim SPPD untuk persetujuan
- **View SPPD List**: Melihat daftar SPPD
- **View SPPD Detail**: Melihat detail SPPD

#### 2. Approval Process
- **Approve SPPD**: Menyetujui SPPD
- **Reject SPPD**: Menolak SPPD
- **View Approval Queue**: Melihat antrian persetujuan
- **Generate Document**: Menghasilkan dokumen SPPD

#### 3. Document Management
- **Upload Documents**: Mengunggah dokumen
- **Download Documents**: Mengunduh dokumen
- **View Document List**: Melihat daftar dokumen

#### 4. User Management
- **Create User**: Membuat pengguna baru
- **Edit User**: Mengedit data pengguna
- **View User List**: Melihat daftar pengguna
- **Assign Roles**: Menetapkan peran pengguna

#### 5. Notification System
- **Send Notifications**: Mengirim notifikasi
- **View Notifications**: Melihat notifikasi
- **Mark as Read**: Menandai notifikasi sebagai telah dibaca

#### 6. Analytics & Reports
- **View Dashboard**: Melihat dashboard
- **Generate Reports**: Menghasilkan laporan
- **Export PDF**: Mengekspor laporan PDF

### Relasi Antar Use Case
- **Include Relationship**: Use case yang harus dilakukan sebelum use case lain
- **Extend Relationship**: Use case opsional yang dapat dilakukan

### Hak Akses Aktor
- **Kasubbag Staff**: Akses penuh ke SPPD Management, Document Management, dan Notification System
- **Sekretaris**: Akses ke Approval Process dan Notification System
- **PPK**: Akses ke Approval Process dan Notification System
- **Admin**: Akses penuh ke semua fitur sistem

---

## File Diagram

### DFD Level 3
- **dfdlevel3_simple.mmd**: File source DFD Level 3 sederhana
- **dfdlevel3_simple.svg**: File gambar DFD Level 3 sederhana (139KB)

### Use Case Diagram

**Versi A4 Vertikal (Direkomendasikan untuk A4 Portrait):**
- **usecase_diagram_a4_vertical.mmd**: File source use case diagram A4 vertikal
- **usecase_diagram_a4_vertical.svg**: File gambar use case diagram A4 vertikal (49KB)

**Versi Vertikal (Alternatif):**
- **usecase_diagram_vertical.mmd**: File source use case diagram vertikal
- **usecase_diagram_vertical.svg**: File gambar use case diagram vertikal (51KB)

**Versi Standar (Format Restaurant):**
- **usecase_diagram_standard.mmd**: File source use case diagram standar
- **usecase_diagram_standard.svg**: File gambar use case diagram standar (53KB)

**Versi Sederhana (Alternatif untuk A4):**
- **usecase_diagram_simple.mmd**: File source use case diagram sederhana
- **usecase_diagram_simple.svg**: File gambar use case diagram sederhana (54KB)

**Versi Lengkap (Untuk referensi):**
- **usecase_diagram.mmd**: File source use case diagram lengkap
- **usecase_diagram.svg**: File gambar use case diagram lengkap (75KB)

## Penggunaan untuk Skripsi

### DFD Level 3
- ✅ **Sesuai standar DFD Level 3** dengan detail yang tepat
- ✅ **60 sub-proses** yang menggambarkan alur detail
- ✅ **Ukuran optimal** untuk skripsi (139KB)

### Use Case Diagram

**Versi A4 Vertikal (Direkomendasikan untuk A4 Portrait):**
- ✅ **22 use case** dengan layout vertikal yang benar
- ✅ **Aktor di atas** tersusun horizontal compact
- ✅ **Use case di bawah** tersusun vertikal dari atas ke bawah
- ✅ **Layout benar-benar vertikal** tanpa melebar ke samping
- ✅ **Ukuran yang paling efisien** (49KB)
- ✅ **Muat sempurna di A4 portrait**

**Versi Vertikal (Alternatif):**
- ✅ **22 use case** dengan layout vertikal
- ✅ **Aktor di kiri** dan use case di kanan
- ✅ **Tersusun vertikal** dari atas ke bawah
- ✅ **Ukuran yang efisien** (51KB)

**Versi Standar (Format Restaurant):**
- ✅ **22 use case** dengan format standar UML
- ✅ **System boundary** yang jelas seperti gambar restaurant
- ✅ **Ukuran yang muat di A4** (53KB)
- ✅ **Layout yang profesional** dan mudah dibaca
- ✅ **Format yang sesuai standar** UML use case diagram

**Versi Sederhana (Alternatif untuk A4):**
- ✅ **22 use case** yang lebih compact
- ✅ **Ukuran yang muat di A4** (54KB)
- ✅ **Elemen yang mudah dibaca**

**Versi Lengkap (Untuk referensi):**
- ✅ **35 use case** yang lebih detail
- ✅ **Ukuran lebih besar** (75KB)
- ✅ **Informasi yang lebih lengkap**

### Rekomendasi Penggunaan:
1. **Untuk skripsi**: Gunakan `usecase_diagram_a4_vertical.svg` dengan layout vertikal yang benar untuk A4 portrait
2. **Untuk alternatif**: `usecase_diagram_vertical.svg` untuk versi vertikal lainnya
3. **Untuk referensi**: `usecase_diagram.svg` untuk detail lengkap
4. **Untuk DFD**: `dfdlevel3_simple.svg` yang sesuai standar Level 3

### Keunggulan Versi A4 Vertikal:
- 📄 **Layout benar-benar vertikal** tanpa melebar ke samping
- 👆 **Aktor di atas** tersusun horizontal compact
- 📋 **Use case di bawah** tersusun vertikal dari atas ke bawah
- 📏 **Muat sempurna di A4 portrait**
- 🎯 **Ukuran paling efisien** (49KB) untuk skripsi
- 👀 **Mudah dibaca** dari atas ke bawah 