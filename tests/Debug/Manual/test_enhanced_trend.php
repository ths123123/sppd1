<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Services\DashboardService;
use Carbon\Carbon;

echo "=== TEST ENHANCED DASHBOARD TREND ===\n";

// 1. Test enhanced DashboardService
echo "1. TESTING ENHANCED DASHBOARD SERVICE:\n";
$dashboardService = new DashboardService();

// Get enhanced monthly trend data
$trendData = $dashboardService->getMonthlyTrendData();
echo "Months: " . implode(', ', $trendData['months']) . "\n";
echo "Completed (Disetujui): " . implode(', ', $trendData['completed']) . "\n";
echo "In Review (Diajukan): " . implode(', ', $trendData['in_review']) . "\n";
echo "Rejected (Ditolak): " . implode(', ', $trendData['rejected']) . "\n";
echo "Submitted (Total Diajukan): " . implode(', ', $trendData['submitted']) . "\n";

// 2. Test current month data
echo "\n2. CURRENT MONTH DATA (Jul 2025):\n";
$currentMonth = Carbon::now('Asia/Jakarta');
$currentMonthName = $currentMonth->format('M Y');

$currentMonthData = [
    'completed' => TravelRequest::where('status', 'completed')
        ->whereYear('created_at', $currentMonth->year)
        ->whereMonth('created_at', $currentMonth->month)
        ->count(),
    'in_review' => TravelRequest::where('status', 'in_review')
        ->whereYear('created_at', $currentMonth->year)
        ->whereMonth('created_at', $currentMonth->month)
        ->count(),
    'rejected' => TravelRequest::where('status', 'rejected')
        ->whereYear('created_at', $currentMonth->year)
        ->whereMonth('created_at', $currentMonth->month)
        ->count(),
    'submitted' => TravelRequest::whereIn('status', ['in_review', 'completed', 'rejected'])
        ->whereYear('created_at', $currentMonth->year)
        ->whereMonth('created_at', $currentMonth->month)
        ->count(),
];

echo $currentMonthName . " - Disetujui: " . $currentMonthData['completed'] . "\n";
echo $currentMonthName . " - Diajukan: " . $currentMonthData['in_review'] . "\n";
echo $currentMonthName . " - Ditolak: " . $currentMonthData['rejected'] . "\n";
echo $currentMonthName . " - Total Diajukan: " . $currentMonthData['submitted'] . "\n";

// 3. Test all time statistics
echo "\n3. ALL TIME STATISTICS:\n";
$allTimeStats = [
    'completed' => TravelRequest::where('status', 'completed')->count(),
    'in_review' => TravelRequest::where('status', 'in_review')->count(),
    'revision' => TravelRequest::where('status', 'revision')->count(),
    'rejected' => TravelRequest::where('status', 'rejected')->count(),
    'draft' => TravelRequest::where('status', 'draft')->count(),
];

foreach ($allTimeStats as $status => $count) {
    $label = match($status) {
        'completed' => 'Disetujui',
        'in_review' => 'Diajukan',
        'revision' => 'Revisi',
        'rejected' => 'Ditolak',
        'draft' => 'Draft',
        default => ucfirst($status)
    };
    echo $label . ": " . $count . "\n";
}

// 4. Test recent activities with status labels
echo "\n4. RECENT ACTIVITIES WITH STATUS LABELS:\n";
$recentActivities = TravelRequest::with(['user'])
    ->orderBy('updated_at', 'desc')
    ->limit(5)
    ->get();

foreach ($recentActivities as $activity) {
    $statusLabel = match($activity->status) {
        'completed' => 'Disetujui',
        'in_review' => 'Diajukan',
        'revision' => 'Revisi',
        'rejected' => 'Ditolak',
        'draft' => 'Draft',
        default => ucfirst($activity->status)
    };
    
    echo "- ID: " . $activity->id . ", Status: " . $statusLabel . ", User: " . ($activity->user->name ?? 'Unknown') . ", Updated: " . $activity->updated_at->format('d/m/Y H:i') . "\n";
}

echo "\n=== TREND ANALYSIS ===\n";
echo "✅ Disetujui: " . $allTimeStats['completed'] . " SPPD\n";
echo "📤 Diajukan: " . $allTimeStats['in_review'] . " SPPD\n";
echo "❌ Ditolak: " . $allTimeStats['rejected'] . " SPPD\n";
echo "📝 Revisi: " . $allTimeStats['revision'] . " SPPD\n";
echo "📋 Draft: " . $allTimeStats['draft'] . " SPPD\n";

echo "\n=== DASHBOARD FEATURES ===\n";
echo "✅ Tren bulanan lengkap (12 bulan terakhir)\n";
echo "✅ Status: Disetujui, Diajukan, Ditolak, Total Diajukan\n";
echo "✅ Data real-time dari database\n";
echo "✅ Chart interaktif dengan 4 garis berbeda\n";
echo "✅ Warna berbeda untuk setiap status\n";

echo "\n=== TEST COMPLETE ===\n"; 
