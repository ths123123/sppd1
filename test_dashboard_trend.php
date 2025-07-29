<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Services\DashboardService;
use Carbon\Carbon;

echo "=== TEST DASHBOARD TREND DATA ===\n";

// 1. Test DashboardService
echo "1. TESTING DASHBOARD SERVICE:\n";
$dashboardService = new DashboardService();

// Get monthly trend data
$trendData = $dashboardService->getMonthlyTrendData();
echo "Months: " . implode(', ', $trendData['months']) . "\n";
echo "Completed: " . implode(', ', $trendData['completed']) . "\n";
echo "In Review: " . implode(', ', $trendData['in_review']) . "\n";

// 2. Test direct database queries
echo "\n2. TESTING DIRECT DATABASE QUERIES:\n";
$currentMonth = Carbon::now('Asia/Jakarta');
echo "Current Month: " . $currentMonth->format('M Y') . "\n";

// Check data for current month
$currentMonthCompleted = TravelRequest::where('status', 'completed')
    ->whereYear('created_at', $currentMonth->year)
    ->whereMonth('created_at', $currentMonth->month)
    ->count();

$currentMonthInReview = TravelRequest::whereIn('status', ['in_review'])
    ->whereYear('created_at', $currentMonth->year)
    ->whereMonth('created_at', $currentMonth->month)
    ->count();

echo "Current Month Completed: " . $currentMonthCompleted . "\n";
echo "Current Month In Review: " . $currentMonthInReview . "\n";

// 3. Test all status counts
echo "\n3. TESTING ALL STATUS COUNTS:\n";
$statusCounts = [
    'completed' => TravelRequest::where('status', 'completed')->count(),
    'in_review' => TravelRequest::where('status', 'in_review')->count(),
    'revision' => TravelRequest::where('status', 'revision')->count(),
    'rejected' => TravelRequest::where('status', 'rejected')->count(),
    'draft' => TravelRequest::where('status', 'draft')->count(),
];

foreach ($statusCounts as $status => $count) {
    echo ucfirst($status) . ": " . $count . "\n";
}

// 4. Test monthly data for last 3 months
echo "\n4. TESTING MONTHLY DATA (Last 3 months):\n";
for ($i = 2; $i >= 0; $i--) {
    $month = $currentMonth->copy()->subMonths($i);
    $monthName = $month->format('M Y');
    
    $completed = TravelRequest::where('status', 'completed')
        ->whereYear('created_at', $month->year)
        ->whereMonth('created_at', $month->month)
        ->count();
    
    $inReview = TravelRequest::whereIn('status', ['in_review'])
        ->whereYear('created_at', $month->year)
        ->whereMonth('created_at', $month->month)
        ->count();
    
    echo $monthName . " - Completed: " . $completed . ", In Review: " . $inReview . "\n";
}

// 5. Test recent activities
echo "\n5. TESTING RECENT ACTIVITIES:\n";
$recentActivities = TravelRequest::with(['user'])
    ->orderBy('updated_at', 'desc')
    ->limit(5)
    ->get();

foreach ($recentActivities as $activity) {
    echo "- ID: " . $activity->id . ", Status: " . $activity->status . ", User: " . ($activity->user->name ?? 'Unknown') . ", Updated: " . $activity->updated_at->format('d/m/Y H:i') . "\n";
}

echo "\n=== ANALYSIS ===\n";
echo "If all counts are > 0, the dashboard trend data is working correctly.\n";
echo "If counts are 0, there might be no data in the database.\n";

echo "\n=== TEST COMPLETE ===\n"; 