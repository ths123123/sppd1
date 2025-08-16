<?php

namespace App\Services;

use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * TravelRequestService
 *
 * Handles all business logic related to Travel Request (SPPD) operations
 * Separated from controller for better testability and maintainability
 */
class TravelRequestService
{
    /**
     * Get travel requests statistics for current user
     *
     * @param int $userId
     * @return array
     */
    public function getUserStatistics(int $userId): array
    {
        return [
            'menunggu_review' => TravelRequest::where('user_id', $userId)->where('status', 'in_review')->count(),
            'disetujui' => TravelRequest::where('user_id', $userId)->where('status', 'completed')->count(),
            'semua_menunggu' => TravelRequest::where('user_id', $userId)->whereIn('status', ['in_review'])->count(),
            'total' => TravelRequest::where('user_id', $userId)->count(),
        ];
    }

    /**
     * Get travel requests for a user
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserTravelRequests(\App\Models\User $user)
    {
        return TravelRequest::where('user_id', $user->id);
    }

    /**
     * Get travel requests statistics for management
     *
     * @return array
     */
    public function getManagementStatistics(): array
    {
        return [
            'menunggu_review' => TravelRequest::whereIn('status', ['in_review'])->count(),
            'disetujui' => TravelRequest::where('status', 'completed')->count(),
            'semua_menunggu' => TravelRequest::whereIn('status', ['in_review'])->count(),
            'total' => TravelRequest::count(),
        ];
    }

