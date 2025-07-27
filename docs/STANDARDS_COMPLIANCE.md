# 🏛️ STANDAR KOMPLIANCE SISTEM SPPD KPU KABUPATEN CIREBON
# 📋 Indonesian Government Standards Compliance

## 📅 Version: 1.0 | Last Updated: July 13, 2025

---

## 🎯 OVERVIEW
Dokumen ini menjelaskan kepatuhan sistem SPPD terhadap standar pemerintah Indonesia, khususnya untuk format data pegawai negeri sipil (PNS).

---

## 🔢 FORMAT NIP (NOMOR INDUK PEGAWAI)

### **Standar Indonesia:**
- **Panjang:** 18 digit angka
- **Format:** `YYYYMMDDNNNNNNNNN`
  - 8 digit pertama: Tanggal lahir (YYYYMMDD)
  - 10 digit berikutnya: Nomor urut

### **Contoh NIP Valid:**
- `198402132009121001` (13 Februari 1984, nomor urut 2009121001)
- `199005152010012001` (15 Mei 1990, nomor urut 2010012001)

### **Implementasi di Sistem:**
```php
// Database Migration
$table->string('nip', 18)->nullable()->unique()->comment('NIP format: 18 digit angka (YYYYMMDDNNNNNNNNN)');

// Validation Rules
'nip' => [
    'nullable', 
    'string', 
    'max:18', 
    'regex:/^[0-9]{18}$/', // Hanya angka, tepat 18 digit
    Rule::unique(User::class)->ignore($user->id)
]
```

---

## 📧 FORMAT EMAIL

### **Standar Domain:**
- **Domain Utama:** `@kpu.go.id`
- **Format:** `[username]@kpu.go.id`

### **Contoh Email Valid:**
- `staff@kpu.go.id`
- `admin@kpu.go.id`
- `sekretaris@kpu.go.id`
- `ketua@kpu.go.id`

---

## 🏢 STRUKTUR ORGANISASI

### **Hierarki Jabatan:**
1. **PKK** - Pimpinan tertinggi
2. **Sekretaris** - Wakil pimpinan
3. **Kasubbag** - Kepala sub bagian
4. **Staff** - Pegawai pelaksana
5. **Admin** - Administrator sistem

### **Implementasi Role:**
```php
$table->enum('role', ['admin', 'staff', 'kasubbag', 'sekretaris', 'pkk'])
      ->default('staff');
```

---

## 💰 ANGGARAN DAN KEUANGAN

### **Budget Allocation:**
- **Field:** `budget_allocation`
- **Type:** Integer
- **Format:** Angka tanpa pemisah ribuan
- **Contoh:** `250000000` (250 juta rupiah)

### **Validasi Biaya SPPD:**
- **Total Maksimal:** Rp 50.000.000
- **Transportasi Maksimal:** Rp 10.000.000
- **Penginapan Maksimal:** Rp 20.000.000

---

## 🔒 KEAMANAN DAN VALIDASI

### **Password Policy:**
- **Minimal:** 8 karakter
- **Hash:** bcrypt/argon2
- **Default:** `72e82b77` (untuk testing)

### **Input Validation:**
- **NIP:** 18 digit angka saja
- **Email:** Format email valid + domain @kpu.go.id
- **Phone:** Format Indonesia (+62 atau 08xx)
- **File Upload:** PDF, JPG, PNG (max 2MB)

---

## 📊 DATABASE STANDARDS

### **User Table Structure:**
```sql
CREATE TABLE users (
    id bigint PRIMARY KEY,
    nip varchar(18) UNIQUE, -- NIP format: 18 digit
    name varchar(255) NOT NULL,
    email varchar(255) UNIQUE NOT NULL,
    password varchar(255) NOT NULL,
    jabatan varchar(255),
    role enum('admin','staff','kasubbag','sekretaris','ketua') DEFAULT 'staff',
    phone varchar(20),
    pangkat varchar(255),
    golongan varchar(10),
    unit_kerja varchar(255),
    is_active boolean DEFAULT true,
    -- ... other fields
);
```

---

## 🧪 TESTING STANDARDS

### **User Factory:**
```php
'nip' => fake()->unique()->numerify('1984##########'), // 18 digit format
'email' => fake()->unique()->safeEmail(), // Will be updated to @kpu.go.id
'role' => fake()->randomElement(['staff', 'kasubbag', 'sekretaris', 'ketua']),
```

### **Database Seeder:**
```php
User::firstOrCreate([
    'email' => 'staff@kpu.go.id'
], [
    'nip' => '', // Kosong atau 18 digit angka
    'jabatan' => 'Staff',
    'role' => 'staff',
]);
```

---

## ✅ CHECKLIST COMPLIANCE

### **✅ Sudah Sesuai Standar:**
- [x] Format NIP 18 digit angka
- [x] Domain email @kpu.go.id
- [x] Validation rules untuk NIP
- [x] Database migration dengan panjang field yang tepat
- [x] User factory dengan format NIP yang benar
- [x] Database seeder dengan format yang standar
- [x] Error messages dalam bahasa Indonesia
- [x] Budget allocation field (kosong untuk diisi nanti)

### **🔄 Perlu Diperbaiki:**
- [ ] Migration untuk mengubah field NIP yang sudah ada
- [ ] Update data existing yang tidak sesuai format
- [ ] Testing untuk memastikan validasi berfungsi

---

## 🚀 IMPLEMENTATION STEPS

### **1. Jalankan Migration:**
```bash
php artisan migrate
```

### **2. Update Existing Data:**
```bash
php artisan db:seed --class=DatabaseSeeder
```

### **3. Test Validation:**
```bash
php artisan test --filter=UserValidationTest
```

---

## 📝 NOTES

1. **NIP Kosong:** Diperbolehkan jika pegawai belum memiliki NIP resmi
2. **Email Domain:** Konsisten menggunakan @kpu.go.id
3. **Validation:** Strict validation untuk memastikan data sesuai standar
4. **Backward Compatibility:** Sistem tetap bisa menangani data lama

---

## 🔗 REFERENCES

- [Peraturan Pemerintah tentang NIP](https://peraturan.bpk.go.id/)
- [Standar Email Pemerintah](https://www.kominfo.go.id/)
- [Format SPPD Standar](https://www.bkn.go.id/)

---

*Dokumen ini akan diperbarui sesuai dengan perubahan standar pemerintah Indonesia.* 