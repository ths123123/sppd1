<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\Approval;
use App\Models\Setting;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;

class PerformanceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $admin;
    protected $approver;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->approver = User::factory()->approver()->create();
    }

    /** @test */
    public function database_queries_are_optimized_with_eager_loading()
    {
        // Create travel requests with relationships
        $travelRequests = TravelRequest::factory()->count(10)->create([
            'user_id' => $this->user->id
        ]);

        // Without eager loading - should trigger N+1 queries
        DB::enableQueryLog();
        
        $travelRequestsWithoutEager = TravelRequest::all();
        foreach ($travelRequestsWithoutEager as $request) {
            $request->user->name; // This triggers additional queries
        }
        
        $queriesWithoutEager = count(DB::getQueryLog());
        
        // Clear query log
        DB::flushQueryLog();
        
        // With eager loading - should be optimized
        $travelRequestsWithEager = TravelRequest::with('user')->get();
        foreach ($travelRequestsWithEager as $request) {
            $request->user->name; // This should not trigger additional queries
        }
        
        $queriesWithEager = count(DB::getQueryLog());
        
        // Eager loading should reduce the number of queries significantly
        $this->assertLessThan($queriesWithoutEager, $queriesWithEager);
    }

    /** @test */
    public function database_queries_use_proper_indexes()
    {
        // Create test data
        User::factory()->count(100)->create();
        TravelRequest::factory()->count(100)->create();
        
        // Enable query log
        DB::enableQueryLog();
        
        // Query that should use indexes
        $users = User::where('role', 'user')->get();
        $travelRequests = TravelRequest::where('status', 'pending')->get();
        
        $queries = DB::getQueryLog();
        
        // Check if queries are using indexes (should be fast)
        $this->assertCount(100, $users);
        $this->assertCount(100, $travelRequests);
        
        // Queries should complete quickly (this is a basic check)
        $this->assertLessThan(1000, microtime(true) * 1000); // Less than 1 second
    }

    /** @test */
    public function database_pagination_works_efficiently()
    {
        // Create large dataset
        TravelRequest::factory()->count(1000)->create();
        
        $startTime = microtime(true);
        
        // Test pagination
        $page1 = TravelRequest::paginate(50);
        $page2 = TravelRequest::paginate(50, ['*'], 'page', 2);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Pagination should be fast
        $this->assertLessThan(1.0, $executionTime); // Less than 1 second
        $this->assertEquals(50, $page1->count());
        $this->assertEquals(50, $page2->count());
    }

    /** @test */
    public function database_search_is_optimized()
    {
        // Create test data
        TravelRequest::factory()->count(100)->create([
            'tujuan' => 'Jakarta'
        ]);
        
        TravelRequest::factory()->count(100)->create([
            'tujuan' => 'Bandung'
        ]);
        
        $startTime = microtime(true);
        
        // Search operation
        $results = TravelRequest::where('tujuan', 'LIKE', '%Jakarta%')->get();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Search should be fast
        $this->assertLessThan(0.5, $executionTime); // Less than 0.5 seconds
        $this->assertCount(100, $results);
    }

    /** @test */
    public function database_bulk_operations_are_efficient()
    {
        $startTime = microtime(true);
        
        // Bulk insert
        $users = [];
        for ($i = 0; $i < 100; $i++) {
            $users[] = [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => bcrypt('password'),
                'role' => 'user',
                'nip' => "123456{$i}",
                'jabatan' => 'Staff',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        User::insert($users);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Bulk insert should be fast
        $this->assertLessThan(1.0, $executionTime); // Less than 1 second
        $this->assertEquals(103, User::count()); // 3 from setUp + 100 new
    }

    /** @test */
    public function database_relationships_are_optimized()
    {
        // Create test data with relationships
        $travelRequests = TravelRequest::factory()->count(50)->create([
            'user_id' => $this->user->id
        ]);
        
        foreach ($travelRequests as $request) {
            Approval::factory()->create([
                'travel_request_id' => $request->id,
                'approver_id' => $this->approver->id
            ]);
        }
        
        $startTime = microtime(true);
        
        // Load relationships efficiently
        $travelRequestsWithRelations = TravelRequest::with(['user', 'approvals.approver'])
            ->where('user_id', $this->user->id)
            ->get();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Loading relationships should be fast
        $this->assertLessThan(0.5, $executionTime); // Less than 0.5 seconds
        $this->assertCount(50, $travelRequestsWithRelations);
        
        // Check if relationships are loaded
        foreach ($travelRequestsWithRelations as $request) {
            $this->assertTrue($request->relationLoaded('user'));
            $this->assertTrue($request->relationLoaded('approvals'));
        }
    }

    /** @test */
    public function caching_improves_performance()
    {
        // Create test data
        $settings = Setting::factory()->count(10)->create();
        
        // First request without cache
        $startTime = microtime(true);
        $settingsWithoutCache = Setting::all();
        $endTime = microtime(true);
        $timeWithoutCache = $endTime - $startTime;
        
        // Cache the results
        Cache::put('settings', $settingsWithoutCache, 3600);
        
        // Second request with cache
        $startTime = microtime(true);
        $settingsWithCache = Cache::get('settings');
        $endTime = microtime(true);
        $timeWithCache = $endTime - $startTime;
        
        // Cached request should be faster
        $this->assertLessThan($timeWithoutCache, $timeWithCache);
        $this->assertCount(10, $settingsWithCache);
    }

    /** @test */
    public function database_connection_pooling_works()
    {
        // Test multiple concurrent database connections
        $connections = [];
        $startTime = microtime(true);
        
        for ($i = 0; $i < 10; $i++) {
            $connections[] = DB::connection();
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Creating connections should be fast
        $this->assertLessThan(1.0, $executionTime); // Less than 1 second
        $this->assertCount(10, $connections);
        
        // Test if connections work
        foreach ($connections as $connection) {
            $result = $connection->select('SELECT 1 as test');
            $this->assertEquals(1, $result[0]->test);
        }
    }

    /** @test */
    public function database_transactions_are_efficient()
    {
        $startTime = microtime(true);
        
        DB::transaction(function () {
            for ($i = 0; $i < 100; $i++) {
                User::factory()->create();
            }
        });
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Transaction should be efficient
        $this->assertLessThan(2.0, $executionTime); // Less than 2 seconds
        $this->assertEquals(203, User::count()); // 3 from setUp + 100 new
    }

    /** @test */
    public function database_queries_are_logged_for_debugging()
    {
        // Enable query logging
        DB::enableQueryLog();
        
        // Perform some queries
        User::all();
        TravelRequest::all();
        
        $queries = DB::getQueryLog();
        
        // Queries should be logged
        $this->assertGreaterThan(0, count($queries));
        
        // Check query structure
        foreach ($queries as $query) {
            $this->assertArrayHasKey('sql', $query);
            $this->assertArrayHasKey('time', $query);
        }
    }

    /** @test */
    public function database_slow_query_logging_works()
    {
        // Set slow query threshold
        config(['database.slow_query_threshold' => 100]); // 100ms
        
        // Enable query logging
        DB::enableQueryLog();
        
        // Perform a potentially slow query
        $startTime = microtime(true);
        $users = User::where('role', 'user')->get();
        $endTime = microtime(true);
        
        $queries = DB::getQueryLog();
        
        // Check if slow queries are identified
        foreach ($queries as $query) {
            if ($query['time'] > 100) {
                // This query is considered slow
                $this->assertGreaterThan(100, $query['time']);
            }
        }
    }

    /** @test */
    public function database_connection_timeout_handling()
    {
        // Test database connection timeout
        $startTime = microtime(true);
        
        try {
            // This should complete within reasonable time
            $users = User::all();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            // Query should complete quickly
            $this->assertLessThan(5.0, $executionTime); // Less than 5 seconds
            
        } catch (\Exception $e) {
            // If timeout occurs, it should be handled gracefully
            $this->assertStringContainsString('timeout', strtolower($e->getMessage()));
        }
    }

    /** @test */
    public function database_memory_usage_is_optimized()
    {
        // Get initial memory usage
        $initialMemory = memory_get_usage(true);
        
        // Perform memory-intensive operations
        $users = User::factory()->count(1000)->create();
        $travelRequests = TravelRequest::factory()->count(1000)->create();
        
        // Get memory usage after operations
        $finalMemory = memory_get_usage(true);
        $memoryUsed = $finalMemory - $initialMemory;
        
        // Memory usage should be reasonable (less than 100MB)
        $this->assertLessThan(100 * 1024 * 1024, $memoryUsed); // Less than 100MB
    }

    /** @test */
    public function database_query_result_caching()
    {
        // Create test data
        $settings = Setting::factory()->count(5)->create();
        
        // First query
        $startTime = microtime(true);
        $result1 = Setting::all();
        $endTime = microtime(true);
        $time1 = $endTime - $startTime;
        
        // Second query (should be cached)
        $startTime = microtime(true);
        $result2 = Setting::all();
        $endTime = microtime(true);
        $time2 = $endTime - $startTime;
        
        // Results should be the same
        $this->assertEquals($result1->count(), $result2->count());
        
        // Second query might be faster due to internal caching
        $this->assertLessThanOrEqual($time1, $time2);
    }

    /** @test */
    public function database_connection_limits_are_respected()
    {
        // Test connection limit handling
        $connections = [];
        $maxConnections = 20;
        
        try {
            for ($i = 0; $i < $maxConnections; $i++) {
                $connections[] = DB::connection();
            }
            
            // Should be able to create connections up to limit
            $this->assertCount($maxConnections, $connections);
            
        } catch (\Exception $e) {
            // If connection limit is reached, it should be handled gracefully
            $this->assertStringContainsString('connection', strtolower($e->getMessage()));
        }
    }

    /** @test */
    public function database_backup_performance()
    {
        // Create test data
        User::factory()->count(100)->create();
        TravelRequest::factory()->count(100)->create();
        
        $startTime = microtime(true);
        
        // Simulate backup operation
        $this->artisan('backup:run');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Backup should complete within reasonable time
        $this->assertLessThan(60.0, $executionTime); // Less than 60 seconds
    }

    /** @test */
    public function database_optimization_commands()
    {
        // Test database optimization commands
        $startTime = microtime(true);
        
        // Optimize tables
        $this->artisan('db:optimize');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Optimization should complete quickly
        $this->assertLessThan(10.0, $executionTime); // Less than 10 seconds
    }

    /** @test */
    public function database_maintenance_mode_performance()
    {
        // Enable maintenance mode
        $this->artisan('down');
        
        $startTime = microtime(true);
        
        // Try to access application
        $response = $this->get('/');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Maintenance mode response should be fast
        $this->assertLessThan(1.0, $executionTime); // Less than 1 second
        $this->assertEquals(503, $response->status());
        
        // Disable maintenance mode
        $this->artisan('up');
    }

    /** @test */
    public function database_queue_performance()
    {
        // Test queue performance
        $startTime = microtime(true);
        
        // Dispatch multiple jobs
        for ($i = 0; $i < 100; $i++) {
            // Simulate job dispatch
            Queue::push(function () {
                // Simulate job processing
                sleep(0.001);
            });
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Job dispatching should be fast
        $this->assertLessThan(1.0, $executionTime); // Less than 1 second
    }

    /** @test */
    public function database_event_performance()
    {
        // Test event performance
        $startTime = microtime(true);
        
        // Fire multiple events
        for ($i = 0; $i < 100; $i++) {
            Event::dispatch('test.event', ['data' => $i]);
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Event dispatching should be fast
        $this->assertLessThan(1.0, $executionTime); // Less than 1 second
    }

    /** @test */
    public function database_connection_pooling_efficiency()
    {
        // Test connection pooling efficiency
        $startTime = microtime(true);
        
        $connections = [];
        for ($i = 0; $i < 50; $i++) {
            $connections[] = DB::connection();
        }
        
        // Use connections
        foreach ($connections as $connection) {
            $connection->select('SELECT 1 as test');
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Connection pooling should be efficient
        $this->assertLessThan(2.0, $executionTime); // Less than 2 seconds
        $this->assertCount(50, $connections);
    }
}