    /**
     * Generate unique SPPD code
     *
     * @return string
     */
    public function generateKodeSppd(): string
    {
        $prefix = 'SPD';
        $year = date('Y');
        $month = date('m');

        // Get last number for current month/year
        $lastRequest = \App\Models\TravelRequest::where('kode_sppd', 'like', "{$prefix}/{$year}/{$month}/%")
            ->orderBy('kode_sppd', 'desc')
            ->first();

        if ($lastRequest) {
            // Extract number from last code
            $parts = explode('/', $lastRequest->kode_sppd);
            $lastNumber = (int) end($parts);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s/%s/%s/%03d', $prefix, $year, $month, $newNumber);
    }

    /**
     * Generate unique nomor surat tugas
     *
     * @return string
     */
    public function generateNomorSuratTugas(): string
    {
        $prefix = 'ST';
        $year = date('Y');

        // Get last number for current year
        $lastRequest = \App\Models\TravelRequest::where('nomor_surat_tugas', 'like', "{$prefix}-%/{$year}")
            ->orderBy('nomor_surat_tugas', 'desc')
            ->first();

        if ($lastRequest) {
            // Extract number from last nomor (format: ST-001/KPU/2025)
            $parts = explode('-', $lastRequest->nomor_surat_tugas);
            $numberPart = explode('/', $parts[1])[0];
            $lastNumber = (int) $numberPart;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%03d/KPU/%s', $prefix, $newNumber, $year);
    }

    /**
     * Calculate travel duration in days
     *
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public function calculateDuration(string $startDate, string $endDate): int
    {
        return Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
    }

    /**
     * Clean and normalize cost input (remove formatting)
     *
     * @param string|null $cost
     * @return int
     */
    public function normalizeCost(?string $cost): int
    {
        if (!$cost) return 0;
        
        // Hapus semua karakter non-digit
        $cleanValue = preg_replace('/[^\d]/', '', $cost);
        
        // Pastikan nilai tidak kosong setelah pembersihan
        if (empty($cleanValue)) {
            return 0;
        }
        
        // Konversi ke integer
        $result = (int) $cleanValue;
        
        // Log untuk debugging
        \Log::debug('TravelRequestService::normalizeCost', [
            'input' => $cost,
            'cleaned' => $cleanValue,
            'result' => $result
        ]);
        
        return $result;
    }

    /**
     * Calculate total cost from components
     *
     * @param array $costs
     * @return int
     */
    public function calculateTotalCost(array $costs): int
    {
        $transport = $this->normalizeCost($costs['biaya_transport'] ?? '0');
        $penginapan = $this->normalizeCost($costs['biaya_penginapan'] ?? '0');
        $harian = $this->normalizeCost($costs['uang_harian'] ?? '0');
        $lainnya = $this->normalizeCost($costs['biaya_lainnya'] ?? '0');
        
        $total = $transport + $penginapan + $harian + $lainnya;
        
        // Log untuk debugging
        \Log::debug('TravelRequestService::calculateTotalCost', [
            'transport' => $transport,
            'penginapan' => $penginapan,
            'harian' => $harian,
            'lainnya' => $lainnya,
            'total' => $total
        ]);
        
        return $total;
    }

    /**
     * Prepare travel request data for storage
     *
     * @param Request $request
     * @param User $currentUser
     * @return array
     */
    public function prepareTravelRequestData(Request $request, User $currentUser): array
    {
        $duration = $this->calculateDuration(
            $request->tanggal_berangkat,
            $request->tanggal_kembali
        );

        $costs = [
            'biaya_transport' => $request->biaya_transport,
            'biaya_penginapan' => $request->biaya_penginapan,
            'uang_harian' => $request->uang_harian,
            'biaya_lainnya' => $request->biaya_lainnya,
        ];

        $totalCost = $this->calculateTotalCost($costs);

        // Determine status and submission time
        $status = 'in_review';
        $submittedAt = null;
        if ($request->action === 'submit') {
            $status = 'in_review';
            $submittedAt = now();
        }

        // Determine user ID (for management creating SPPD for others)
        $userId = $request->user_id ?? $currentUser->id;

        return [
            'user_id' => $userId,
            'kode_sppd' => null,
            'tempat_berangkat' => $request->tempat_berangkat,
            'tujuan' => $request->tujuan,
            'keperluan' => $request->keperluan,
            'tanggal_berangkat' => $request->tanggal_berangkat,
            'tanggal_kembali' => $request->tanggal_kembali,
            'lama_perjalanan' => $duration,
            'transportasi' => $request->transportasi,
            'tempat_menginap' => $request->tempat_menginap,
            'biaya_transport' => $this->normalizeCost($costs['biaya_transport']),
            'biaya_penginapan' => $this->normalizeCost($costs['biaya_penginapan']),
            'uang_harian' => $this->normalizeCost($costs['uang_harian']),
            'biaya_lainnya' => $this->normalizeCost($costs['biaya_lainnya']),
            'total_biaya' => $totalCost,
            'sumber_dana' => $request->sumber_dana,
            'catatan_pemohon' => $request->catatan_pemohon,
            'is_urgent' => $request->has('is_urgent') ? 1 : 0,
            'nomor_surat_tugas' => null,
            'tanggal_surat_tugas' => null,
            'status' => $status,
            'current_approval_level' => 0,
            'submitted_at' => $submittedAt,
        ];
    }

    /**
     * Create new travel request
     *
     * @param Request $request
     * @param User $currentUser
     * @return TravelRequest
     */
    public function createTravelRequest(Request $request, User $currentUser): TravelRequest
    {
        $data = $this->prepareTravelRequestData($request, $currentUser);
        $travelRequest = TravelRequest::create($data);
        return $travelRequest;
    }

    /**
     * Update existing travel request
     *
     * @param TravelRequest $travelRequest
     * @param Request $request
     * @return TravelRequest
     */
    public function updateTravelRequest(TravelRequest $travelRequest, Request $request): TravelRequest
    {
        // Allow updates for in_review and revision status
        if (!in_array($travelRequest->status, ['in_review', 'revision'])) {
            throw new \Exception('Only in_review or revision SPPD can be updated.');
        }

        $duration = $this->calculateDuration(
            $request->tanggal_berangkat,
            $request->tanggal_kembali
        );

        $costs = [
            'biaya_transport' => $request->biaya_transport,
            'biaya_penginapan' => $request->biaya_penginapan,
            'uang_harian' => $request->uang_harian,
            'biaya_lainnya' => $request->biaya_lainnya,
        ];

        $totalCost = $this->calculateTotalCost($costs);

        // Determine status based on action
        $status = $travelRequest->status;
        $submittedAt = $travelRequest->submitted_at;
        if ($request->action === 'submit') {
            $status = 'in_review';
            $submittedAt = now();
        }

        $updateData = [
            'tujuan' => $request->tujuan,
            'keperluan' => $request->keperluan,
            'tanggal_berangkat' => $request->tanggal_berangkat,
            'tanggal_kembali' => $request->tanggal_kembali,
            'lama_perjalanan' => $duration,
            'transportasi' => $request->transportasi,
            'tempat_menginap' => $request->tempat_menginap,
            'biaya_transport' => $this->normalizeCost($costs['biaya_transport']),
            'biaya_penginapan' => $this->normalizeCost($costs['biaya_penginapan']),
            'uang_harian' => $this->normalizeCost($costs['uang_harian']),
            'biaya_lainnya' => $this->normalizeCost($costs['biaya_lainnya']),
            'total_biaya' => $totalCost,
            'sumber_dana' => $request->sumber_dana,
            'catatan_pemohon' => $request->catatan_pemohon,
            'is_urgent' => $request->has('is_urgent') ? 1 : 0,
            'status' => $status,
            'submitted_at' => $submittedAt,
        ];

        $travelRequest->update($updateData);
        return $travelRequest;
    }

    /**
     * Handle document uploads for travel request
     *
     * @param TravelRequest $travelRequest
     * @param Request $request
     * @param User $currentUser
     * @return void
     */
    public function handleDocumentUploads(TravelRequest $travelRequest, Request $request, User $currentUser): void
    {
        if (!$request->hasFile('dokumen_pendukung')) {
            return;
        }

        foreach ($request->file('dokumen_pendukung') as $file) {
            $filename = uniqid('doc_') . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/documents', $filename);

            Document::create([
                'travel_request_id' => $travelRequest->id,
                'uploaded_by' => $currentUser->id,
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'document_type' => 'supporting',
                'description' => 'Dokumen pendukung SPPD',
                'is_required' => false,
                'is_verified' => false,
            ]);
        }
    }

    /**
     * Check if user can access travel request
     *
     * @param TravelRequest $travelRequest
     * @param User $user
     * @return bool
     */
    public function canUserAccess(TravelRequest $travelRequest, User $user): bool
    {
        // Management can access all travel requests
        if (in_array($user->role, ['kasubbag', 'sekretaris', 'ppk'])) {
            return true;
        }

        // Users can only access their own travel requests
        return $travelRequest->user_id === $user->id;
    }

    /**
     * Check if user can edit travel request
     *
     * @param TravelRequest $travelRequest
     * @param User $user
     * @return bool
     */
    public function canUserEdit(TravelRequest $travelRequest, User $user): bool
    {
        // User can only edit their own SPPD
        if ($travelRequest->user_id !== $user->id) {
            return false;
        }

        // Allow edits for in_review or revision status
        return $travelRequest->status === 'in_review' || $travelRequest->status === 'revision';
    }

    /**
     * Get appropriate redirect route based on user role
     *
     * @param User $user
     * @return string
     */
    public function getIndexRoute(User $user): string
    {
        if (in_array($user->role, ['kasubbag', 'sekretaris', 'ppk'])) {
            return 'travel-requests.index';
        }

        return 'my-travel-requests.index';
    }

    /**
     * Get success message based on action
     *
     * @param string $action
     * @return string
     */
    public function getSuccessMessage(string $action): string
    {
        return 'Pengajuan SPPD berhasil diajukan. Silakan pantau status persetujuan secara berkala.';
    }

    /**
     * Apply filters to the travel request query.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterTravelRequests(array $validatedData, $query)
    {
        if (isset($validatedData['search'])) {
            $searchTerm = strtolower($validatedData['search']);
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(tujuan) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(keperluan) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"]);
                  });
            });
        }

        if (isset($validatedData['status'])) {
            $query->where('status', $validatedData['status']);
        }

        if (isset($validatedData['year'])) {
            $query->whereYear('tanggal_berangkat', $validatedData['year']);
        }

        return $query;
    }
}
