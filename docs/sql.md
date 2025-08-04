# 📋 SQL QUERIES - SPPD KPU KABUPATEN CIREBON
## Database Troubleshooting & Maintenance Queries

---

## 🔍 **QUERY UNTUK DEBUGGING SPPD APPROVAL**

### **1. Cek Status SPPD yang Tidak Muncul di Menu Approval**
```sql
-- Cek SPPD dengan current_approval_level = 0 (tidak muncul di approval)
SELECT 
    id,
    kode_sppd,
    user_id,
    tujuan,
    status,
    current_approval_level,
    submitted_at,
    created_at
FROM travel_requests 
WHERE current_approval_level = 0 
AND status = 'in_review'
ORDER BY created_at DESC;
```

### **2. Cek Semua SPPD dan Status Approval Level**
```sql
-- Cek semua SPPD dengan detail approval level
SELECT 
    tr.id,
    tr.kode_sppd,
    u.name as pemohon,
    tr.tujuan,
    tr.status,
    tr.current_approval_level,
    tr.submitted_at,
    tr.created_at
FROM travel_requests tr
JOIN users u ON tr.user_id = u.id
ORDER BY tr.created_at DESC;
```

### **3. Fix SPPD yang Tidak Muncul di Menu Approval**
```sql
-- Update SPPD agar muncul di menu approval (set current_approval_level = 1)
UPDATE travel_requests 
SET current_approval_level = 1 
WHERE id = 1;  -- Ganti dengan ID SPPD yang bermasalah

-- Atau update semua SPPD yang stuck
UPDATE travel_requests 
SET current_approval_level = 1 
WHERE current_approval_level = 0 
AND status = 'in_review';
```

---

## 📊 **QUERY UNTUK CEK TANGGAL SURAT TUGAS**

### **4. Cek SPPD yang Sudah Completed tapi Tanggal Surat Tugas Kosong**
```sql
-- Cek SPPD completed yang tanggal_surat_tugas masih NULL
SELECT 
    id,
    kode_sppd,
    nomor_surat_tugas,
    tanggal_surat_tugas,
    status,
    approved_at,
    created_at
FROM travel_requests 
WHERE status = 'completed' 
AND tanggal_surat_tugas IS NULL
ORDER BY created_at DESC;
```

### **5. Fix Tanggal Surat Tugas untuk SPPD Completed**
```sql
-- Update tanggal_surat_tugas untuk SPPD yang sudah completed
UPDATE travel_requests 
SET tanggal_surat_tugas = approved_at::date
WHERE status = 'completed' 
AND tanggal_surat_tugas IS NULL;

-- Atau set tanggal manual
UPDATE travel_requests 
SET tanggal_surat_tugas = '2025-01-07'  -- Ganti dengan tanggal yang diinginkan
WHERE id = 1;  -- Ganti dengan ID SPPD
```

---

## 👥 **QUERY UNTUK USER MANAGEMENT**

### **6. Cek User yang Ada di Database**
```sql
-- Cek semua user
SELECT 
    id,
    name,
    email,
    nip,
    role,
    jabatan,
    is_active,
    created_at
FROM users 
ORDER BY created_at DESC;
```

### **7. Cek User dengan Role Tertentu**
```sql
-- Cek user dengan role sekretaris
SELECT 
    id,
    name,
    email,
    nip,
    role,
    jabatan
FROM users 
WHERE role = 'sekretaris'
AND is_active = true;
```

### **8. Cek User dengan NIP Tertentu**
```sql
-- Cek user berdasarkan NIP
SELECT 
    id,
    name,
    email,
    nip,
    role,
    jabatan
FROM users 
WHERE nip = '197012345678901234';  -- Ganti dengan NIP yang dicari
```

---

## 🔐 **QUERY UNTUK APPROVAL WORKFLOW**

### **9. Cek Approval History**
```sql
-- Cek riwayat approval untuk SPPD tertentu
SELECT 
    a.id,
    a.travel_request_id,
    u.name as approver_name,
    a.role as approver_role,
    a.level,
    a.status,
    a.comments,
    a.approved_at,
    a.created_at
FROM approvals a
JOIN users u ON a.approver_id = u.id
WHERE a.travel_request_id = 1  -- Ganti dengan ID SPPD
ORDER BY a.level ASC;
```

### **10. Cek SPPD yang Menunggu Approval**
```sql
-- Cek SPPD yang sedang dalam proses approval
SELECT 
    tr.id,
    tr.kode_sppd,
    u.name as pemohon,
    tr.tujuan,
    tr.status,
    tr.current_approval_level,
    tr.submitted_at
FROM travel_requests tr
JOIN users u ON tr.user_id = u.id
WHERE tr.status = 'in_review'
ORDER BY tr.submitted_at ASC;
```

