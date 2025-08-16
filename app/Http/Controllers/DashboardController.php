<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $user = Auth::user();

        // Get dashboard data based on user role
        $dashboardData = $this->dashboardService->getUserDashboardData($user->role, $user->id);

        // Ensure we have the submitted count
        $submittedCount = $dashboardData['statistics']['submitted'] ?? 0;
        
        return view('dashboard.dashboard-utama', [
            'approvedCount' => $dashboardData['statistics']['completed'],
            'submittedCount' => $submittedCount,
            'reviewCount' => $dashboardData['statistics']['review'],
            'documentCount' => $dashboardData['statistics']['documents'],
            'rejectedCount' => $dashboardData['statistics']['rejected'],
            'months' => $dashboardData['trend_data']['months'],
            'monthlyApproved' => $dashboardData['trend_data']['completed'],
            'monthlyInReview' => $dashboardData['trend_data']['in_review'],
            'monthlyRejected' => $dashboardData['trend_data']['rejected'] ?? [],
            'monthlySubmitted' => $dashboardData['trend_data']['submitted'] ?? [],
            'statusDistribution' => $dashboardData['status_distribution'],
            'recentActivities' => $dashboardData['recent_activities'],
            'lastUpdated' => $dashboardData['last_updated'],
            'pendingApprovals' => $dashboardData['pending_approvals'] ?? 0,
            'mySspdCount' => $dashboardData['my_sppd_count'] ?? 0,
            'myPendingCount' => $dashboardData['my_pending_count'] ?? 0,
        ]);
    }
    
    /**
     * Get recent activities for API
     *
     * @return JsonResponse
     */
    public function getRecentActivities(): JsonResponse
    {
        try {
            $activities = $this->dashboardService->getFormattedRecentActivities(10);
            return response()->json($activities);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memuat aktivitas terbaru'], 500);
        }
    }

    /**
     * API untuk mendapatkan data dashboard real-time
     */
    public function getRealtimeData()
    {
        try {
            $data = $this->dashboardService->getRealtimeDashboardData();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard API error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error loading dashboard data',
                'data' => $this->dashboardService->getRealtimeDashboardData() // Fallback data
            ]);
        }
    }
}
