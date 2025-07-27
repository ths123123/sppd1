<?php

namespace App\Listeners;

use App\Events\SPPDStatusChanged;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWhatsAppNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $whatsappService;

    /**
     * Create the event listener.
     */
    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Handle the event.
     */
    public function handle(SPPDStatusChanged $event): void
    {
        $travelRequest = $event->travelRequest;
        $user = $travelRequest->user;

        // Skip jika user tidak punya nomor HP
        if (!$user->phone) {
            return;
        }

        // Format nomor HP untuk WhatsApp
        $phone = $user->whatsapp_number;
        if (!$phone) {
            return;
        }

        // Kirim notifikasi WhatsApp
        $this->whatsappService->sendSPPDNotification(
            $phone,
            $user->name,
            $event->newStatus,
            $travelRequest->id,
            $travelRequest->tujuan,
            $event->notes
        );

        // Jika ada approval terkait, kirim notifikasi ke approver juga
        if (in_array($event->newStatus, ['in_review'])) {
            $this->notifyApprovers($travelRequest, $event->newStatus);
        }
    }

    /**
     * Notify approvers when SPPD needs approval
     */
    private function notifyApprovers($travelRequest, $status): void
    {
        // Hanya kasubbag yang bisa mengajukan SPPD
        // Alur: kasubbag -> sekretaris -> ppk
        if ($travelRequest->user->role === 'kasubbag') {
            // Notifikasi ke sekretaris (level 1)
            if ($travelRequest->current_approval_level === 1) {
                $sekretaris = \App\Models\User::where('role', 'sekretaris')->get();
                foreach ($sekretaris as $approver) {
                    if ($approver->whatsapp_number) {
                        $this->whatsappService->sendApproverNotification(
                            $approver->whatsapp_number,
                            $approver->name,
                            $travelRequest->id,
                            $travelRequest->tujuan
                        );
                    }
                }
            } 
            // Notifikasi ke ppk (level 2)
            elseif ($travelRequest->current_approval_level === 2) {
                $ppk = \App\Models\User::where('role', 'ppk')->get();
                foreach ($ppk as $approver) {
                    if ($approver->whatsapp_number) {
                        $this->whatsappService->sendApproverNotification(
                            $approver->whatsapp_number,
                            $approver->name,
                            $travelRequest->id,
                            $travelRequest->tujuan
                        );
                    }
                }
            }
        }
    }
}
