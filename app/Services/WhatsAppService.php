<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private $apiUrl;
    private $apiKey;
    private $isEnabled;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url', 'https://api.fonnte.com/send');
        $this->apiKey = config('services.whatsapp.api_key');
        $this->isEnabled = config('services.whatsapp.enabled', false);
    }

    /**
     * Send WhatsApp message
     */
    public function sendMessage(string $phone, string $message): bool
    {
        if (!$this->isEnabled || !$this->apiKey) {
            Log::info("WhatsApp disabled atau API key tidak ada. Pesan: {$message}");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->post($this->apiUrl, [
                'target' => $this->formatPhoneNumber($phone),
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp berhasil dikirim ke {$phone}");
                return true;
            } else {
                Log::error("WhatsApp gagal dikirim ke {$phone}: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SPPD status notification
     */
    public function sendSPPDNotification(string $phone, string $userName, string $status, string $sppdId, string $tujuan, ?string $notes = null): bool
    {
        $message = $this->buildSPPDMessage($userName, $status, $sppdId, $tujuan, $notes);
        return $this->sendMessage($phone, $message);
    }

    /**
     * Build SPPD notification message
     */
    private function buildSPPDMessage(string $userName, string $status, string $sppdId, string $tujuan, ?string $notes = null): string
    {
        $statusMessages = [
            'in_review' => '👀 *SPPD SEDANG DIREVIEW*',
            'revision' => '📝 *SPPD PERLU REVISI*',
            'completed' => '✅ *SPPD DISETUJUI*',
            'rejected' => '❌ *SPPD DITOLAK*',
        ];

        $statusEmoji = [
            'in_review' => '👀',
            'revision' => '📝',
            'completed' => '✅',
            'rejected' => '❌',
        ];

        $header = $statusMessages[$status] ?? "📋 *UPDATE SPPD*";
        $emoji = $statusEmoji[$status] ?? '📋';

        $message = "{$header}\n\n";
        $message .= "Halo *{$userName}*,\n\n";
        $message .= "{$emoji} *SPPD #{$sppdId}*\n";
        $message .= "🎯 *Tujuan:* {$tujuan}\n";
        $message .= "📅 *Status:* " . ucfirst($status) . "\n";

        if ($notes) {
            $message .= "💬 *Catatan:* {$notes}\n";
        }

        $message .= "\n📱 Silakan buka aplikasi SPPD untuk detail lengkap.\n";
        $message .= "🔗 " . config('app.url') . "\n\n";
        $message .= "_Sistem SPPD KPU Kabupaten Cirebon_";

        return $message;
    }

    /**
     * Format phone number for WhatsApp
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove any non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If starts with +62, remove +
        if (substr($phone, 0, 3) === '+62') {
            $phone = substr($phone, 1);
        }

        // If doesn't start with 62, assume it's Indonesian number
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Test WhatsApp connection
     */
    public function test(string $phone): bool
    {
        $message = "🧪 *TEST NOTIFIKASI*\n\n";
        $message .= "Halo! Ini adalah pesan test dari Sistem SPPD KPU Kabupaten Cirebon.\n\n";
        $message .= "✅ Notifikasi WhatsApp berhasil dikonfigurasi!\n\n";
        $message .= "_Pesan ini dikirim pada " . now()->format('d/m/Y H:i') . "_";

        return $this->sendMessage($phone, $message);
    }
}
