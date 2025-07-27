<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
    // Endpoint API notifikasi user (untuk navbar polling)
    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
            
        // Log untuk debugging
        \Log::debug('NotificationController::apiIndex', [
            'method' => $request->method(),
            'user_id' => $user->id,
            'unread_count' => $unreadCount,
            'notifications_count' => $notifications->count()
        ]);
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'success' => true
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-Content-Type-Options' => 'nosniff'
        ]);
    }

    public function markAllRead(Request $request)
    {
        $user = Auth::user();
        
        // Log untuk debugging
        \Log::debug('NotificationController::markAllRead', [
            'method' => $request->method(),
            'user_id' => $user->id
        ]);
        
        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        return response()->json([
            'success' => true,
            'marked_count' => $count
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-Content-Type-Options' => 'nosniff'
        ]);
    }
} 