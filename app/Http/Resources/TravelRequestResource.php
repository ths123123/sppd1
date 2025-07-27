<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\TravelRequestStatus;

class TravelRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_sppd' => $this->kode_sppd,
            'status' => [
                'value' => $this->status,
                'label' => TravelRequestStatus::tryFrom($this->status)?->label() ?? $this->status,
                'badge_class' => TravelRequestStatus::tryFrom($this->status)?->badgeClass() ?? 'bg-gray-100 text-gray-800',
                'is_final' => TravelRequestStatus::tryFrom($this->status)?->isFinal() ?? false,
                'is_editable' => TravelRequestStatus::tryFrom($this->status)?->isEditable() ?? false,
            ],
            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
                'role' => $this->user->role ?? null,
            ],
            'travel_info' => [
                'tempat_berangkat' => $this->tempat_berangkat,
                'tujuan' => $this->tujuan,
                'keperluan' => $this->keperluan,
                'tanggal_berangkat' => $this->tanggal_berangkat?->format('Y-m-d'),
                'tanggal_kembali' => $this->tanggal_kembali?->format('Y-m-d'),
                'transportasi' => $this->transportasi,
                'tempat_menginap' => $this->tempat_menginap,
            ],
            'budget' => [
                'biaya_transport' => (float)($this->biaya_transport ?? 0),
                'biaya_penginapan' => (float)($this->biaya_penginapan ?? 0),
                'uang_harian' => (float)($this->uang_harian ?? 0),
                'biaya_lainnya' => (float)($this->biaya_lainnya ?? 0),
                'total_biaya' => $this->calculateTotalBudget(),
                'sumber_dana' => $this->sumber_dana,
            ],
            'participants' => $this->whenLoaded('participants', function () {
                return $this->participants->map(function ($participant) {
                    return [
                        'id' => $participant->id,
                        'name' => $participant->name,
                        'role' => $participant->role,
                        'nip' => $participant->nip,
                    ];
                });
            }),
            'approvals' => $this->whenLoaded('approvals', function () {
                return $this->approvals->map(function ($approval) {
                    return [
                        'id' => $approval->id,
                        'status' => $approval->status,
                        'approver' => [
                            'id' => $approval->approver->id ?? null,
                            'name' => $approval->approver->name ?? null,
                            'role' => $approval->approver->role ?? null,
                        ],
                        'created_at' => $approval->created_at?->format('Y-m-d H:i:s'),
                    ];
                });
            }),
            'metadata' => [
                'is_urgent' => (bool)$this->is_urgent,
                'catatan_pemohon' => $this->catatan_pemohon,
                'current_approval_level' => $this->current_approval_level,
                'submitted_at' => $this->submitted_at?->format('Y-m-d H:i:s'),
                'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
                'created_at' => $this->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            ],
            'permissions' => [
                'can_edit' => $this->canEdit(),
                'can_delete' => $this->canDelete(),
                'can_submit' => $this->canSubmit(),
                'can_export' => $this->canExport(),
            ],
        ];
    }

    /**
     * Calculate total budget
     */
    private function calculateTotalBudget(): float
    {
        return ($this->biaya_transport ?? 0) + 
               ($this->biaya_penginapan ?? 0) + 
               ($this->uang_harian ?? 0) + 
               ($this->biaya_lainnya ?? 0);
    }

    /**
     * Check if user can edit this travel request
     */
    private function canEdit(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Only owner can edit
        if ($this->user_id !== $user->id) return false;

        // Only editable statuses
        return TravelRequestStatus::tryFrom($this->status)?->isEditable() ?? false;
    }

    /**
     * Check if user can delete this travel request
     */
    private function canDelete(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Only owner can delete
        if ($this->user_id !== $user->id) return false;

        // Only in_review status can be deleted
        return $this->status === TravelRequestStatus::IN_REVIEW->value;
    }

    /**
     * Check if user can submit this travel request
     */
    private function canSubmit(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Only kasubbag can submit
        if ($user->role !== 'kasubbag') return false;

        // Only owner can submit
        if ($this->user_id !== $user->id) return false;

        // Only in_review or revision status can be submitted
        return in_array($this->status, [
            TravelRequestStatus::IN_REVIEW->value,
            TravelRequestStatus::REVISION->value
        ]);
    }

    /**
     * Check if user can export this travel request
     */
    private function canExport(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Only completed status can be exported
        if ($this->status !== TravelRequestStatus::COMPLETED->value) return false;

        // User must have access to this travel request
        return $this->user_id === $user->id || 
               $this->participants->contains('id', $user->id) ||
               in_array($user->role, ['kasubbag', 'sekretaris', 'ppk', 'admin']);
    }
} 