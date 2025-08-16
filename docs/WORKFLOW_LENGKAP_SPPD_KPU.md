# WORKFLOW LENGKAP SISTEM SPPD KPU KABUPATEN CIREBON

## 📋 **DAFTAR ISI**
1. [Role dan Hak Akses](#role-dan-hak-akses)
2. [Alur Workflow Lengkap](#alur-workflow-lengkap)
3. [Detail Setiap Role](#detail-setiap-role)
4. [Proses Approval](#proses-approval)
5. [Status SPPD](#status-sppd)
6. [Notifikasi Sistem](#notifikasi-sistem)
7. [Dokumen dan File](#dokumen-dan-file)
8. [Laporan dan Analytics](#laporan-dan-analytics)

---

## 👥 **ROLE DAN HAK AKSES**

### **1. ADMIN**
**Deskripsi:** Administrator sistem dengan akses penuh
**Hak Akses:**
- ✅ Melihat semua SPPD
- ✅ Mengelola user (create, edit, delete, activate/deactivate)
- ✅ Akses ke semua fitur monitoring
- ✅ Export data dan laporan
- ✅ Akses analytics dan dashboard
- ❌ **TIDAK BISA** mengajukan SPPD
- ❌ **TIDAK BISA** approve/reject SPPD
- ❌ **TIDAK BISA** menjadi peserta SPPD

### **2. KASUBBAG**
**Deskripsi:** Kepala Subbagian yang dapat mengajukan SPPD
**Hak Akses:**
- ✅ **HANYA KASUBBAG** yang bisa mengajukan SPPD
- ✅ Memilih peserta SPPD dari user lain
- ✅ Edit SPPD sebelum submit
- ✅ Melihat SPPD yang diajukan
- ✅ Akses approval queue (untuk monitoring)
- ✅ Akses dashboard dan analytics
- ✅ Export laporan
- ❌ **TIDAK BISA** approve/reject SPPD
- ❌ **TIDAK BISA** menjadi peserta SPPD

### **3. SEKRETARIS**
**Deskripsi:** Sekretaris KPU yang menyetujui SPPD (Level 1)
**Hak Akses:**
- ✅ Approve/reject SPPD di level 1
- ✅ Request revision SPPD
- ✅ Melihat semua SPPD
- ✅ Akses approval queue
- ✅ Akses dashboard dan analytics
- ✅ Export laporan
- ✅ Mengelola template dokumen
- ❌ **TIDAK BISA** mengajukan SPPD
- ❌ **TIDAK BISA** menjadi peserta SPPD

### **4. PPK (Pejabat Pembuat Komitmen)**
**Deskripsi:** PPK yang menyetujui SPPD (Level 2 - Final)
**Hak Akses:**
- ✅ Approve/reject SPPD di level 2 (final)
- ✅ Request revision SPPD
- ✅ Melihat semua SPPD
- ✅ Akses approval queue
- ✅ Akses dashboard dan analytics
- ✅ Export laporan
- ❌ **TIDAK BISA** mengajukan SPPD
- ❌ **TIDAK BISA** menjadi peserta SPPD

### **5. STAFF**
**Deskripsi:** Staff yang dapat menjadi peserta SPPD
**Hak Akses:**
- ✅ Melihat SPPD dimana dia menjadi peserta
- ✅ Download dokumen SPPD yang diikuti
- ✅ Akses dashboard terbatas
- ✅ Update profil pribadi
- ❌ **TIDAK BISA** mengajukan SPPD
- ❌ **TIDAK BISA** approve/reject SPPD
- ❌ **TIDAK BISA** akses approval queue

---

## 🔄 **ALUR WORKFLOW LENGKAP**

### **DIAGRAM WORKFLOW:**
```
┌─────────────────────────────────────────────────────────────────┐
│                    SISTEM SPPD KPU                            │
└─────────────────────────────────────────────────────────────────┘

KASUBBAG
    │
    ├─ 1. CREATE SPPD
    │   ├─ Fill basic info (tujuan, keperluan, tanggal, dll)
    │   ├─ Select participants (staff only)
    │   ├─ Calculate budget
    │   ├─ Upload documents
    │   ├─ Save as DRAFT (kode_sppd = null, nomor_surat_tugas = null)
    │   └─ Status: 'draft'
    │
    ├─ 2. EDIT SPPD (if needed)
    │   ├─ Modify details
    │   ├─ Add/remove participants
    │   ├─ Update budget
    │   └─ Re-save as DRAFT
    │
    └─ 3. SUBMIT SPPD
        ├─ Set status: 'in_review'
        ├─ Set current_approval_level: 1
        ├─ Set submitted_at: now()
        ├─ **KODE SPPD BELUM DIGENERATE** (masih null)
        ├─ **NOMOR SURAT TUGAS BELUM DIGENERATE** (masih null)
        ├─ Send notification to SEKRETARIS
        └─ Lock editing (cannot edit anymore)
        │
        ▼
SEKRETARIS (Level 1 Approval)
    │
    ├─ 4. REVIEW SPPD
    │   ├─ Check completeness
    │   ├─ Validate budget
    │   ├─ Review participants
    │   └─ Check documents
    │
    ├─ 5. DECISION
    │   ├─ APPROVE → Continue to PPK
    │   ├─ REJECT → End workflow
    │   └─ REVISION → Back to KASUBBAG
    │
    └─ 6. APPROVE (if approved)
        ├─ Create approval record
        ├─ Set current_approval_level: 2
        ├─ Send notification to PPK
        └─ Status remains 'in_review'
        │
        ▼
PPK (Level 2 Approval - Final)
    │
    ├─ 7. FINAL REVIEW
    │   ├─ Review all details
    │   ├─ Check previous approval (SEKRETARIS)
    │   ├─ Validate final budget
    │   └─ Check compliance
    │
    ├─ 8. FINAL DECISION
    │   ├─ APPROVE → Complete workflow
    │   ├─ REJECT → End workflow
    │   └─ REVISION → Back to KASUBBAG
    │
    └─ 9. FINAL APPROVE (if approved)
        ├─ Create approval record
        ├─ **GENERATE KODE SPPD** (SPD/YYYY/MM/001)
        ├─ **GENERATE NOMOR SURAT TUGAS** (ST/YYYY/001)
        ├─ **SET TANGGAL SURAT TUGAS** (today's date)
        ├─ Set status: 'completed'
        ├─ Set current_approval_level: 0
        ├─ Set approved_at: now()
        ├─ Send notification to all participants
        └─ Complete workflow
        │
        ▼
COMPLETED SPPD
    │
    ├─ 10. DOCUMENT GENERATION
    │   ├─ Generate PDF SPPD (dengan kode SPPD yang sudah digenerate)
    │   ├─ Create approval letter
    │   └─ Store in document system
    │
    ├─ 11. NOTIFICATION
    │   ├─ Notify KASUBBAG (creator)
    │   ├─ Notify all participants
    │   └─ Send WhatsApp notification
    │
    └─ 12. ACCESS CONTROL
        ├─ Creator can download PDF
        ├─ Participants can download PDF
        ├─ Approvers can download PDF
        └─ Admin can access all documents
```

### **PENTING: KAPAN KODE SPPD DIGENERATE**

#### **❌ SALAH (dalam dokumentasi sebelumnya):**
- Kode SPPD digenerate saat submit
- Nomor surat tugas digenerate saat submit

#### **✅ BENAR (implementasi real):**
- **Kode SPPD digenerate saat PPK approve (final approval)**
- **Nomor surat tugas digenerate saat PPK approve (final approval)**
- **Tanggal surat tugas diset saat PPK approve (final approval)**

#### **Implementasi Real di `ApprovalService.php`:**
```php
if ($allApprovalsCompleted) {
    // All approvals completed, set status to completed
    // Generate kode SPD jika belum ada
    if (empty($travelRequest->kode_sppd)) {
        $kodeSppd = app(\App\Services\TravelRequestService::class)->generateKodeSppd();
        $travelRequest->kode_sppd = $kodeSppd;
    }
    // Generate nomor surat tugas dan tanggal surat tugas saat approve
    if (empty($travelRequest->nomor_surat_tugas)) {
        $nomorSuratTugas = app(\App\Services\TravelRequestService::class)->generateNomorSuratTugas();
        $travelRequest->nomor_surat_tugas = $nomorSuratTugas;
    }
    $travelRequest->update([
        'status' => 'completed',
        'current_approval_level' => 0,
        'approved_at' => now(),
        'updated_at' => now(),
        'kode_sppd' => $travelRequest->kode_sppd,
        'nomor_surat_tugas' => $travelRequest->nomor_surat_tugas,
        'tanggal_surat_tugas' => now()->format('Y-m-d'),
    ]);
}
```

#### **Implementasi Real di `ApprovalService.php`:**
```php
if ($allApprovalsCompleted) {
    // All approvals completed, set status to completed
    // Generate kode SPD jika belum ada
    if (empty($travelRequest->kode_sppd)) {
        $kodeSppd = app(\App\Services\TravelRequestService::class)->generateKodeSppd();
        $travelRequest->kode_sppd = $kodeSppd;
    }
    // Generate nomor surat tugas dan tanggal surat tugas saat approve
    if (empty($travelRequest->nomor_surat_tugas)) {
        $nomorSuratTugas = app(\App\Services\TravelRequestService::class)->generateNomorSuratTugas();
        $travelRequest->nomor_surat_tugas = $nomorSuratTugas;
    }
    $travelRequest->update([
        'status' => 'completed',
        'current_approval_level' => 0,
        'approved_at' => now(),
        'updated_at' => now(),
        'kode_sppd' => $travelRequest->kode_sppd,
        'nomor_surat_tugas' => $travelRequest->nomor_surat_tugas,
        'tanggal_surat_tugas' => now()->format('Y-m-d'),
    ]);
}
```

---

## 👤 **DETAIL SETIAP ROLE**

### **KASUBBAG - ROLE UTAMA PENGUSUL**

#### **A. Hak Khusus:**
- **HANYA KASUBBAG** yang dapat mengajukan SPPD
- Dapat memilih peserta dari daftar staff
- Dapat edit SPPD sebelum submit
- Dapat melihat progress approval

#### **B. Menu yang Dapat Diakses:**
```
Dashboard
├─ Overview SPPD
├─ Statistics
└─ Recent Activities

SPPD Management
├─ Create New SPPD
├─ My SPPD Requests
├─ All SPPD (view only)
└─ Export SPPD

Approval
├─ Approval Queue (view only)
└─ Approval History

Documents
├─ My Documents
├─ All Documents
└─ Document Templates

Reports
├─ Laporan
├─ Analytics
└─ Export Reports

Settings
├─ Profile
├─ Password
└─ System Settings
```

#### **C. Workflow KASUBBAG:**
1. **CREATE SPPD**
   - Akses: `/travel-requests/create`
   - Form fields: Basic info, participants, budget, documents
   - Action: Save as DRAFT
   - **Kode SPPD: null**
   - **Nomor Surat Tugas: null**

2. **EDIT SPPD**
   - Akses: `/travel-requests/{id}/edit`
   - Available: Only if status = 'draft' or 'revision'
   - Can modify: All fields except participants after submit

3. **SUBMIT SPPD**
   - Action: Submit for approval
   - System: Set status 'in_review', current_approval_level = 1
   - **Kode SPPD: masih null**
   - **Nomor Surat Tugas: masih null**
   - Notification: Sent to SEKRETARIS

4. **MONITOR PROGRESS**
   - View approval status
   - Track approval progress
   - Receive notifications

5. **REVISION HANDLING**
   - If revision requested: Edit and resubmit
   - If rejected: View rejection reason

### **SEKRETARIS - LEVEL 1 APPROVER**

#### **A. Hak Khusus:**
- Approve/reject SPPD di level 1
- Request revision jika ada masalah
- Melihat semua SPPD untuk approval

#### **B. Menu yang Dapat Diakses:**
```
Dashboard
├─ Pending Approvals
├─ Approval Statistics
└─ Recent Activities

Approval Management
├─ Approval Queue
├─ Approval History
├─ Bulk Approval
└─ Approval Settings

SPPD Management
├─ All SPPD (view only)
├─ SPPD by Status
└─ Export SPPD

Documents
├─ All Documents
├─ Document Templates
└─ Document Management

Reports
├─ Laporan
├─ Analytics
└─ Export Reports

Settings
├─ Profile
├─ Password
└─ System Settings
```

#### **C. Workflow SEKRETARIS:**
1. **RECEIVE NOTIFICATION**
   - WhatsApp notification
   - Email notification
   - Dashboard notification

2. **REVIEW SPPD**
   - Check completeness
   - Validate budget calculation
   - Review participants
   - Check supporting documents

3. **MAKE DECISION**
   - **APPROVE**: Continue to PPK
   - **REJECT**: End workflow with reason
   - **REVISION**: Request changes

4. **PROCESS APPROVAL**
   - Create approval record
   - Update SPPD status
   - Send notification to next approver
   - **Kode SPPD: masih null**
   - **Nomor Surat Tugas: masih null**

### **PPK - LEVEL 2 APPROVER (FINAL)**

#### **A. Hak Khusus:**
- Approve/reject SPPD di level 2 (final decision)
- Request revision jika ada masalah
- Melihat semua SPPD untuk approval

#### **B. Menu yang Dapat Diakses:**
```
Dashboard
├─ Pending Approvals
├─ Approval Statistics
└─ Recent Activities

Approval Management
├─ Approval Queue
├─ Approval History
├─ Bulk Approval
└─ Approval Settings

SPPD Management
├─ All SPPD (view only)
├─ SPPD by Status
└─ Export SPPD

Documents
├─ All Documents
├─ Document Templates
└─ Document Management

Reports
├─ Laporan
├─ Analytics
└─ Export Reports

Settings
├─ Profile
├─ Password
└─ System Settings
```

#### **C. Workflow PPK:**
1. **RECEIVE NOTIFICATION**
   - WhatsApp notification
   - Email notification
   - Dashboard notification

2. **FINAL REVIEW**
   - Review all details
   - Check previous approval (SEKRETARIS)
   - Validate final budget
   - Check compliance with regulations

3. **FINAL DECISION**
   - **APPROVE**: Complete workflow
   - **REJECT**: End workflow with reason
   - **REVISION**: Request changes

4. **FINAL APPROVAL**
   - Create approval record
   - **GENERATE KODE SPPD** (SPD/YYYY/MM/001)
   - **GENERATE NOMOR SURAT TUGAS** (ST/YYYY/001)
   - **SET TANGGAL SURAT TUGAS** (today's date)
   - Set status: 'completed'
   - Generate PDF document
   - Send notification to all participants

### **STAFF - PESERTA SPPD**

#### **A. Hak Khusus:**
- Melihat SPPD dimana dia menjadi peserta
- Download dokumen SPPD yang diikuti
- Update profil pribadi

#### **B. Menu yang Dapat Diakses:**
```
Dashboard
├─ My SPPD Participation
├─ Recent Activities
└─ Notifications

SPPD Management
├─ My Participations
└─ Download Documents

Profile
├─ Edit Profile
├─ Change Password
└─ View Profile
```

#### **C. Workflow STAFF:**
1. **RECEIVE NOTIFICATION**
   - Notified when selected as participant
   - Notified when SPPD approved/rejected

2. **VIEW SPPD DETAILS**
   - View SPPD information
   - Check approval status
   - View travel details

3. **DOWNLOAD DOCUMENTS**
   - Download PDF SPPD (hanya jika status completed)
   - Download supporting documents
   - Access approval letters

### **ADMIN - SYSTEM ADMINISTRATOR**

#### **A. Hak Khusus:**
- Akses penuh ke semua fitur
- Mengelola user dan role
- Monitoring sistem
- Export data dan laporan

#### **B. Menu yang Dapat Diakses:**
```
Dashboard
├─ System Overview
├─ User Statistics
├─ System Health
└─ Recent Activities

User Management
├─ User List
├─ Create User
├─ Edit User
├─ Activate/Deactivate User
└─ Export Users

SPPD Management
├─ All SPPD
├─ SPPD by Status
├─ SPPD by User
└─ Export SPPD

Approval Management
├─ Approval Queue
├─ Approval History
├─ Approval Statistics
└─ Approval Settings

Documents
├─ All Documents
├─ Document Management
├─ Document Templates
└─ Document Statistics

Reports
├─ Laporan
├─ Analytics
├─ System Reports
└─ Export Reports

Settings
├─ System Settings
├─ User Settings
├─ Notification Settings
└─ Security Settings
```

#### **C. Workflow ADMIN:**
1. **SYSTEM MONITORING**
   - Monitor user activities
   - Check system health
   - Review logs

2. **USER MANAGEMENT**
   - Create new users
   - Edit user information
   - Activate/deactivate users
   - Assign roles

3. **DATA MANAGEMENT**
   - Export data
   - Generate reports
   - Backup data

4. **SYSTEM MAINTENANCE**
   - Update system settings
   - Manage notifications
   - Security configurations

---

## ✅ **PROSES APPROVAL**

### **APPROVAL FLOW MATRIX:**
```
┌─────────────┬─────────────┬─────────────┬─────────────┐
│   ROLE      │  LEVEL 1    │  LEVEL 2    │  LEVEL 3    │
├─────────────┼─────────────┼─────────────┼─────────────┤
│   Staff     │   ❌ DENY   │   ❌ DENY   │   ❌ DENY   │
│  Kasubbag   │ Sekretaris  │   PPK     │   COMPLETE  │
│ Sekretaris  │   ❌ DENY   │   ❌ DENY   │   ❌ DENY   │
│   PPK     │   ❌ DENY   │   ❌ DENY   │   ❌ DENY   │
│   Admin     │   ❌ DENY   │   ❌ DENY   │   ❌ DENY   │
└─────────────┴─────────────┴─────────────┴─────────────┘
```

### **APPROVAL PROCESS:**
1. **KASUBBAG submits SPPD**
   - Status: `in_review`
   - Current Level: 1
   - Next Approver: SEKRETARIS
   - **Kode SPPD: null**
   - **Nomor Surat Tugas: null**

2. **SEKRETARIS reviews**
   - Can: Approve, Reject, Request Revision
   - If Approve: Level 2 → PPK
   - If Reject: End workflow
   - If Revision: Back to KASUBBAG
   - **Kode SPPD: masih null**
   - **Nomor Surat Tugas: masih null**

3. **PPK reviews (Final)**
   - Can: Approve, Reject, Request Revision
   - If Approve: 
     - **GENERATE KODE SPPD**
     - **GENERATE NOMOR SURAT TUGAS**
     - **SET TANGGAL SURAT TUGAS**
     - Status → `completed`
   - If Reject: End workflow
   - If Revision: Back to KASUBBAG

### **REVISION PROCESS:**
1. **Approver requests revision**
   - Status: `revision`
   - Current Level: 0
   - Notification to KASUBBAG

2. **KASUBBAG revises**
   - Edit SPPD details
   - Resubmit for approval
   - Status: `in_review`
   - Current Level: 1 (back to SEKRETARIS)

---

## 📊 **STATUS SPPD**

### **STATUS ENUM:**
```php
enum TravelRequestStatus: string
{
    case DRAFT = 'draft';           // SPPD baru dibuat, belum submit
    case IN_REVIEW = 'in_review';   // SPPD sedang dalam proses approval
    case COMPLETED = 'completed';   // SPPD sudah disetujui semua level
    case REJECTED = 'rejected';     // SPPD ditolak oleh salah satu approver
    case REVISION = 'revision';     // SPPD diminta revisi
}
```

### **STATUS FLOW:**
```
DRAFT → IN_REVIEW → COMPLETED
  │         │
  │         ├─ REJECTED
  │         └─ REVISION → IN_REVIEW
  │
  └─ IN_REVIEW (resubmit)
```

### **CURRENT_APPROVAL_LEVEL:**
- **0**: No approval level (draft/revision)
- **1**: Waiting for SEKRETARIS approval
- **2**: Waiting for PPK approval
- **0**: Completed (after PPK approval)

### **KODE SPPD GENERATION:**
- **Format**: `SPD/YYYY/MM/001`
- **Generated**: Saat PPK approve (final approval)
- **Before approval**: `null`
- **After approval**: `SPD/2025/07/001`

### **NOMOR SURAT TUGAS GENERATION:**
- **Format**: `ST/YYYY/001`
- **Generated**: Saat PPK approve (final approval)
- **Before approval**: `null`
- **After approval**: `ST/2025/001`

---

## 🔔 **NOTIFIKASI SISTEM**

### **NOTIFICATION TYPES:**
1. **SPPD Submitted**
   - To: SEKRETARIS
   - Method: WhatsApp, Email, Dashboard

2. **SPPD Approved (Level 1)**
   - To: PPK
   - Method: WhatsApp, Email, Dashboard

3. **SPPD Approved (Final)**
   - To: KASUBBAG, All Participants
   - Method: WhatsApp, Email, Dashboard
   - **Note**: Kode SPPD dan nomor surat tugas sudah digenerate

4. **SPPD Rejected**
   - To: KASUBBAG
   - Method: WhatsApp, Email, Dashboard

5. **SPPD Revision Requested**
   - To: KASUBBAG
   - Method: WhatsApp, Email, Dashboard

6. **Participant Added**
   - To: Selected Staff
   - Method: Email, Dashboard

### **NOTIFICATION CHANNELS:**
- **WhatsApp**: Real-time notifications
- **Email**: Detailed notifications
- **Dashboard**: In-app notifications
- **SMS**: Backup notifications

---

## 📄 **DOKUMEN DAN FILE**

### **DOCUMENT TYPES:**
1. **SPPD PDF**
   - Generated automatically when approved
   - Contains all SPPD details
   - **Includes generated kode SPPD**
   - **Includes generated nomor surat tugas**
   - Official document format

2. **Supporting Documents**
   - Uploaded by KASUBBAG
   - File types: PDF, DOCX, XLSX, JPG, PNG
   - Size limit: 10MB per file

3. **Approval Letters**
   - Generated for each approval
   - Contains approver signature
   - Official approval record

### **DOCUMENT ACCESS:**
- **Creator (KASUBBAG)**: Full access
- **Participants (STAFF)**: Read access
- **Approvers (SEKRETARIS, PPK)**: Full access
- **Admin**: Full access

### **PDF GENERATION:**
- **Only available**: When status = 'completed'
- **Contains**: Kode SPPD, nomor surat tugas, tanggal surat tugas
- **Access**: Creator, participants, approvers, admin

---

## 📈 **LAPORAN DAN ANALYTICS**

### **REPORT TYPES:**
1. **SPPD Reports**
   - By status (completed, rejected, in_review)
   - By date range
   - By approver
   - By budget range

2. **Approval Reports**
   - Approval statistics
   - Approval time analysis
   - Rejection reasons
   - Revision patterns

3. **Budget Reports**
   - Total budget by period
   - Budget by destination
   - Budget by transport type
   - Budget trends

4. **User Reports**
   - User activity
   - SPPD creation by user
   - Approval performance
   - Participant statistics

### **ANALYTICS DASHBOARD:**
- **Real-time statistics**
- **Interactive charts**
- **Export capabilities**
- **Filter options**

---

## 🔒 **SECURITY & ACCESS CONTROL**

### **AUTHENTICATION:**
- Login with email/password
- Session management
- CSRF protection
- Rate limiting

### **AUTHORIZATION:**
- Role-based access control
- Policy-based permissions
- Route protection
- Middleware validation

### **DATA PROTECTION:**
- Password hashing
- Sensitive data encryption
- Audit logging
- Backup procedures

---

## 🚀 **SYSTEM FEATURES**

### **CORE FEATURES:**
- ✅ Multi-level approval workflow
- ✅ Participant management
- ✅ Document upload/download
- ✅ Real-time notifications
- ✅ PDF generation (hanya setelah approval final)
- ✅ Email notifications
- ✅ WhatsApp integration
- ✅ Dashboard analytics
- ✅ Report generation
- ✅ Export capabilities
- ✅ User management
- ✅ Role-based access
- ✅ Audit logging
- ✅ Backup system

### **TECHNICAL STACK:**
- **Backend**: Laravel 11 (PHP)
- **Frontend**: Blade + Alpine.js
- **Database**: MySQL/PostgreSQL
- **PDF**: DomPDF
- **Notifications**: WhatsApp API + Email
- **UI**: Tailwind CSS
- **Charts**: Chart.js


---

## 🔧 **IMPLEMENTASI REAL SISTEM**

### **KODE SPPD GENERATION:**
```php
// Di ApprovalService.php - processTravelRequestApproval()
if ($allApprovalsCompleted) {
    // Generate kode SPD jika belum ada
    if (empty($travelRequest->kode_sppd)) {
        $kodeSppd = app(\App\Services\TravelRequestService::class)->generateKodeSppd();
        $travelRequest->kode_sppd = $kodeSppd;
    }
    // Generate nomor surat tugas dan tanggal surat tugas saat approve
    if (empty($travelRequest->nomor_surat_tugas)) {
        $nomorSuratTugas = app(\App\Services\TravelRequestService::class)->generateNomorSuratTugas();
        $travelRequest->nomor_surat_tugas = $nomorSuratTugas;
    }
    $travelRequest->update([
        'status' => 'completed',
        'current_approval_level' => 0,
        'approved_at' => now(),
        'updated_at' => now(),
        'kode_sppd' => $travelRequest->kode_sppd,
        'nomor_surat_tugas' => $travelRequest->nomor_surat_tugas,
        'tanggal_surat_tugas' => now()->format('Y-m-d'),
    ]);
}
```

### **TRAVEL REQUEST CREATION:**
```php
// Di TravelRequestService.php - prepareTravelRequestData()
return [
    'user_id' => $userId,
    'kode_sppd' => null,  // BELUM DIGENERATE
    'nomor_surat_tugas' => null,  // BELUM DIGENERATE
    'tanggal_surat_tugas' => null,  // BELUM DISET
    'status' => $status,
    'current_approval_level' => 0,
    'submitted_at' => $submittedAt,
    // ... other fields
];
```

### **PDF EXPORT RESTRICTION:**
```php
// Di TravelRequestController.php - exportPdf()
if ($travelRequest->status !== TravelRequestStatus::COMPLETED->value) {
    abort(403, 'SPPD hanya bisa diunduh jika sudah disetujui dan statusnya completed.');
}
```

---

## 🔧 **IMPLEMENTASI REAL SISTEM**

### **KODE SPPD GENERATION:**
```php
// Di ApprovalService.php - processTravelRequestApproval()
if ($allApprovalsCompleted) {
    // Generate kode SPD jika belum ada
    if (empty($travelRequest->kode_sppd)) {
        $kodeSppd = app(\App\Services\TravelRequestService::class)->generateKodeSppd();
        $travelRequest->kode_sppd = $kodeSppd;
    }
    // Generate nomor surat tugas dan tanggal surat tugas saat approve
    if (empty($travelRequest->nomor_surat_tugas)) {
        $nomorSuratTugas = app(\App\Services\TravelRequestService::class)->generateNomorSuratTugas();
        $travelRequest->nomor_surat_tugas = $nomorSuratTugas;
    }
    $travelRequest->update([
        'status' => 'completed',
        'current_approval_level' => 0,
        'approved_at' => now(),
        'updated_at' => now(),
        'kode_sppd' => $travelRequest->kode_sppd,
        'nomor_surat_tugas' => $travelRequest->nomor_surat_tugas,
        'tanggal_surat_tugas' => now()->format('Y-m-d'),
    ]);
}
```

### **TRAVEL REQUEST CREATION:**
```php
// Di TravelRequestService.php - prepareTravelRequestData()
return [
    'user_id' => $userId,
    'kode_sppd' => null,  // BELUM DIGENERATE
    'nomor_surat_tugas' => null,  // BELUM DIGENERATE
    'tanggal_surat_tugas' => null,  // BELUM DISET
    'status' => $status,
    'current_approval_level' => 0,
    'submitted_at' => $submittedAt,
    // ... other fields
];
```

### **PDF EXPORT RESTRICTION:**
```php
// Di TravelRequestController.php - exportPdf()
if ($travelRequest->status !== TravelRequestStatus::COMPLETED->value) {
    abort(403, 'SPPD hanya bisa diunduh jika sudah disetujui dan statusnya completed.');
}
```

---

**Dokumen ini dibuat otomatis oleh sistem pada Juli 2025**
**Status: AKTIF DAN BERFUNGSI DENGAN BAIK** ✅
**Update: Sesuai implementasi real sistem** ✅