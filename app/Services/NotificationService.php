<?php

namespace App\Services;

use App\Models\TravelRequest;
use App\Models\Notification;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

/**
 * NotificationService
 *
 * Handles all notification logic for SPPD system
 * Manages database notifications and WhatsApp notifications
 */
class NotificationService
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Create notification in database
     *
     * @param User $user
     * @param TravelRequest $travelRequest
     * @param string $type
     * @param string $title
     * @param string $message
     * @return Notification
     */
    public function createNotification(User $user, TravelRequest $travelRequest, string $type, string $title, string $message): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'travel_request_id' => $travelRequest->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }

    /**
     * Notify when SPPD is submitted for approval
     *
     * @param TravelRequest $travelRequest
     * @return void
     */
    public function notifySppdSubmitted(TravelRequest $travelRequest): void
    {
        try {
            // Notify approver
            if ($travelRequest->current_approver_role) {
                $approvers = User::where('role', $travelRequest->current_approver_role)
                    ->where('is_active', 1)
                    ->get();

                foreach ($approvers as $approver) {
                    $title = "SPPD Baru Perlu Persetujuan";
                    $message = "SPPD {$travelRequest->kode_sppd} dari {$travelRequest->user->name} perlu persetujuan Anda. Tujuan: {$travelRequest->tujuan}";

                    // Database notification
                    $this->createNotification($approver, $travelRequest, 'sppd_submitted', $title, $message);

                    // WhatsApp notification (if phone number exists)
                    $this->sendWhatsAppNotification($approver, $title, $message);
                }
            }

            // Notify submitter (confirmation)
            $submitter = $travelRequest->user;
            $title = "SPPD Berhasil Diajukan";
            $message = "SPPD Anda ({$travelRequest->kode_sppd}) berhasil diajukan dan sedang menunggu persetujuan.";

            $this->createNotification($submitter, $travelRequest, 'sppd_submitted_confirmation', $title, $message);

        } catch (\Exception $e) {
            Log::error('Failed to send SPPD submitted notification: ' . $e->getMessage());
        }
    }

    /**
     * Notify when SPPD is completed
     */
    public function notifySppdCompleted(TravelRequest $travelRequest, User $approver): void
    {
        try {
            if ($travelRequest->status === 'completed') {
                $submitter = $travelRequest->user;
                $title = 'SPPD Selesai';
                $message = 'SPPD Anda telah selesai disetujui oleh semua pihak.';
                $this->createNotification($submitter, $travelRequest, 'sppd_completed', $title, $message);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send SPPD completed notification: ' . $e->getMessage());
        }
    }

    /**
     * Notify when SPPD is rejected
     *
     * @param TravelRequest $travelRequest
     * @param User $approver
     * @param string $reason
     * @return void
     */
    public function notifySppdRejected(TravelRequest $travelRequest, User $approver, string $reason): void
    {
        try {
            $submitter = $travelRequest->user;
            $title = "SPPD Ditolak";
            $message = "SPPD Anda ({$travelRequest->kode_sppd}) ditolak oleh {$approver->role}. Alasan: {$reason}";

            // Database notification
            $this->createNotification($submitter, $travelRequest, 'sppd_rejected', $title, $message);

            // WhatsApp notification
            $this->sendWhatsAppNotification($submitter, $title, $message);

        } catch (\Exception $e) {
            Log::error('Failed to send SPPD rejected notification: ' . $e->getMessage());
        }
    }

    /**
     * Notify when SPPD needs revision
     *
     * @param TravelRequest $travelRequest
     * @param User $approver
     * @param string $reason
     * @param string $target
     * @return void
     */
    public function notifySppdRevision(TravelRequest $travelRequest, User $approver, string $reason, string $target): void
    {
        try {
            $submitter = $travelRequest->user;
            $title = "SPPD Perlu Revisi";
            $message = "SPPD Anda ({$travelRequest->kode_sppd}) perlu direvisi. Catatan dari {$approver->role}: {$reason}";

            // Database notification
            $this->createNotification($submitter, $travelRequest, 'sppd_revision', $title, $message);

            // WhatsApp notification
            $this->sendWhatsAppNotification($submitter, $title, $message);

        } catch (\Exception $e) {
            Log::error('Failed to send SPPD revision notification: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification
     *
     * @param User $user
     * @param string $title
     * @param string $message
     * @return void
     */
    protected function sendWhatsAppNotification(User $user, string $title, string $message): void
    {
        try {
            // Check if user has phone number
            if (empty($user->phone) && empty($user->no_hp)) {
                return;
            }

            $phone = $user->phone ?? $user->no_hp;
            $fullMessage = "*{$title}*\n\n{$message}\n\n_Sistem SPPD KPU Kabupaten Cirebon_";

            $this->whatsAppService->sendMessage($phone, $fullMessage);

        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification: ' . $e->getMessage());
        }
    }

    /**
     * Get unread notifications for user
     *
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnreadNotifications(User $user, int $limit = 10)
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->with('travelRequest')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all notifications for user
     *
     * @param User $user
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserNotifications(User $user, int $perPage = 15)
    {
        return Notification::where('user_id', $user->id)
            ->with('travelRequest')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mark notification as read
     *
     * @param int $notificationId
     * @param User $user
     * @return bool
     */
    public function markAsRead(int $notificationId, User $user): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $user->id)
            ->first();

        if ($notification) {
            $notification->update(['is_read' => true]);
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for user
     *
     * @param User $user
     * @return int Number of notifications marked as read
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Get notification count for user
     *
     * @param User $user
     * @return array
     */
    public function getNotificationCounts(User $user): array
    {
        return [
            'unread' => Notification::where('user_id', $user->id)->where('is_read', false)->count(),
            'total' => Notification::where('user_id', $user->id)->count(),
        ];
    }

    /**
     * Send reminder for pending approvals
     *
     * @param string $role
     * @return void
     */
    public function sendApprovalReminders(string $role): void
    {
        try {
            $pendingCount = TravelRequest::where('status', 'in_review')
                ->where('current_approver_role', $role)
                ->count();

            if ($pendingCount === 0) {
                return;
            }

            $approvers = User::where('role', $role)
                ->where('is_active', 1)
                ->get();

            foreach ($approvers as $approver) {
                $title = "Pengingat Persetujuan SPPD";
                $message = "Anda memiliki {$pendingCount} pengajuan SPPD yang menunggu persetujuan. Silakan periksa sistem untuk tindak lanjut.";

                // Database notification
                $dummyTravelRequest = new TravelRequest(); // For notification structure
                $this->createNotification($approver, $dummyTravelRequest, 'approval_reminder', $title, $message);

                // WhatsApp notification
                $this->sendWhatsAppNotification($approver, $title, $message);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send approval reminders: ' . $e->getMessage());
        }
    }

    /**
     * Clean old notifications (older than specified days)
     *
     * @param int $days
     * @return int Number of notifications deleted
     */
    public function cleanOldNotifications(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
