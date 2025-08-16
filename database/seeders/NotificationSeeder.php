<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus notifikasi yang ada terlebih dahulu
        Notification::truncate();
        
        // Buat notifikasi dummy untuk setiap user
        $users = User::all();
        $travelRequests = TravelRequest::all();
        
        if ($travelRequests->isEmpty()) {
            $this->command->info('Tidak ada travel request untuk membuat notifikasi dummy!');
            return;
        }
        
        Log::info('Membuat notifikasi dummy untuk ' . $users->count() . ' pengguna');
        
        foreach ($users as $user) {
            // Notifikasi SPPD diajukan
            $travelRequest = $travelRequests->random();
            Notification::create([
                'user_id' => $user->id,
                'travel_request_id' => $travelRequest->id,
                'title' => 'SPPD Diajukan',
                'message' => 'SPPD dengan nomor ' . $travelRequest->reference_number . ' telah diajukan dan menunggu persetujuan.',
                'type' => 'info',
                'data' => [
                    'travel_request_id' => $travelRequest->id,
                    'reference_number' => $travelRequest->reference_number,
                ],
                'action_url' => '/travel-requests/' . $travelRequest->id,
                'action_text' => 'Lihat Detail',
                'is_read' => false,
                'is_important' => true,
            ]);
            
            // Notifikasi SPPD disetujui
            $travelRequest = $travelRequests->random();
            Notification::create([
                'user_id' => $user->id,
                'travel_request_id' => $travelRequest->id,
                'title' => 'SPPD Disetujui',
                'message' => 'SPPD dengan nomor ' . $travelRequest->reference_number . ' telah disetujui.',
                'type' => 'success',
                'data' => [
                    'travel_request_id' => $travelRequest->id,
                    'reference_number' => $travelRequest->reference_number,
                ],
                'action_url' => '/travel-requests/' . $travelRequest->id,
                'action_text' => 'Lihat Detail',
                'is_read' => rand(0, 1) === 1,
                'is_important' => true,
            ]);
            
            // Notifikasi pengingat perjalanan dinas
            $travelRequest = $travelRequests->random();
            Notification::create([
                'user_id' => $user->id,
                'travel_request_id' => $travelRequest->id,
                'title' => 'Pengingat Perjalanan Dinas',
                'message' => 'Perjalanan dinas Anda ke ' . $travelRequest->destination . ' akan dimulai dalam 2 hari.',
                'type' => 'reminder',
                'data' => [
                    'travel_request_id' => $travelRequest->id,
                    'reference_number' => $travelRequest->reference_number,
                    'destination' => $travelRequest->destination,
                ],
                'action_url' => '/travel-requests/' . $travelRequest->id,
                'action_text' => 'Lihat Detail',
                'is_read' => false,
                'is_important' => true,
            ]);
            
            // Tambahkan notifikasi warning
            $travelRequest = $travelRequests->random();
            Notification::create([
                'user_id' => $user->id,
                'travel_request_id' => $travelRequest->id,
                'title' => 'Dokumen Belum Lengkap',
                'message' => 'SPPD dengan nomor ' . $travelRequest->reference_number . ' memiliki dokumen yang belum lengkap.',
                'type' => 'warning',
                'data' => [
                    'travel_request_id' => $travelRequest->id,
                    'reference_number' => $travelRequest->reference_number,
                ],
                'action_url' => '/travel-requests/' . $travelRequest->id,
                'action_text' => 'Lengkapi Dokumen',
                'is_read' => false,
                'is_important' => true,
            ]);
            
            // Tambahkan notifikasi error
            $travelRequest = $travelRequests->random();
            Notification::create([
                'user_id' => $user->id,
                'travel_request_id' => $travelRequest->id,
                'title' => 'SPPD Ditolak',
                'message' => 'SPPD dengan nomor ' . $travelRequest->reference_number . ' telah ditolak.',
                'type' => 'error',
                'data' => [
                    'travel_request_id' => $travelRequest->id,
                    'reference_number' => $travelRequest->reference_number,
                ],
                'action_url' => '/travel-requests/' . $travelRequest->id,
                'action_text' => 'Lihat Detail',
                'is_read' => false,
                'is_important' => true,
            ]);
        }
        
        Log::info('Berhasil membuat ' . Notification::count() . ' notifikasi dummy');
        $this->command->info('Berhasil membuat notifikasi dummy!');
    }
}