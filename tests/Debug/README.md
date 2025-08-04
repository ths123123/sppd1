# Debug Tests Structure

## Overview
Folder ini berisi file-file test untuk debugging dan troubleshooting sistem SPPD-KPU.

## Structure

### `/Manual/`
File-file test manual untuk debugging fitur spesifik:
- `test_*.php` - Test untuk berbagai skenario fitur
- `debug_*.php` - Debug workflow dan logic
- `add_test_*.php` - Test untuk menambah data
- `fix_*.php` - Script untuk memperbaiki data

### `/Check/`
File-file untuk mengecek status sistem:
- `check_*.php` - Script untuk mengecek data dan status

## Usage

### Manual Tests
```bash
# Jalankan test manual
php tests/Debug/Manual/test_participant_sync.php
php tests/Debug/Manual/debug_revision_workflow.php
```

### Check Scripts
```bash
# Cek status sistem
php tests/Debug/Check/check_participants.php
php tests/Debug/Check/check_travel_requests.php
```

## Notes
- File-file ini untuk debugging dan tidak dimaksudkan untuk production
- Selalu backup data sebelum menjalankan script debug
- Dokumentasi lengkap ada di `docs/testing/` 