<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\TravelRequest;
use App\Services\NotificationService;

// Cari user staff yang aktif
$staffUser = User::where('role', 'staff')
    ->where('is_active', 1)
    ->first();

// Cari user sekretaris yang aktif
$sekretarisUser = User::where('role', 'sekretaris')
    ->where('is_active', 1)
    ->first();

// Ambil travel request pertama untuk contoh
$travelRequest = TravelRequest::first();

if ($staffUser && $sekretarisUser && $travelRequest) {
    // Buat instance NotificationService
    $notificationService = new NotificationService();
    
    // Tes notifikasi SPPD disetujui oleh sekretaris
    $notificationService->notifySppdApproved($travelRequest, $sekretarisUser);
    echo "Notifikasi SPPD disetujui oleh sekretaris berhasil dibuat\n";
    
    // Tes notifikasi SPPD selesai
    $notificationService->notifySppdCompleted($travelRequest, $sekretarisUser);
    echo "Notifikasi SPPD selesai berhasil dibuat\n";
} else {
    echo "Gagal membuat notifikasi: Staff, Sekretaris, atau TravelRequest tidak ditemukan\n";
}