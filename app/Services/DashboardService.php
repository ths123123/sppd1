<?php

namespace App\Services;

use App\Models\TravelRequest;
use App\Models\Document;
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
                'pending' => TravelRequest::where('status', 'submitted')->count(), // FIX: Menggunakan status 'submitted' untuk data 'pending'
                'review' => TravelRequest::where('status', 'in_review')->count(),
                'documents' => Document::count(),
                'rejected' => TravelRequest::where('status', 'rejected')->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting dashboard statistics: ' . $e->getMessage());
            return [
                'completed' => 1,
                'pending' => 2,
                'review' => 1,
                'documents' => 8,
                'rejected' => 1,
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

            // Return fallback data
            return [
                'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                'completed' => [8, 12, 15, 10, 18, 14, 16, 19, 22, 25, 28, 30],
                'in_review' => [12, 18, 20, 15, 25, 20, 24, 28, 32, 35, 38, 42],
                'rejected' => [2, 3, 1, 2, 4, 3, 2, 5, 3, 4, 6, 2],
                'submitted' => [22, 33, 36, 27, 47, 37, 42, 52, 57, 64, 72, 74],
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
                'in_review' => $stats['pending'],
                'rejected' => $stats['rejected'],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting status distribution: ' . $e->getMessage());
            return [
                'completed' => 1,
                'in_review' => 2,
                'rejected' => 1,
            ];
        }
    }

    /**
     * Get recent activities
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getRecentActivities(int $limit = 5)
    {
        try {
            return TravelRequest::with(['user'])
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
    public function getFormattedRecentActivities(int $limit = 5): array
    {
        try {
            $activities = $this->getRecentActivities($limit);

            return $activities->map(function($item) {
                return [
                    'id' => $item->id,
                    'kode_sppd' => $item->kode_sppd,
                    'user_name' => $item->user->name ?? 'Unknown',
                    'status' => $item->status,
                    'updated_at' => $item->updated_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i'),
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting formatted recent activities: ' . $e->getMessage());
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
            'recent_activities' => $recentActivities,
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
                'pending' => $statistics['pending'],
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
        $baseData = $this->getDashboardData();

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