---

## 🗑️ **QUERY UNTUK CLEANUP DATA**

### **11. Hapus User Test (Jika Perlu)**
```sql
-- Hapus user berdasarkan email
DELETE FROM users 
WHERE email LIKE '%test%' 
OR email LIKE '%example%';

-- Hapus user berdasarkan role
DELETE FROM users 
WHERE role = 'staff' 
AND created_at > '2025-01-01';  -- Hapus staff yang dibuat setelah tanggal tertentu
```

### **12. Reset Approval Level (Jika Perlu)**
```sql
-- Reset semua approval level ke 0
UPDATE travel_requests 
SET current_approval_level = 0 
WHERE status = 'in_review';

-- Reset status ke draft
UPDATE travel_requests 
SET status = 'draft', 
    current_approval_level = 0 
WHERE status = 'in_review';
```

---

## 📈 **QUERY UNTUK ANALYTICS**

### **13. Statistik SPPD per Status**
```sql
-- Hitung jumlah SPPD per status
SELECT 
    status,
    COUNT(*) as jumlah,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM travel_requests), 2) as persentase
FROM travel_requests 
GROUP BY status
ORDER BY jumlah DESC;
```

### **14. Statistik SPPD per Bulan**
```sql
-- Hitung jumlah SPPD per bulan
SELECT 
    DATE_TRUNC('month', created_at) as bulan,
    COUNT(*) as jumlah_sppd,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as sppd_selesai
FROM travel_requests 
GROUP BY DATE_TRUNC('month', created_at)
ORDER BY bulan DESC;
```

### **15. Statistik User Activity**
```sql
-- Hitung aktivitas user (jumlah SPPD yang dibuat)
SELECT 
    u.name,
    u.role,
    COUNT(tr.id) as jumlah_sppd,
    COUNT(CASE WHEN tr.status = 'completed' THEN 1 END) as sppd_selesai
FROM users u
LEFT JOIN travel_requests tr ON u.id = tr.user_id
GROUP BY u.id, u.name, u.role
ORDER BY jumlah_sppd DESC;
```

---

## 🔧 **QUERY UNTUK MAINTENANCE**

### **16. Cek Database Size**
```sql
-- Cek ukuran database
SELECT 
    pg_size_pretty(pg_database_size(current_database())) as database_size;

-- Cek ukuran tabel
SELECT 
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as table_size
FROM pg_tables 
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
```

### **17. Cek Index Usage**
```sql
-- Cek index yang ada
SELECT 
    indexname,
    tablename,
    indexdef
FROM pg_indexes 
WHERE schemaname = 'public'
ORDER BY tablename, indexname;
```

### **18. Vacuum Database**
```sql
-- Vacuum database untuk maintenance
VACUUM ANALYZE;

-- Vacuum full (hati-hati, bisa lock table)
-- VACUUM FULL;
```

---

## ⚠️ **QUERY BERBAHAYA (BACKUP DULU!)**

### **19. Reset Semua Data (HATI-HATI!)**
```sql
-- Backup dulu sebelum menjalankan query ini!
-- TRUNCATE TABLE approvals CASCADE;
-- TRUNCATE TABLE travel_requests CASCADE;
-- DELETE FROM users WHERE role != 'admin';
```

### **20. Reset Sequence ID (HATI-HATI!)**
```sql
-- Reset sequence ID (jika ada gap di ID)
-- ALTER SEQUENCE travel_requests_id_seq RESTART WITH 1;
-- ALTER SEQUENCE users_id_seq RESTART WITH 1;
```

---

## 📝 **CATATAN PENTING**

### **Sebelum Menjalankan Query:**
1. ✅ **Backup database** terlebih dahulu
2. ✅ **Test di environment development** dulu
3. ✅ **Cek WHERE clause** dengan teliti
4. ✅ **Gunakan LIMIT** untuk query SELECT besar
5. ✅ **Monitor performance** saat menjalankan query

### **Best Practices:**
- Selalu gunakan `WHERE` clause yang spesifik
- Gunakan `ORDER BY` untuk hasil yang konsisten
- Monitor query execution time
- Backup sebelum UPDATE/DELETE
- Test di data sample dulu

### **Emergency Contacts:**
- Database Admin: [Contact Info]
- System Admin: [Contact Info]
- Backup Location: [Path]

---

**Last Updated:** January 2025  
**Version:** 1.0  
**Maintained by:** Development Team 