<?php

namespace App\Services;

use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Support\Collection;

class ParticipantService
{
    /**
     * Get all available users for participant selection
     * Excludes admin users and ensures data consistency
     */
    public function getAvailableUsers(): Collection
    {
        return User::where('is_active', true)
            ->where('role', '!=', 'admin')
            ->orderBy('name')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'avatar_url' => $user->avatar_url,
                ];
            })->values();
    }

    /**
     * Parse and validate participants data from any format
     * This is the central method to prevent all participant-related bugs
     */
    public function parseParticipants($participants): array
    {
        if (empty($participants)) {
            return [];
        }

        $participantIds = collect();

        if (is_string($participants)) {
            $participantIds = $this->parseStringParticipants($participants);
        } elseif (is_array($participants)) {
            $participantIds = $this->parseArrayParticipants($participants);
        }

        return $this->validateAndFilterParticipants($participantIds);
    }

    /**
     * Parse participants from string format
     */
    private function parseStringParticipants(string $participants): Collection
    {
        return collect(explode(',', $participants))
            ->map(fn($id) => trim($id))
            ->filter(fn($id) => !empty($id) && is_numeric($id))
            ->map(fn($id) => (int)$id);
    }

    /**
     * Parse participants from array format
     */
    private function parseArrayParticipants(array $participants): Collection
    {
        $result = collect();

        foreach ($participants as $participant) {
            if (is_string($participant)) {
                if (str_contains($participant, ',')) {
                    // Handle comma-separated strings in array
                    $result = $result->merge($this->parseStringParticipants($participant));
                } else {
                    // Handle single string values
                    if (!empty($participant) && is_numeric($participant)) {
                        $result->push((int)$participant);
                    }
                }
            } elseif (is_numeric($participant)) {
                $result->push((int)$participant);
            }
        }

        return $result;
    }

    /**
     * Validate and filter participants
     */
    private function validateAndFilterParticipants(Collection $participantIds): array
    {
        return $participantIds
            ->unique()
            ->filter(function ($id) {
                $user = User::find($id);
                return $user && $user->role !== 'admin';
            })
            ->values()
            ->all();
    }

    /**
     * Sync participants to travel request
     * This method ensures data consistency and prevents bugs
     */
    public function syncParticipants(TravelRequest $travelRequest, $participants): void
    {
        // Debug: Log input data
        \Log::info('syncParticipants - Input data:', [
            'travel_request_id' => $travelRequest->id,
            'raw_participants' => $participants,
            'participants_type' => gettype($participants),
            'participants_is_array' => is_array($participants),
            'participants_is_null' => is_null($participants),
            'participants_is_empty' => empty($participants)
        ]);

        $participantIds = $this->parseParticipants($participants);
        
        // Debug: Log parsed data
        \Log::info('syncParticipants - Parsed data:', [
            'parsed_participant_ids' => $participantIds,
            'parsed_count' => count($participantIds)
        ]);
        
        // Always sync to ensure data consistency
        $travelRequest->participants()->sync($participantIds);
        
        // Log for debugging
        \Log::info('Participants synced', [
            'travel_request_id' => $travelRequest->id,
            'participant_ids' => $participantIds,
            'participant_count' => count($participantIds)
        ]);
    }

    /**
     * Get participants with avatar data for display
     */
    public function getParticipantsWithAvatars(TravelRequest $travelRequest): Collection
    {
        return $travelRequest->participants->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'avatar_url' => $user->avatar_url,
            ];
        });
    }

    /**
     * Check if travel request has participants
     */
    public function hasParticipants(TravelRequest $travelRequest): bool
    {
        return $travelRequest->participants->count() > 0;
    }

    /**
     * Get participant count
     */
    public function getParticipantCount(TravelRequest $travelRequest): int
    {
        return $travelRequest->participants->count();
    }

    /**
     * Format participants for display
     */
    public function formatParticipantsForDisplay(TravelRequest $travelRequest): array
    {
        if ($this->hasParticipants($travelRequest)) {
            return [
                'has_participants' => true,
                'participants' => $this->getParticipantsWithAvatars($travelRequest),
                'count' => $this->getParticipantCount($travelRequest)
            ];
        } else {
            return [
                'has_participants' => false,
                'message' => 'Tidak ada peserta tambahan - pengaju sendiri yang melakukan perjalanan dinas',
                'pengaju' => [
                    'id' => $travelRequest->user->id,
                    'name' => $travelRequest->user->name,
                    'role' => $travelRequest->user->role,
                    'avatar_url' => $travelRequest->user->avatar_url,
                ]
            ];
        }
    }
} 