<?php

namespace App\Services;

use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * DashboardService
 *
 * Handles all business logic related to dashboard data
 * Provides statistics, trends, and activity data for dashboard views
 */
class DashboardService
{
    /**
     * Get main dashboard statistics
     *
     * @return array
     */
    public function getDashboardStatistics(): array
    {
        try {
            return [
                'completed' => TravelRequest::where('status', 'completed')->count(),
                'submitted' => TravelRequest::where('status', 'submitted')->count(),
                'review' => TravelRequest::where('status', 'in_review')->count(),
                'documents' => Document::count(),
                'rejected' => TravelRequest::where('status', 'rejected')->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting dashboard statistics: ' . $e->getMessage());
            // Return zeroed data on error instead of fake data
            return [
                'completed' => 0,
                'submitted' => 0,
                'review' => 0,
                'documents' => 0,
                'rejected' => 0,
            ];
        }
    }

    /**
     * Get monthly trend data for the last 12 months
     *
     * @return array
     */
    public function getMonthlyTrendData(): array
    {
        try {
            Carbon::setLocale('id');

            $monthlyCompleted = [];
            $monthlyInReview = [];
            $monthlyRejected = [];
            $monthlySubmitted = [];
            $months = [];

            for ($i = 11; $i >= 0; $i--) {
                $month = now('Asia/Jakarta')->subMonths($i);
                $months[] = $month->format('M Y');

                // Completed (Disetujui)
                $monthlyCompleted[] = TravelRequest::where('status', 'completed')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();

                // In Review (Diajukan)
                $monthlyInReview[] = TravelRequest::whereIn('status', ['in_review'])
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();

                // Rejected (Ditolak)
                $monthlyRejected[] = TravelRequest::where('status', 'rejected')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();

                // Submitted (Diajukan - termasuk draft yang sudah diajukan)
                $monthlySubmitted[] = TravelRequest::whereIn('status', ['in_review', 'completed', 'rejected'])
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
            }

            return [
                'months' => $months,
                'completed' => $monthlyCompleted,
                'in_review' => $monthlyInReview,
                'rejected' => $monthlyRejected,
                'submitted' => $monthlySubmitted,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting monthly trend data: ' . $e->getMessage());

            // Return empty data on error
            return [
                'months' => [],
                'completed' => [],
                'in_review' => [],
                'rejected' => [],
                'submitted' => [],
            ];
        }
    }

    /**
     * Get status distribution data (group by status, sama seperti analytics)
     *
     * @return array
     */
    public function getStatusDistribution(): array
    {
        try {
            $stats = $this->getDashboardStatistics();

            return [
                'completed' => $stats['completed'],
                'in_review' => $stats['review'],
                'rejected' => $stats['rejected'],
                'submitted' => $stats['submitted'],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting status distribution: ' . $e->getMessage());
            // Return zeroed data on error
            return [
                'completed' => 0,
                'in_review' => 0,
                'rejected' => 0,
                'submitted' => 0,
            ];
        }
    }

    /**
     * Get recent activities
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getRecentActivities(int $limit = 10)
    {
        try {
            $activityLogs = app(ActivityLogService::class)->getRecentActivities($limit);

            if ($activityLogs->isNotEmpty()) {
                return $activityLogs;
            }

            // Fallback to TravelRequest if no activity logs are found
            return TravelRequest::with('user', 'approvals.user')
                ->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get();

        } catch (\Exception $e) {
            Log::error('Error getting recent activities: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get formatted recent activities for API response
     *
     * @param int $limit
     * @return array
     */
    public function getFormattedRecentActivities(int $limit = 10): array
    {
        try {
            $activities = $this->getRecentActivities($limit);

            if ($activities->isEmpty()) {
                return [];
            }

            return $activities->map(function ($item) {
                if (!$item instanceof ActivityLog) {
                    return null; // Lewati jika bukan ActivityLog
                }

                $createdAt = $item->created_at->setTimezone('Asia/Jakarta');
                $timeAgo = $createdAt->isToday()
                    ? 'Hari ini ' . $createdAt->format('H:i')
                    : ($createdAt->isYesterday()
                        ? 'Kemarin ' . $createdAt->format('H:i')
                        : $createdAt->diffForHumans());

                $details = $item->details ?? [];
                $modelType = $item->model_type ?? '';
                $modelId = $item->model_id ?? null;
                $kodeSppd = $details['kode_sppd'] ?? null;
                $travelRequest = null;

                if ($modelType == 'App\Models\TravelRequest' && $modelId) {
                    $travelRequest = \App\Models\TravelRequest::with('user', 'approvals.user')->find($modelId);
                    if ($travelRequest && !$kodeSppd) {
                        $kodeSppd = $travelRequest->kode_sppd;
                    }
                }

                $status = 'info';
                if (str_contains($item->action, 'create') || str_contains($item->action, 'submit') || str_contains($item->action, 'Dibuat') || str_contains($item->action, 'Diajukan')) {
                    $status = 'submitted';
                } elseif (str_contains($item->action, 'approve') || str_contains($item->action, 'complete') || str_contains($item->action, 'Disetujui')) {
                    $status = 'completed';
                } elseif (str_contains($item->action, 'reject') || str_contains($item->action, 'Ditolak')) {
                    $status = 'rejected';
                } elseif (str_contains($item->action, 'review') || str_contains($item->action, 'Dalam Review')) {
                    $status = 'in_review';
                } elseif (str_contains($item->action, 'revise') || str_contains($item->action, 'Revisi')) {
                    $status = 'revision';
                }

                $approverName = $details['approver_name'] ?? null;
                $approverRole = $details['approver_role'] ?? null;

                if ($travelRequest && $travelRequest->approvals->isNotEmpty()) {
                    $sekretarisApproval = $travelRequest->approvals->firstWhere('user.role', 'sekretaris');
                    $ppkApproval = $travelRequest->approvals->firstWhere('user.role', 'ppk');

                    if ($status === 'completed' && $sekretarisApproval && $ppkApproval) {
                        $approverName = sprintf(
                            '%s (Sekretaris) & %s (PPK)',
                            $sekretarisApproval->user->name,
                            $ppkApproval->user->name
                        );
                    } elseif ($approverName === null) {
                        $lastApproval = $travelRequest->approvals->last();
                        if ($lastApproval && $lastApproval->user) {
                            $approverName = $lastApproval->user->name;
                            $approverRole = $lastApproval->user->role;
                        }
                    }
                }

                $userName = $item->user->name ?? ($travelRequest->user->name ?? 'Sistem');
                $description = $details['description'] ?? $item->action;

                if ($travelRequest) {
                    switch ($status) {
                        case 'submitted':
                            $description = "📋 SPPD {$kodeSppd} telah berhasil diajukan untuk tujuan {$travelRequest->tujuan} oleh {$userName}.";
                            break;
                        case 'completed':
                            $description = "✅ SPPD {$kodeSppd} telah memperoleh persetujuan penuh dan siap untuk eksekusi perjalanan dinas.";
                            break;
                        case 'rejected':
                            $description = "❌ SPPD {$kodeSppd} tidak dapat diproses dan telah ditolak oleh {$approverName}.";
                            break;
                        case 'revision':
                            $description = "🔄 SPPD {$kodeSppd} memerlukan perbaikan berdasarkan evaluasi dari {$approverName}.";
                            break;
                        case 'in_review':
                            $description = "⏳ SPPD {$kodeSppd} sedang dalam tahap peninjauan dan evaluasi oleh tim yang berwenang.";
                            break;
                    }
                } else {
                    // If no travel request found, use the description from activity log
                    $description = $details['description'] ?? $item->action;
                }

                return [
                    'id' => $item->id,
                    'kode_sppd' => $kodeSppd,
                    'user_name' => $userName,
                    'user_role' => $item->user->role ?? ($travelRequest->user->role ?? 'system'),
                    'status' => $status,
                    'description' => $description,
                    'tujuan' => $travelRequest->tujuan ?? $details['tujuan'] ?? null,
                    'approver_name' => $approverName,
                    'approver_role' => $approverRole,
                    'updated_at' => $createdAt->format('d/m/Y H:i:s'),
                    'time_ago' => $timeAgo,
                    'updated_at_diff' => $createdAt->diffForHumans(),
                ];
            })->filter()->values()->toArray(); // filter() untuk menghapus null
        } catch (\Exception $e) {
            Log::error('Error getting formatted recent activities: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return [];
        }
    }

    /**
     * Get complete dashboard data
     *
     * @return array
     */
    public function getDashboardData(): array
    {
        $statistics = $this->getDashboardStatistics();
        $trendData = $this->getMonthlyTrendData();
        $statusDistribution = $this->getStatusDistribution();
        $recentActivities = $this->getRecentActivities();

        return [
            'statistics' => $statistics,
            'trend_data' => $trendData,
            'status_distribution' => $statusDistribution,
            'recent_activities'   => $recentActivities ? $recentActivities->toArray() : [],
            'last_updated' => now('Asia/Jakarta')->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Get realtime dashboard data for API
     *
     * @return array
     */
    public function getRealtimeDashboardData(): array
    {
        $statistics = $this->getDashboardStatistics();
        $trendData = $this->getMonthlyTrendData();
        $statusDistribution = $this->getStatusDistribution();
        $recentActivities = $this->getFormattedRecentActivities();

        return [
            'statistics' => [
                'completed' => $statistics['completed'],
                'submitted' => $statistics['submitted'],
                'review' => $statistics['review'],
                'documents' => $statistics['documents'],
                'rejected' => $statistics['rejected'],
            ],
            'monthly_trend' => $trendData,
            'status_distribution' => $statusDistribution,
            'recent_activities' => $recentActivities,
            'last_updated' => now('Asia/Jakarta')->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Get user-specific dashboard data based on role
     *
     * @param string $userRole
     * @param int $userId
     * @return array
     */
    public function getUserDashboardData(string $userRole, int $userId): array
    {
        // Dapatkan data dasar dashboard
        $statistics = $this->getDashboardStatistics();
        $trendData = $this->getMonthlyTrendData();
        $statusDistribution = $this->getStatusDistribution();

        // Dapatkan aktivitas terbaru yang sudah diformat
        $recentActivities = $this->getFormattedRecentActivities(10);

        // Data dasar untuk semua pengguna
        $baseData = [
            'statistics' => $statistics,
            'trend_data' => $trendData,
            'status_distribution' => $statusDistribution,
            'recent_activities' => $recentActivities,
            'last_updated' => now('Asia/Jakarta')->format('d/m/Y H:i:s'),
        ];

        // Add role-specific data
        switch ($userRole) {
            case 'kasubbag':
            case 'sekretaris':
            case 'ppk':
                // For managers, add pending approval count
                $pendingRequests = TravelRequest::where('status', 'in_review')
                    ->where('current_approval_level', '>', 0)
                    ->with('user')
                    ->get()
                    ->filter(function($request) use ($userRole) {
                        return $request->current_approver_role === $userRole;
                    });
                $baseData['pending_approvals'] = $pendingRequests->count();
                break;

            default:
                // For regular users, add their own SPPD count
                $baseData['my_sppd_count'] = TravelRequest::where('user_id', $userId)->count();
                $baseData['my_pending_count'] = TravelRequest::where('user_id', $userId)
                    ->whereIn('status', ['in_review'])
                    ->count();
                break;
        }

        return $baseData;
    }
}
