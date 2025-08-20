<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Notification;

// Cari user PPK yang aktif
$ppkUser = User::where('role', 'ppk')
    ->where('is_active', 1)
    ->first();

// Ambil travel request pertama untuk contoh
$travelRequest = TravelRequest::first();

if ($ppkUser && $travelRequest) {
    // Buat notifikasi baru
    $notification = new Notification();
    $notification->user_id = $ppkUser->id;
    $notification->travel_request_id = $travelRequest->id;
    $notification->title = 'SPPD Menunggu Persetujuan Anda';
    $notification->message = "SPPD {$travelRequest->kode_sppd} dari {$travelRequest->user->name} telah disetujui oleh Sekretaris dan menunggu persetujuan Anda sebagai PPK. Tujuan: {$travelRequest->tujuan}";
    $notification->type = 'sppd_approved_by_sekretaris';
    $notification->is_read = false;
    $notification->save();
    
    echo "Notifikasi berhasil dibuat untuk PPK dengan ID: {$ppkUser->id}\n";
} else {
    echo "Gagal membuat notifikasi: PPK atau TravelRequest tidak ditemukan\n";
}