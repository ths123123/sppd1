# Participant Bug Prevention Guide

## Overview
Dokumen ini berisi solusi permanen untuk mencegah bug-bug yang berulang terkait dengan peserta SPPD.

## Bug yang Sering Terjadi

### 1. Error "participants.0 field must be an integer"
**Penyebab:** Data participants dikirim dalam format yang tidak sesuai dengan validasi Laravel
**Solusi:** Menggunakan `ParticipantService` dan validasi yang robust

### 2. Peserta tidak tersimpan
**Penyebab:** Format data tidak konsisten antara frontend dan backend
**Solusi:** Normalisasi data di `TravelRequestStoreRequest`

### 3. Avatar tidak muncul
**Penyebab:** Data user tidak lengkap atau tidak konsisten
**Solusi:** Menggunakan `ParticipantService::getParticipantsWithAvatars()`

## Solusi Permanen

### 1. ParticipantService
```php
// Gunakan service ini untuk semua operasi peserta
$participantService = app(ParticipantService::class);

// Parse participants dari berbagai format
$participantIds = $participantService->parseParticipants($request->participants);

// Sync participants ke travel request
$participantService->syncParticipants($travelRequest, $request->participants);

// Get participants dengan avatar
$participants = $participantService->getParticipantsWithAvatars($travelRequest);
```

### 2. Validasi yang Robust
```php
// Di TravelRequestStoreRequest
protected function prepareForValidation()
{
    $participants = $this->input('participants');
    if ($participants) {
        $processedParticipants = $this->normalizeParticipantsData($participants);
        $this->merge(['participants' => $processedParticipants]);
    }
}
```

### 3. Format Data yang Didukung
- `[]` (array kosong)
- `null` (null value)
- `""` (string kosong)
- `"1"` (single ID string)
- `["1"]` (single ID array)
- `["1", "2", "3"]` (multiple IDs array)
- `"1,2,3"` (comma-separated string)
- `"1, 2, 3"` (comma-separated with spaces)
- `["1,2,3"]` (array with comma string)
- `["1", "2,3", "4"]` (mixed formats)

## Best Practices

### 1. Selalu Gunakan ParticipantService
```php
// ✅ BENAR
$this->participantService->syncParticipants($travelRequest, $request->participants);

// ❌ SALAH
$travelRequest->participants()->sync($participantIds);
```

### 2. Validasi Data Sebelum Disimpan
```php
// ✅ BENAR
$participantIds = $this->participantService->parseParticipants($participants);

// ❌ SALAH
$participantIds = explode(',', $participants);
```

### 3. Logging untuk Debugging
```php
// Log semua operasi peserta
Log::info('Participants synced', [
    'travel_request_id' => $travelRequest->id,
    'participant_ids' => $participantIds,
    'participant_count' => count($participantIds)
]);
```

## Testing

### 1. Test Semua Format Data
```php
$testCases = [
    'empty_array' => ['participants' => []],
    'null_value' => ['participants' => null],
    'single_id' => ['participants' => '1'],
    'multiple_ids' => ['participants' => ['1', '2', '3']],
    'comma_separated' => ['participants' => '1,2,3'],
    'mixed_formats' => ['participants' => ['1', '2,3', '4']],
];
```

### 2. Test Error Handling
```php
// Test invalid data
$invalidData = ['participants' => ['abc', 'def']];
// Should handle gracefully without crashing
```

## Monitoring

### 1. Log Error Patterns
```php
// Di ParticipantValidationMiddleware
if ($participantErrors->isNotEmpty()) {
    Log::warning('Participant validation errors detected', [
        'errors' => $participantErrors->toArray(),
        'url' => $request->url(),
        'method' => $request->method()
    ]);
}
```

### 2. Alert untuk Error Berulang
```php
// Jika error terjadi lebih dari 3 kali dalam 1 jam
// Kirim alert ke developer
```

## Checklist Sebelum Deploy

- [ ] ParticipantService sudah diimplementasi
- [ ] Validasi robust sudah diterapkan
- [ ] Semua format data sudah ditest
- [ ] Error handling sudah ditambahkan
- [ ] Logging sudah dikonfigurasi
- [ ] Dokumentasi sudah diupdate

## Troubleshooting

### Jika Error Masih Terjadi

1. **Cek Log Laravel**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Cek Data Request**
   ```php
   Log::info('Request data', $request->all());
   ```

3. **Test Manual**
   ```php
   $participantService = app(ParticipantService::class);
   $result = $participantService->parseParticipants($request->participants);
   dd($result);
   ```

4. **Rollback jika Perlu**
   ```bash
   git revert <commit-hash>
   ```

## Kesimpulan

Dengan implementasi solusi ini:
- ✅ Bug "participants.0 field must be an integer" tidak akan terjadi lagi
- ✅ Peserta akan selalu tersimpan dengan benar
- ✅ Avatar akan selalu muncul
- ✅ Data akan konsisten di semua bagian aplikasi
- ✅ Error handling yang graceful
- ✅ Logging untuk debugging yang mudah 