<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\TravelRequest;
use App\Models\Approval;
use App\Models\Notification;

class CleanupDummyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:dummy-data {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up all dummy travel request data from database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Starting cleanup of dummy data...');
        
        // Check if force flag is set
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete all dummy travel request data?')) {
                $this->info('❌ Cleanup cancelled.');
                return;
            }
        }
        
        try {
            // Start transaction
            DB::beginTransaction();
            
            // 1. Find dummy travel requests
            $dummyTravelRequests = TravelRequest::where('kode_sppd', 'like', 'SPD/2025/07/%')->get();
            $travelRequestCount = $dummyTravelRequests->count();
            
            if ($travelRequestCount > 0) {
                $this->info("Found {$travelRequestCount} dummy travel requests to delete...");
                
                // Get travel request IDs
                $travelRequestIds = $dummyTravelRequests->pluck('id')->toArray();
                
                // Delete related approvals first
                $approvalCount = Approval::whereIn('travel_request_id', $travelRequestIds)->delete();
                $this->info("✅ Deleted {$approvalCount} related approvals");
                
                // Delete related notifications
                $notificationCount = Notification::whereIn('travel_request_id', $travelRequestIds)->delete();
                $this->info("✅ Deleted {$notificationCount} related notifications");
                
                // Delete travel requests
                $deletedCount = TravelRequest::whereIn('id', $travelRequestIds)->delete();
                $this->info("✅ Deleted {$deletedCount} dummy travel requests");
                
            } else {
                $this->info("ℹ️  No dummy travel requests found");
            }
            
            // 2. Delete any other recent dummy data (optional)
            $otherDummyRequests = TravelRequest::where('kode_sppd', 'like', 'SPD/%')
                ->where('created_at', '>=', now()->subDays(7))
                ->where('user_id', function($query) {
                    $query->select('id')
                          ->from('users')
                          ->where('email', 'kasubbag1@kpu.go.id')
                          ->limit(1);
                })
                ->get();
            
            if ($otherDummyRequests->count() > 0) {
                $this->info("Found {$otherDummyRequests->count()} additional dummy requests...");
                
                $otherIds = $otherDummyRequests->pluck('id')->toArray();
                
                // Delete related data
                Approval::whereIn('travel_request_id', $otherIds)->delete();
                Notification::whereIn('travel_request_id', $otherIds)->delete();
                TravelRequest::whereIn('id', $otherIds)->delete();
                
                $this->info("✅ Deleted {$otherDummyRequests->count()} additional dummy requests");
            }
            
            // Commit transaction
            DB::commit();
            
            // Show final statistics
            $totalTravelRequests = TravelRequest::count();
            $totalApprovals = Approval::count();
            $totalNotifications = Notification::count();
            
            $this->newLine();
            $this->info('📊 Database Statistics after cleanup:');
            $this->line("   • Travel Requests: {$totalTravelRequests}");
            $this->line("   • Approvals: {$totalApprovals}");
            $this->line("   • Notifications: {$totalNotifications}");
            
            $this->newLine();
            $this->info('🎉 Cleanup completed successfully!');
            
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            $this->error("❌ Error during cleanup: " . $e->getMessage());
            $this->error("Stack trace:\n" . $e->getTraceAsString());
            
            return 1;
        }
        
        return 0;
    }
} 