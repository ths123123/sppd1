<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::get('/notifications/count', function (Request $request) {
        $user = auth()->user();
        $count = \App\Models\Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
        
        return response()->json(['count' => $count]);
    });
    
    // Error logging endpoint
    Route::post('/log-error', function (Request $request) {
        \Log::error('Frontend error: ' . ($request->type ?? 'unknown'), [
            'user_id' => auth()->id(),
            'message' => $request->message,
            'details' => $request->all()
        ]);
        
        return response()->json(['success' => true]);
    });
});
