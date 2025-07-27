<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MonitorUsers extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'users:monitor 
                          {--send-report : Send monitoring report via email}
                          {--check-integrity : Check database integrity}
                          {--alert-threshold=5 : Minimum user count threshold}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor user database for anomalies and issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting User Database Monitoring...');
        
        $stats = $this->collectUserStats();
        $issues = [];
        
        // Check user count
        $threshold = (int) $this->option('alert-threshold');
        if ($stats['total_users'] < $threshold) {
            $issues[] = "LOW USER COUNT: Only {$stats['total_users']} users (threshold: {$threshold})";
        }
        
        // Check for admin users
        if ($stats['admin_count'] === 0) {
            $issues[] = "NO ADMIN USERS: System has no administrators";
        }
        
        // Check for users without passwords
        if ($stats['users_without_password'] > 0) {
            $issues[] = "USERS WITHOUT PASSWORD: {$stats['users_without_password']} users have no password";
        }
        
        // Check for inactive users that might indicate deletion issues
        if ($stats['inactive_users'] > ($stats['total_users'] * 0.5)) {
            $issues[] = "HIGH INACTIVE USER RATIO: {$stats['inactive_users']}/{$stats['total_users']} users are inactive";
        }
        
        // Log monitoring results
        Log::info('User monitoring completed', [
            'timestamp' => now(),
            'stats' => $stats,
            'issues_found' => count($issues),
            'issues' => $issues,
        ]);
        
        // Display results
        $this->displayResults($stats, $issues);
        
        // Send report if requested
        if ($this->option('send-report')) {
            $this->sendMonitoringReport($stats, $issues);
        }
        
        $this->info('✅ User Monitoring Completed!');
        
        // Return exit code based on issues found
        return count($issues) > 0 ? 1 : 0;
    }
    
    private function collectUserStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'admin_count' => User::where('role', 'admin')->count(),
            'sekretaris_count' => User::where('role', 'sekretaris')->count(),
            'staff_count' => User::where('role', 'staff')->count(),
            'users_without_password' => User::whereNull('password')->count(),
            'users_without_email' => User::whereNull('email')->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'recent_logins' => User::where('last_login_at', '>=', now()->subDays(7))->count(),
            'timestamp' => now(),
        ];
    }
    
    private function displayResults(array $stats, array $issues): void
    {
        $this->info('📊 USER DATABASE STATISTICS:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Users', $stats['total_users']],
                ['Active Users', $stats['active_users']],
                ['Inactive Users', $stats['inactive_users']],
                ['Admin Users', $stats['admin_count']],
                ['Sekretaris Users', $stats['sekretaris_count']],

                ['Staff Users', $stats['staff_count']],
                ['Users Without Password', $stats['users_without_password']],
                ['Verified Users', $stats['verified_users']],
                ['Recent Logins (7 days)', $stats['recent_logins']],
            ]
        );
        
        if (count($issues) > 0) {
            $this->error('🚨 ISSUES DETECTED:');
            foreach ($issues as $issue) {
                $this->error("❌ {$issue}");
            }
        } else {
            $this->info('✅ No issues detected - Database is healthy!');
        }
    }
    
    private function sendMonitoringReport(array $stats, array $issues): void
    {
        $this->info('📧 Sending monitoring report...');
        
        // For now, just log the report
        // In production, you would send an actual email
        Log::info('User monitoring report generated', [
            'stats' => $stats,
            'issues' => $issues,
            'severity' => count($issues) > 0 ? 'WARNING' : 'OK',
            'timestamp' => now(),
        ]);
        
        $this->info('✅ Monitoring report logged successfully');
    }
}
