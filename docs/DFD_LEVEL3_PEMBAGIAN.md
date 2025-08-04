# PEMBAGIAN DFD LEVEL 3 SPPD KPU

## 📊 **Pembagian DFD Level 3**

DFD Level 3 telah dibagi menjadi **2 bagian** untuk memudahkan pembacaan dan pemahaman karena kompleksitas informasi yang tinggi.

### **🔄 DFD Level 3 - Part 1: SPPD Management & Approval Process**

**File:** `dfdlevel3_part1.png` (73KB)

#### **📋 Cakupan Proses:**

**1. SPPD Management (1.x.x):**
- **1.1.1** Input Basic Data
- **1.1.2** Input Travel Details  
- **1.1.3** Calculate Budget
- **1.1.4** Select Participants
- **1.1.5** Validate Form
- **1.2.1** Load SPPD Data
- **1.2.2** Edit Basic Info
- **1.2.3** Update Travel Info
- **1.2.4** Recalculate Budget
- **1.2.5** Save Changes
- **1.3.1** Validate Submission
- **1.3.2** Set Status Pending
- **1.3.3** Send to Approver
- **1.3.4** Create Notification
- **1.3.5** Update Queue

**2. Approval Process (2.x.x):**
- **2.1.1** Load SPPD Details (Sekretaris)
- **2.1.2** Review Documents
- **2.1.3** Check Budget
- **2.1.4** Make Decision
- **2.1.5** Record Approval
- **2.2.1** Load SPPD Details (PPK)
- **2.2.2** Review Previous Approval
- **2.2.3** Final Check
- **2.2.4** Make Final Decision
- **2.2.5** Generate SPPD Code

#### **🗄️ Data Stores:**
- **Database SPPD** (DB1)
- **Database Approvals** (DB4)

#### **👥 External Entities:**
- **Kasubbag** (pengaju)
- **Sekretaris** (approver level 1)
- **PPK** (approver level 2)

---

### **📄 DFD Level 3 - Part 2: Document Management & Reporting**

**File:** `dfdlevel3_part2.png` (136KB)

#### **📋 Cakupan Proses:**

**1. Document Management (4.x.x):**
- **4.1.1** Validate File
- **4.1.2** Store File
- **4.1.3** Update Database
- **4.1.4** Create Link
- **4.1.5** Send Notification
- **4.4.1** Load Template
- **4.4.2** Fill Data
- **4.4.3** Generate PDF
- **4.4.4** Add Watermark
- **4.4.5** Save Document

**2. User Management (3.x.x):**
- **3.1** Create User
- **3.2** Edit User
- **3.3** View Users
- **3.4** Assign Roles

**3. Reporting System (5.x.x):**
- **5.1** View Dashboard
- **5.2** Generate Reports
- **5.3** Export Data
- **5.4** View Analytics

#### **🗄️ Data Stores:**
- **Database SPPD** (DB1)
- **Database Users** (DB2)
- **Database Documents** (DB3)
- **Database Approvals** (DB4)
- **Database Templates** (DB5)

#### **👥 External Entities:**
- **Kasubbag** (upload/download dokumen)
- **Admin** (user management & reporting)
- **Staff** (download dokumen)

---

## 🎯 **Keunggulan Pembagian:**

### **✅ Manfaat:**
1. **Kemudahan Membaca:** Setiap bagian fokus pada domain tertentu
2. **Ukuran Optimal:** File PNG tidak terlalu besar
3. **Detail Lebih Jelas:** Text dan garis lebih mudah dibaca
4. **Pemahaman Bertahap:** Bisa dipelajari per bagian

### **📊 Perbandingan Ukuran:**

| File | Ukuran | Fokus |
|------|--------|-------|
| `dfdlevel3_simple.png` | 88KB | Semua proses (terlalu padat) |
| `dfdlevel3_part1.png` | 73KB | SPPD & Approval |
| `dfdlevel3_part2.png` | 136KB | Document & Reporting |

### **🔗 Hubungan Antar Bagian:**

**Part 1 → Part 2:**
- SPPD yang sudah disetujui → Generate Document
- Approval data → Reporting system
- User data → User management

**Part 2 → Part 1:**
- Document templates → PDF generation
- User roles → Approval permissions
- Dashboard data → SPPD status

---

## 📝 **Rekomendasi Penggunaan:**

### **Untuk Skripsi:**
1. **Gunakan Part 1** untuk menjelaskan alur SPPD dan approval
2. **Gunakan Part 2** untuk menjelaskan manajemen dokumen dan laporan
3. **Kombinasikan keduanya** untuk analisis sistem secara menyeluruh

### **Untuk Presentasi:**
1. **Part 1** untuk slide "Alur SPPD"
2. **Part 2** untuk slide "Manajemen Dokumen & Laporan"
3. **Kedua bagian** untuk slide "Arsitektur Sistem Lengkap" 