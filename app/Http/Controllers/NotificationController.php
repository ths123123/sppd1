<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;

class NotificationController extends Controller
{
    // Halaman daftar notifikasi
    public function index()
    {
        $user = Auth::user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(15);
            
        // Tandai semua notifikasi sebagai telah dibaca
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        return view('notifications.index', compact('notifications'));
    }
    // Endpoint API notifikasi user (untuk navbar polling)
    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        
        // Log awal untuk debugging
        Log::info('NotificationController::apiIndex - start', [
            'method' => $request->method(),
            'user_id' => $user->id
        ]);
        
        try {
            // Ambil notifikasi terbaru
            $notifications = Notification::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
                
            // Hitung notifikasi yang belum dibaca
            $unreadCount = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();
                
            // Log untuk debugging
            Log::info('NotificationController::apiIndex - success', [
                'method' => $request->method(),
                'user_id' => $user->id,
                'unread_count' => $unreadCount,
                'notifications_count' => $notifications->count(),
                'notifications' => $notifications->toArray()
            ]);
            
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
                'success' => true
            ], 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'X-Content-Type-Options' => 'nosniff'
            ]);
        } catch (\Exception $e) {
            Log::error('NotificationController::apiIndex - error', [
                'method' => $request->method(),
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'notifications' => [],
                'unread_count' => 0,
                'success' => false,
                'error' => 'Terjadi kesalahan saat mengambil notifikasi'
            ], 500, [
                'Content-Type' => 'application/json; charset=utf-8',
                'X-Content-Type-Options' => 'nosniff'
            ]);
        }
    }

    public function markAllRead(Request $request)
    {
        $user = Auth::user();
        
        // Log untuk debugging
        Log::debug('NotificationController::markAllRead', [
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