<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\Approval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SecurityTest extends TestCase
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
    public function authentication_prevents_unauthorized_access()
    {
        // Try to access protected route without authentication
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authentication_requires_valid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword'
        ]);
        
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function authentication_prevents_brute_force_attacks()
    {
        // Try multiple failed login attempts
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/login', [
                'email' => $this->user->email,
                'password' => 'wrongpassword'
            ]);
        }
        
        // Should implement rate limiting or account lockout
        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function authentication_prevents_sql_injection()
    {
        $maliciousInput = "'; DROP TABLE users; --";
        
        $response = $this->post('/login', [
            'email' => $maliciousInput,
            'password' => 'password'
        ]);
        
        // Should not crash and should handle gracefully
        $response->assertSessionHasErrors();
        
        // Users table should still exist
        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    /** @test */
    public function authentication_prevents_xss_attacks()
    {
        $maliciousInput = '<script>alert("XSS")</script>';
        
        $response = $this->post('/login', [
            'email' => $maliciousInput,
            'password' => 'password'
        ]);
        
        // Should escape HTML and prevent XSS
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function authorization_prevents_privilege_escalation()
    {
        // Regular user trying to access admin functionality
        $response = $this->actingAs($this->user)
            ->get('/admin/dashboard');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function authorization_prevents_unauthorized_resource_access()
    {
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id
        ]);
        
        // User trying to access other user's travel request
        $response = $this->actingAs($this->user)
            ->get("/travel-requests/{$travelRequest->id}");
        
        $response->assertStatus(403);
    }

    /** @test */
    public function authorization_prevents_unauthorized_resource_modification()
    {
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id
        ]);
        
        // User trying to modify other user's travel request
        $response = $this->actingAs($this->user)
            ->put("/travel-requests/{$travelRequest->id}", [
                'tujuan' => 'Hacked Destination'
            ]);
        
        $response->assertStatus(403);
        
        // Data should not be modified
        $this->assertDatabaseMissing('travel_requests', [
            'id' => $travelRequest->id,
            'tujuan' => 'Hacked Destination'
        ]);
    }

    /** @test */
    public function authorization_prevents_unauthorized_resource_deletion()
    {
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id
        ]);
        
        // User trying to delete other user's travel request
        $response = $this->actingAs($this->user)
            ->delete("/travel-requests/{$travelRequest->id}");
        
        $response->assertStatus(403);
        
        // Data should not be deleted
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id
        ]);
    }

    /** @test */
    public function csrf_protection_prevents_cross_site_request_forgery()
    {
        // Disable CSRF protection temporarily
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        
        $response = $this->post('/travel-requests', [
            'tujuan' => 'CSRF Attack',
            'keperluan' => 'Malicious Purpose'
        ]);
        
        // Should fail without CSRF token
        $response->assertStatus(419); // CSRF token mismatch
    }

    /** @test */
    public function input_validation_prevents_malicious_data()
    {
        $maliciousData = [
            'tujuan' => '<script>alert("XSS")</script>',
            'keperluan' => "'; DROP TABLE users; --",
            'tanggal_berangkat' => 'invalid-date',
            'transportasi' => 'Invalid Transport',
            'estimasi_biaya' => 'not-a-number'
        ];
        
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', $maliciousData);
        
        $response->assertSessionHasErrors([
            'tujuan',
            'keperluan',
            'tanggal_berangkat',
            'transportasi',
            'estimasi_biaya'
        ]);
    }

    /** @test */
    public function file_upload_prevents_malicious_files()
    {
        // Try to upload executable file
        $maliciousFile = UploadedFile::fake()->create('malware.exe', 100);
        
        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 'Malicious File',
                'jenis_dokumen' => 'surat_tugas',
                'file' => $maliciousFile
            ]);
        
        $response->assertSessionHasErrors(['file']);
    }

    /** @test */
    public function file_upload_prevents_large_files()
    {
        // Try to upload extremely large file
        $largeFile = UploadedFile::fake()->create('large_file.pdf', 10241); // 10MB + 1KB
        
        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 'Large File',
                'jenis_dokumen' => 'surat_tugas',
                'file' => $largeFile
            ]);
        
        $response->assertSessionHasErrors(['file']);
    }

    /** @test */
    public function file_upload_prevents_invalid_mime_types()
    {
        // Try to upload file with invalid MIME type
        $invalidFile = UploadedFile::fake()->create('document.txt', 100, 'text/plain');
        
        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 'Invalid File',
                'jenis_dokumen' => 'surat_tugas',
                'file' => $invalidFile
            ]);
        
        $response->assertSessionHasErrors(['file']);
    }

    /** @test */
    public function sql_injection_prevention_in_search()
    {
        $maliciousQuery = "'; DROP TABLE users; --";
        
        $response = $this->actingAs($this->user)
            ->get("/travel-requests/search?q={$maliciousQuery}");
        
        // Should not crash and should handle gracefully
        $response->assertStatus(200);
        
        // Users table should still exist
        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    /** @test */
    public function xss_prevention_in_output()
    {
        $maliciousInput = '<script>alert("XSS")</script>';
        
        // Create travel request with malicious input
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'tujuan' => $maliciousInput
        ]);
        
        $response = $this->actingAs($this->user)
            ->get("/travel-requests/{$travelRequest->id}");
        
        // HTML should be escaped in output
        $response->assertDontSee('<script>');
        $response->assertSee(htmlspecialchars($maliciousInput));
    }

    /** @test */
    public function mass_assignment_protection()
    {
        $maliciousData = [
            'tujuan' => 'Legitimate Destination',
            'keperluan' => 'Legitimate Purpose',
            'user_id' => 999, // Trying to assign to different user
            'status' => 'approved', // Trying to set approved status directly
            'approved_at' => now(), // Trying to set approval timestamp
            'approved_by' => 999 // Trying to set approver
        ];
        
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', $maliciousData);
        
        $response->assertSessionHasErrors();
        
        // Only fillable fields should be set
        $this->assertDatabaseMissing('travel_requests', [
            'user_id' => 999,
            'status' => 'approved'
        ]);
    }

    /** @test */
    public function session_fixation_prevention()
    {
        // Get initial session ID
        $initialSessionId = session()->getId();
        
        // Login user
        $this->actingAs($this->user);
        
        // Session ID should change after login
        $newSessionId = session()->getId();
        $this->assertNotEquals($initialSessionId, $newSessionId);
    }

    /** @test */
    public function session_timeout_handling()
    {
        // Login user
        $this->actingAs($this->user);
        
        // Simulate session timeout
        session(['last_activity' => now()->subMinutes(30)]);
        
        $response = $this->get('/dashboard');
        
        // Should redirect to login after timeout
        $response->assertRedirect('/login');
    }

    /** @test */
    public function password_strength_requirements()
    {
        $weakPasswords = [
            '123',
            'password',
            'abc123',
            'qwerty'
        ];
        
        foreach ($weakPasswords as $weakPassword) {
            $response = $this->actingAs($this->admin)
                ->post('/admin/users', [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => $weakPassword,
                    'password_confirmation' => $weakPassword,
                    'role' => 'user'
                ]);
            
            $response->assertSessionHasErrors(['password']);
        }
    }

    /** @test */
    public function password_confirmation_requirement()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'strongpassword',
                'password_confirmation' => 'differentpassword',
                'role' => 'user'
            ]);
        
        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function email_uniqueness_enforcement()
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
    public function nip_uniqueness_enforcement()
    {
        // Create first user
        User::factory()->create(['nip' => '123456789']);
        
        // Try to create second user with same NIP
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'user',
                'nip' => '123456789'
            ]);
        
        $response->assertSessionHasErrors(['nip']);
    }

    /** @test */
    public function role_validation_enforcement()
    {
        $invalidRoles = [
            'invalid_role',
            'super_admin',
            'hacker',
            'root'
        ];
        
        foreach ($invalidRoles as $invalidRole) {
            $response = $this->actingAs($this->admin)
                ->post('/admin/users', [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                    'role' => $invalidRole
                ]);
            
            $response->assertSessionHasErrors(['role']);
        }
    }

    /** @test */
    public function date_validation_enforcement()
    {
        $invalidDates = [
            'invalid-date',
            '2020-13-01', // Invalid month
            '2020-01-32', // Invalid day
            '2020-02-30', // Invalid day for February
            '2020-00-01'  // Invalid month
        ];
        
        foreach ($invalidDates as $invalidDate) {
            $response = $this->actingAs($this->user)
                ->post('/travel-requests', [
                    'tujuan' => 'Test Destination',
                    'keperluan' => 'Test Purpose',
                    'tanggal_berangkat' => $invalidDate,
                    'tanggal_kembali' => '2024-02-03',
                    'transportasi' => 'Pesawat',
                    'estimasi_biaya' => 2000000
                ]);
            
            $response->assertSessionHasErrors(['tanggal_berangkat']);
        }
    }

    /** @test */
    public function numeric_validation_enforcement()
    {
        $invalidNumbers = [
            'not-a-number',
            '123abc',
            'abc123',
            '12.34.56',
            '1,234'
        ];
        
        foreach ($invalidNumbers as $invalidNumber) {
            $response = $this->actingAs($this->user)
                ->post('/travel-requests', [
                    'tujuan' => 'Test Destination',
                    'keperluan' => 'Test Purpose',
                    'tanggal_berangkat' => '2024-02-01',
                    'tanggal_kembali' => '2024-02-03',
                    'transportasi' => 'Pesawat',
                    'estimasi_biaya' => $invalidNumber
                ]);
            
            $response->assertSessionHasErrors(['estimasi_biaya']);
        }
    }

    /** @test */
    public function length_validation_enforcement()
    {
        // Test extremely long inputs
        $longInput = str_repeat('a', 10000);
        
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => $longInput,
                'keperluan' => $longInput,
                'tanggal_berangkat' => '2024-02-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 2000000
            ]);
        
        $response->assertSessionHasErrors(['tujuan', 'keperluan']);
    }

    /** @test */
    public function method_spoofing_prevention()
    {
        // Try to spoof PUT method using POST
        $response = $this->actingAs($this->user)
            ->post('/travel-requests/999', [
                '_method' => 'PUT',
                'tujuan' => 'Spoofed Update'
            ]);
        
        // Should not allow method spoofing
        $response->assertStatus(404); // Resource not found
    }

    /** @test */
    public function header_injection_prevention()
    {
        $maliciousHeaders = [
            'X-Forwarded-For' => 'malicious.com',
            'User-Agent' => '<script>alert("XSS")</script>',
            'Referer' => "javascript:alert('XSS')"
        ];
        
        $response = $this->withHeaders($maliciousHeaders)
            ->actingAs($this->user)
            ->get('/dashboard');
        
        // Should handle malicious headers gracefully
        $response->assertStatus(200);
    }

    /** @test */
    public function directory_traversal_prevention()
    {
        $maliciousPaths = [
            '../../../etc/passwd',
            '..\\..\\..\\windows\\system32\\drivers\\etc\\hosts',
            '....//....//....//etc/passwd',
            '%2e%2e%2f%2e%2e%2f%2e%2e%2fetc%2fpasswd'
        ];
        
        foreach ($maliciousPaths as $maliciousPath) {
            $response = $this->actingAs($this->user)
                ->get("/documents/download?path={$maliciousPath}");
            
            // Should prevent directory traversal
            $response->assertStatus(400); // Bad Request
        }
    }

    /** @test */
    public function log_injection_prevention()
    {
        $maliciousLogData = [
            'action' => "'; DROP TABLE users; --",
            'description' => '<script>alert("XSS")</script>',
            'ip_address' => '127.0.0.1\nadmin:password'
        ];
        
        // Try to create activity log with malicious data
        $response = $this->actingAs($this->user)
            ->post('/activity-logs', $maliciousLogData);
        
        // Should validate and sanitize input
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function rate_limiting_enforcement()
    {
        // Make multiple rapid requests
        for ($i = 0; $i < 100; $i++) {
            $response = $this->actingAs($this->user)
                ->get('/dashboard');
            
            if ($response->status() === 429) {
                break;
            }
        }
        
        // Should implement rate limiting
        $this->assertEquals(429, $response->status()); // Too Many Requests
    }
}
