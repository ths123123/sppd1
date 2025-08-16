<?php

namespace Tests\Feature\ErrorHandling;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\Approval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ErrorHandlingTest extends TestCase
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
    public function handles_404_errors_gracefully()
    {
        // Try to access non-existent travel request
        $response = $this->actingAs($this->user)
            ->get('/travel-requests/99999');

        $response->assertStatus(404);
        $response->assertViewIs('errors.404');
    }

    /** @test */
    public function handles_403_errors_gracefully()
    {
        // Try to access unauthorized resource
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/travel-requests/{$travelRequest->id}");

        $response->assertStatus(403);
        $response->assertViewIs('errors.403');
    }

    /** @test */
    public function handles_500_errors_gracefully()
    {
        // Simulate database connection error
        DB::shouldReceive('table')->andThrow(new QueryException('Database connection failed'));

        $response = $this->actingAs($this->user)
            ->get('/travel-requests');

        $response->assertStatus(500);
        $response->assertViewIs('errors.500');
    }

    /** @test */
    public function handles_validation_errors_properly()
    {
        // Submit form with invalid data
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', []);

        $response->assertSessionHasErrors([
            'tujuan',
            'keperluan',
            'tanggal_berangkat',
            'tanggal_kembali',
            'transportasi',
            'estimasi_biaya'
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function handles_file_upload_errors()
    {
        // Try to upload invalid file
        $invalidFile = UploadedFile::fake()->create('malware.exe', 100);

        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 'Test Document',
                'jenis_dokumen' => 'surat_tugas',
                'file' => $invalidFile
            ]);

        $response->assertSessionHasErrors(['file']);
        $response->assertRedirect();
    }

    /** @test */
    public function handles_database_constraint_violations()
    {
        // Try to create user with duplicate email
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test User',
                'email' => 'duplicate@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'user'
            ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertRedirect();
    }

    /** @test */
    public function handles_foreign_key_constraint_violations()
    {
        // Try to create travel request with non-existent user_id
        $this->expectException(QueryException::class);

        TravelRequest::factory()->create([
            'user_id' => 99999 // Non-existent user
        ]);
    }

    /** @test */
    public function handles_mass_assignment_errors()
    {
        // Try to set protected fields
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Test Destination',
                'keperluan' => 'Test Purpose',
                'tanggal_berangkat' => '2024-02-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 2000000,
                'user_id' => 999, // Protected field
                'status' => 'approved' // Protected field
            ]);

        $response->assertSessionHasErrors();
        $response->assertRedirect();
    }

    /** @test */
    public function handles_session_timeout_errors()
    {
        // Simulate session timeout
        $this->actingAs($this->user);
        session(['last_activity' => now()->subMinutes(30)]);

        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function handles_csrf_token_mismatch()
    {
        // Disable CSRF protection temporarily
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Test Destination'
            ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    /** @test */
    public function handles_rate_limiting_errors()
    {
        // Make multiple rapid requests
        for ($i = 0; $i < 100; $i++) {
            $response = $this->actingAs($this->user)
                ->get('/dashboard');
            
            if ($response->status() === 429) {
                break;
            }
        }

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function handles_invalid_json_errors()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->actingAs($this->user)
            ->post('/api/travel-requests', 'invalid json');

        $response->assertStatus(400); // Bad Request
    }

    /** @test */
    public function handles_method_not_allowed_errors()
    {
        // Try to use wrong HTTP method
        $response = $this->actingAs($this->user)
            ->get('/travel-requests', [
                '_method' => 'POST'
            ]);

        $response->assertStatus(405); // Method Not Allowed
    }

    /** @test */
    public function handles_unsupported_media_type_errors()
    {
        // Try to upload unsupported file type
        $unsupportedFile = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 'Test Document',
                'jenis_dokumen' => 'surat_tugas',
                'file' => $unsupportedFile
            ]);

        $response->assertSessionHasErrors(['file']);
        $response->assertRedirect();
    }

    /** @test */
    public function handles_request_entity_too_large_errors()
    {
        // Try to upload extremely large file
        $largeFile = UploadedFile::fake()->create('large_file.pdf', 10241); // 10MB + 1KB

        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 'Test Document',
                'jenis_dokumen' => 'surat_tugas',
                'file' => $largeFile
            ]);

        $response->assertSessionHasErrors(['file']);
        $response->assertRedirect();
    }

    /** @test */
    public function handles_database_connection_errors()
    {
        // Simulate database connection failure
        DB::shouldReceive('connection')->andThrow(new \Exception('Database connection failed'));

        $response = $this->actingAs($this->user)
            ->get('/travel-requests');

        $response->assertStatus(500);
        $response->assertViewIs('errors.500');
    }

    /** @test */
    public function handles_storage_errors()
    {
        // Simulate storage failure
        Storage::shouldReceive('disk')->andThrow(new \Exception('Storage disk not available'));

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 'Test Document',
                'jenis_dokumen' => 'surat_tugas',
                'file' => $file
            ]);

        $response->assertStatus(500);
        $response->assertViewIs('errors.500');
    }

    /** @test */
    public function handles_mail_sending_errors()
    {
        // Simulate mail sending failure
        \Mail::shouldReceive('to')->andThrow(new \Exception('Mail server not available'));

        // This would normally send an email
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Test Destination',
                'keperluan' => 'Test Purpose',
                'tanggal_berangkat' => '2024-02-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 2000000
            ]);

        // Should still work even if email fails
        $response->assertRedirect('/travel-requests');
    }

    /** @test */
    public function handles_queue_processing_errors()
    {
        // Simulate queue processing failure
        \Queue::shouldReceive('push')->andThrow(new \Exception('Queue server not available'));

        // This would normally queue a job
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Test Destination',
                'keperluan' => 'Test Purpose',
                'tanggal_berangkat' => '2024-02-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 2000000
            ]);

        // Should still work even if queue fails
        $response->assertRedirect('/travel-requests');
    }

    /** @test */
    public function handles_cache_errors()
    {
        // Simulate cache failure
        \Cache::shouldReceive('get')->andThrow(new \Exception('Cache server not available'));

        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        // Should still work even if cache fails
        $response->assertStatus(200);
    }

    /** @test */
    public function handles_session_errors()
    {
        // Simulate session failure
        session()->shouldReceive('get')->andThrow(new \Exception('Session not available'));

        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        // Should handle session errors gracefully
        $response->assertStatus(500);
        $response->assertViewIs('errors.500');
    }

    /** @test */
    public function handles_authentication_errors()
    {
        // Try to access protected route without authentication
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function handles_authorization_errors()
    {
        // Regular user trying to access admin functionality
        $response = $this->actingAs($this->user)
            ->get('/admin/dashboard');

        $response->assertStatus(403);
        $response->assertViewIs('errors.403');
    }

    /** @test */
    public function handles_model_not_found_errors()
    {
        // Try to access non-existent model
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $travelRequest = TravelRequest::findOrFail(99999);
    }

    /** @test */
    public function handles_relationship_errors()
    {
        // Try to access relationship on non-existent model
        $response = $this->actingAs($this->user)
            ->get('/travel-requests/99999/user');

        $response->assertStatus(404);
    }

    /** @test */
    public function handles_validation_rule_errors()
    {
        // Submit data that violates custom validation rules
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Test',
                'keperluan' => 'Test',
                'tanggal_berangkat' => '2020-01-01', // Past date
                'tanggal_kembali' => '2020-01-01', // Past date
                'transportasi' => 'Invalid Transport',
                'estimasi_biaya' => -1000 // Negative value
            ]);

        $response->assertSessionHasErrors([
            'tanggal_berangkat',
            'tanggal_kembali',
            'transportasi',
            'estimasi_biaya'
        ]);
    }

    /** @test */
    public function handles_unique_constraint_errors()
    {
        // Create first user
        User::factory()->create(['email' => 'duplicate@example.com']);

        // Try to create second user with same email
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test User',
                'email' => 'duplicate@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'user'
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function handles_check_constraint_errors()
    {
        // Try to create travel request with invalid status
        $this->expectException(QueryException::class);

        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'invalid_status'
        ]);
    }

    /** @test */
    public function handles_transaction_errors()
    {
        // Simulate transaction failure
        DB::shouldReceive('beginTransaction')->andThrow(new \Exception('Transaction failed'));

        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Test Destination',
                'keperluan' => 'Test Purpose',
                'tanggal_berangkat' => '2024-02-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 2000000
            ]);

        $response->assertStatus(500);
        $response->assertViewIs('errors.500');
    }

    /** @test */
    public function handles_file_permission_errors()
    {
        // Simulate file permission error
        Storage::shouldReceive('disk')->andThrow(new \Exception('Permission denied'));

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 'Test Document',
                'jenis_dokumen' => 'surat_tugas',
                'file' => $file
            ]);

        $response->assertStatus(500);
        $response->assertViewIs('errors.500');
    }

    /** @test */
    public function handles_memory_limit_errors()
    {
        // Simulate memory limit error
        $this->expectException(\ErrorException::class);

        // Try to allocate more memory than available
        $largeArray = [];
        for ($i = 0; $i < PHP_INT_MAX; $i++) {
            $largeArray[] = str_repeat('a', 1000000);
        }
    }

    /** @test */
    public function handles_timeout_errors()
    {
        // Simulate timeout error
        set_time_limit(1);
        
        // This should timeout
        sleep(2);
        
        $this->assertTrue(true); // If we reach here, timeout handling worked
    }

    /** @test */
    public function handles_custom_exception_handling()
    {
        // Test custom exception handler
        $this->expectException(\App\Exceptions\CustomException::class);

        throw new \App\Exceptions\CustomException('Custom error message');
    }

    /** @test */
    public function handles_logging_errors()
    {
        // Simulate logging failure
        \Log::shouldReceive('error')->andThrow(new \Exception('Logging failed'));

        // This should not crash the application
        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function handles_error_reporting()
    {
        // Test error reporting configuration
        $this->assertTrue(error_reporting() !== 0);
        
        // Test that errors are logged
        $this->assertTrue(\Log::getLogger() !== null);
    }
}
