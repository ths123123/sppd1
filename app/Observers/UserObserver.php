<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        Log::info('User creation attempt', [
            'email' => $user->email,
            'name' => $user->name,
            'nip' => $user->nip,
            'timestamp' => now(),
        ]);
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Log::info('User created successfully', [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'nip' => $user->nip,
            'timestamp' => now(),
        ]);
    }

    /**
     * Handle the User "updating" event.
     */
    public function updating(User $user): void
    {
        $originalAttributes = $user->getOriginal();
        $changes = $user->getDirty();
        
        // Log sensitive changes
        $sensitiveFields = ['role', 'is_active', 'password', 'email'];
        $sensitiveChanges = array_intersect_key($changes, array_flip($sensitiveFields));
        
        if (!empty($sensitiveChanges)) {
            Log::warning('Sensitive user fields being updated', [
                'user_id' => $user->id,
                'email' => $user->email,
                'changes' => $sensitiveChanges,
                'original' => array_intersect_key($originalAttributes, array_flip($sensitiveFields)),
                'updated_by' => auth()->id(),
                'timestamp' => now()
            ]);
        }
        
        Log::info('User update attempt', [
            'id' => $user->id,
            'email' => $user->email,
            'changes' => $user->getDirty(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        Log::info('User updated successfully', [
            'id' => $user->id,
            'email' => $user->email,
            'changes' => $user->getChanges(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Handle the User "deleting" event.
     */
    public function deleting(User $user): void
    {
        // Prevent deletion of the last admin user
        if ($user->role === 'admin') {
            $activeAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();
            
            if ($activeAdmins === 0) {
                Log::critical('Attempted to delete last admin user - BLOCKED', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'deleted_by' => auth()->id(),
                    'timestamp' => now()
                ]);
                
                throw new \Exception('Cannot delete the last admin user. At least one admin must remain.');
            }
        }

        Log::warning('User deletion attempt', [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'timestamp' => now(),
        ]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Log::critical('User deleted', [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'deleted_by' => auth()->id(),
            'timestamp' => now(),
        ]);

        // Create backup of deleted user
        $this->createDeletedUserBackup($user);
    }

    /**
     * Handle user deactivation protection
     */
    private function checkAdminDeactivation(User $user): void
    {
        if ($user->role === 'admin' && !$user->is_active) {
            $activeAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();
            
            if ($activeAdmins === 0) {
                Log::critical('Last admin user deactivated - Creating emergency admin', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'deactivated_by' => auth()->id(),
                    'timestamp' => now()
                ]);
                
                // Auto-create emergency admin
                $this->createEmergencyAdmin();
            }
        }
    }

    /**
     * Create emergency admin user
     */
    private function createEmergencyAdmin(): void
    {
        $emergencyAdmin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@kpu.go.id',
            'password' => Hash::make('72e82b77'),
            'nip' => '19800101198001001',
            'role' => 'admin',
            'is_active' => true,
        ]);

        Log::critical('Emergency admin created', [
            'user_id' => $emergencyAdmin->id,
            'email' => $emergencyAdmin->email,
            'created_by' => 'system',
            'timestamp' => now()
        ]);
    }

    /**
     * Create backup of deleted user
     */
    private function createDeletedUserBackup(User $user): void
    {
        $backupData = [
            'deleted_at' => now(),
            'user_data' => $user->toArray(),
            'deleted_by' => auth()->id(),
        ];

        $backupPath = storage_path('app/backups/deleted_users/user_' . $user->id . '_' . date('Y-m-d_H-i-s') . '.json');
        
        if (!file_exists(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0755, true);
        }

        file_put_contents($backupPath, json_encode($backupData, JSON_PRETTY_PRINT));
        
        Log::info('Deleted user backup created', [
            'backup_path' => $backupPath,
            'user_id' => $user->id,
            'timestamp' => now()
        ]);
    }
}
