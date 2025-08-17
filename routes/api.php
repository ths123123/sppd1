<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes for SPPD System
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Profile API
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show']);
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update']);

    // Travel Request API
    Route::apiResource('travel-requests', App\Http\Controllers\TravelRequestController::class);

    // Admin only routes
    Route::middleware('admin')->group(function () {
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Admin dashboard']);
        });
    });
});

// Notifications API (using web auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'apiIndex']);
    Route::post('/notifications', [\App\Http\Controllers\NotificationController::class, 'apiIndex']); // Tambahkan dukungan untuk POST
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead']);

    Route::get('/notifications/count', function (Request $request) {
        $user = Auth::user();
        $count = \App\Models\Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    });

    // Error logging endpoint
    Route::post('/log-error', function (Request $request) {
        Log::error('Frontend error: ' . ($request->type ?? 'unknown'), [
            'user_id' => Auth::id(),
            'message' => $request->message,
            'details' => $request->all()
        ]);

        return response()->json(['success' => true]);
    });
});

// Additional API endpoints for dashboard functionality
Route::middleware(['auth'])->group(function () {
    // Activity logs API
    Route::get('/activity-logs', function (Request $request) {
        $user = Auth::user();
        $activities = \App\Models\ActivityLog::where('user_id', $user->id)
            ->orWhere('target_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    });

    // Travel requests API (alias for compatibility)
    Route::get('/travel-requests', function (Request $request) {
        $user = Auth::user();
        $requests = \App\Models\TravelRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    });

    // Activities API (alias for compatibility)
    Route::get('/activities', function (Request $request) {
        $user = Auth::user();
        $activities = \App\Models\ActivityLog::where('user_id', $user->id)
            ->orWhere('target_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    });

    // SPPD API (alias for compatibility)
    Route::get('/sppd', function (Request $request) {
        $user = Auth::user();
        $requests = \App\Models\TravelRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    });
});
