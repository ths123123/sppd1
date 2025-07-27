<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupUsers extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'users:backup 
                          {--format=json : Backup format (json|csv)}
                          {--include-sensitive : Include sensitive data in backup}';

    /**
     * The console command description.
     */
    protected $description = 'Create backup of user data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('💾 Starting User Backup Process...');
        
        $format = $this->option('format');
        $includeSensitive = $this->option('include-sensitive');
        
        $users = $this->collectUserData($includeSensitive);
        $filename = $this->generateBackupFilename($format);
        
        try {
            if ($format === 'csv') {
                $this->saveAsCSV($users, $filename);
            } else {
                $this->saveAsJSON($users, $filename);
            }
            
            $this->info("✅ Backup saved: {$filename}");
            
            Log::info('User backup created', [
                'filename' => $filename,
                'user_count' => count($users),
                'format' => $format,
                'include_sensitive' => $includeSensitive,
                'timestamp' => now(),
            ]);
            
        } catch (\Exception $e) {
            $this->error("❌ Backup failed: {$e->getMessage()}");
            Log::error('User backup failed', [
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            
            return 1;
        }
        
        $this->info('✅ User Backup Process Completed!');
        return 0;
    }
    
    private function collectUserData(bool $includeSensitive): array
    {
        $users = User::all();
        $userData = [];
        
        foreach ($users as $user) {
            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nip' => $user->nip,
                'jabatan' => $user->jabatan,
                'role' => $user->role,
                'phone' => $user->phone,
                'address' => $user->address,
                'pangkat' => $user->pangkat,
                'golongan' => $user->golongan,
                'unit_kerja' => $user->unit_kerja,
                'is_active' => $user->is_active,
                'last_login_at' => $user->last_login_at?->toISOString(),
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'created_at' => $user->created_at->toISOString(),
                'updated_at' => $user->updated_at->toISOString(),
                'department' => $user->department,
                'employee_id' => $user->employee_id,
                'birth_date' => $user->birth_date?->format('Y-m-d'),
                'gender' => $user->gender,
            ];
            
            if ($includeSensitive) {
                $data['password_hash'] = $user->password;
                $data['remember_token'] = $user->remember_token;
            }
            
            $userData[] = $data;
        }
        
        return $userData;
    }
    
    private function generateBackupFilename(string $format): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "user_backup_{$timestamp}.{$format}";
    }
    
    private function saveAsJSON(array $users, string $filename): void
    {
        $jsonData = json_encode([
            'backup_info' => [
                'created_at' => now()->toISOString(),
                'user_count' => count($users),
                'version' => '1.0',
            ],
            'users' => $users,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        Storage::disk('local')->put("backups/{$filename}", $jsonData);
    }
    
    private function saveAsCSV(array $users, string $filename): void
    {
        if (empty($users)) {
            throw new \Exception('No users to backup');
        }
        
        $csv = fopen('php://temp', 'w+');
        
        // Write header
        fputcsv($csv, array_keys($users[0]));
        
        // Write data
        foreach ($users as $user) {
            fputcsv($csv, $user);
        }
        
        rewind($csv);
        $csvContent = stream_get_contents($csv);
        fclose($csv);
        
        Storage::disk('local')->put("backups/{$filename}", $csvContent);
    }
}
