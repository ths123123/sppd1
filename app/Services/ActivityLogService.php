<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    /**
     * Log an activity
     *
     * @param string $action The action performed
     * @param string|null $modelType The type of model affected
     * @param int|null $modelId The ID of the model affected
     * @param array|null $details Additional details about the action
     * @param User|null $user The user who performed the action
     * @return ActivityLog
     */
    public function log(string $action, ?string $modelType = null, ?int $modelId = null, ?array $details = null, ?User $user = null): ActivityLog
    {
        $userId = $user ? $user->id : (Auth::check() ? Auth::id() : null);
        
        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'details' => $details
        ]);
    }
    
    /**
     * Get recent activities
     *
     * @param int $limit Number of activities to retrieve
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentActivities(int $limit = 5)
    {
        return ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
