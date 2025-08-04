# Prompt Komprehensif: Pembersihan dan Optimasi Sistem SPPD

## Konteks Sistem
Saya memiliki sistem SPPD (Surat Perintah Perjalanan Dinas) yang sudah berjalan, namun telah mengalami revisi alur kerja. Sistem masih mengingat alur lama dan terdapat file-file yang tidak terpakai.

## Tujuan Utama
1. Membersihkan sistem dari alur lama yang tidak digunakan
2. Memastikan alur baru dikenali oleh seluruh sistem
3. Menghapus file-file yang tidak diperlukan
4. **PENTING**: Tidak merusak fungsionalitas sistem yang sudah berjalan

## Analisis yang Dibutuhkan

### 1. Audit Kode dan File
```
Lakukan analisis mendalam pada:
- Semua file controller, model, view yang terkait SPPD
- Route/routing yang masih mengacu ke alur lama
- File konfigurasi yang mungkin masih menyimpan setting lama
- Database schema dan migration files
- File JavaScript/frontend yang masih menggunakan endpoint lama
- File CSS/styling yang tidak terpakai
- File gambar/asset yang tidak digunakan
- Dokumentasi dan comment code yang outdated
```

### 2. Identifikasi Alur Lama vs Baru
```
Bandingkan dan identifikasi:
- Endpoint API yang sudah tidak digunakan
- Function/method yang redundant
- Database table/column yang tidak terpakai
- Business logic yang sudah deprecated
- Validation rules yang sudah berubah
- Permission/authorization yang sudah tidak relevan
```

### 3. Cache dan Session Management
```
Bersihkan:
- Application cache yang menyimpan data alur lama
- Browser cache instruction
- Session data yang mungkin conflict
- Redis/Memcached keys yang outdated
- File cache yang sudah expired
```

## Langkah Pembersihan Bertahap

### Phase 1: Backup dan Dokumentasi
```
1. Buat backup lengkap sistem current
2. Dokumentasikan alur baru secara detail
3. List semua perubahan yang telah dilakukan
4. Identifikasi dependencies antar module
```

### Phase 2: Safe Cleanup Strategy
```
1. Rename/disable code lama, jangan langsung hapus
2. Implementasi feature flag untuk testing
3. Buat migration script untuk data cleanup
4. Test setiap perubahan di environment staging
```

### Phase 3: Systematic Cleanup
```
1. Hapus route yang tidak digunakan
2. Clean unused imports dan dependencies
3. Remove dead code dan commented code lama
4. Update configuration files
5. Clean database dari data testing lama
```

### Phase 4: Optimization
```
1. Optimize database queries
2. Minify dan compress assets
3. Update documentation
4. Implement proper error handling untuk alur baru
```

## Checklist Keamanan

### Sebelum Cleanup
- [ ] Backup database dan codebase
- [ ] Test semua functionality critical
- [ ] Dokumentasi alur current dan target
- [ ] Setup monitoring untuk detect issues

### Selama Cleanup
- [ ] Test setiap perubahan secara incremental
- [ ] Monitor system performance
- [ ] Check error logs secara berkala
- [ ] Validate user permissions masih berfungsi

### Setelah Cleanup
- [ ] Full system testing
- [ ] User acceptance testing
- [ ] Performance benchmarking
- [ ] Update user manual/guide

## Perintah Spesifik untuk Analisis

### 1. Analisis Struktur File
```
Scan semua directory dan berikan:
- List file yang terakhir dimodifikasi > 3 bulan lalu
- File yang tidak pernah di-include/import
- Function yang tidak pernah dipanggil
- CSS class yang tidak digunakan
- JavaScript function yang orphaned
```

### 2. Database Cleanup
```
Identifikasi dan bersihkan:
- Table yang tidak memiliki foreign key relationship
- Column yang selalu NULL atau tidak pernah diupdate
- Index yang tidak pernah digunakan
- Stored procedure/trigger yang deprecated
- Data test yang masih tersisa di production
```

### 3. API Endpoint Audit
```
Review semua endpoint dan identifikasi:
- Route yang tidak pernah diakses (check access logs)
- API yang return deprecated data structure
- Endpoint yang tidak memiliki proper authentication
- Method yang tidak sesuai REST convention
```

## Template Report Output

Berikan hasil analisis dalam format:

### Summary Executive
- Total file yang bisa dihapus
- Estimasi space yang akan dihemat
- Risk level dari setiap cleanup action
- Timeline recommended untuk cleanup

### Detailed Findings
- File-by-file analysis dengan reasoning
- Code snippets yang perlu diupdate
- SQL scripts untuk database cleanup
- Step-by-step cleanup procedure

### Recommendations
- Prioritas cleanup (high/medium/low risk)
- Best practices untuk maintenance kedepan
- Monitoring yang perlu disetup
- Documentation yang perlu diupdate

## Constraints dan Batasan

### Yang TIDAK Boleh Disentuh
- Core authentication system
- Payment/financial modules (jika ada)
- User data dan profile
- Audit trail dan logging system
- Backup dan recovery mechanism

### Yang Harus Hati-hati
- Database migration scripts
- Configuration files production
- Third-party integration
- Email templates yang masih digunakan
- Report generation modules

## Testing Strategy

### Unit Testing
- Test semua function critical setelah cleanup
- Validate data integrity
- Check performance impact

### Integration Testing
- Test alur end-to-end SPPD baru
- Validate semua user role permissions
- Check third-party integration masih berfungsi

### User Acceptance Testing
- Test dengan sample user real
- Validate UI/UX tidak broken
- Check mobile responsiveness

## Monitoring dan Rollback Plan

### Monitoring Setup
- Error rate monitoring
- Performance metrics tracking
- User activity monitoring
- Database query performance

### Rollback Strategy
- Quick rollback procedure jika ada critical issue
- Database restore point
- Code rollback mechanism
- Communication plan ke user

---

**Instruksi Eksekusi:**
Jalankan analisis ini secara bertahap, mulai dari audit file, lalu cleanup ringan, test, baru lanjut ke cleanup yang lebih agresif. Selalu prioritaskan keamanan sistem dan user experience yang sudah berjalan baik.

Tips tambahan:

Sertakan informasi spesifik sistem Anda (tech stack, struktur folder, dll)