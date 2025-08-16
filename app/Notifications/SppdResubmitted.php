<?php

namespace App\Notifications;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SppdResubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $travelRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(TravelRequest $travelRequest)
    {
        $this->travelRequest = $travelRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('SPPD Diajukan Ulang - ' . $this->travelRequest->kode_sppd)
            ->line('SPPD telah diajukan ulang dan memerlukan persetujuan Anda.')
            ->line('Kode SPPD: ' . $this->travelRequest->kode_sppd)
            ->line('Tujuan: ' . $this->travelRequest->tujuan)
            ->line('Tanggal: ' . $this->travelRequest->tanggal_berangkat . ' s/d ' . $this->travelRequest->tanggal_kembali)
            ->action('Lihat SPPD', url('/travel-requests/' . $this->travelRequest->id))
            ->line('Terima kasih atas perhatian Anda.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'travel_request_id' => $this->travelRequest->id,
            'kode_sppd' => $this->travelRequest->kode_sppd,
            'tujuan' => $this->travelRequest->tujuan,
            'tanggal_berangkat' => $this->travelRequest->tanggal_berangkat,
            'tanggal_kembali' => $this->travelRequest->tanggal_kembali,
            'message' => 'SPPD telah diajukan ulang dan memerlukan persetujuan Anda.'
        ];
    }
}