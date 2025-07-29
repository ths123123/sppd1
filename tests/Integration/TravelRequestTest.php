<?php

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Run the user seeder to create test users
    $this->artisan('db:seed', ['--class' => 'UserRoleSeeder']);
});

describe('Travel Request Integration', function () {
    test('authenticated user can create travel request', function () {
        // Arrange
        $user = User::where('email', 'kasubbag1@kpu.go.id')->first();
        
        if (!$user) {
            $this->markTestSkipped('Kasubbag user not found in database');
        }
        
        $data = [
            'tempat_berangkat' => 'Cirebon',
            'tujuan' => 'Jakarta',
            'keperluan' => 'Dinas Luar Kota',
            'tanggal_berangkat' => Carbon::now()->toDateString(),
            'tanggal_kembali' => Carbon::now()->addDays(2)->toDateString(),
            'lama_perjalanan' => 3,
            'transportasi' => 'Bus',
            'tempat_menginap' => 'Hotel Jakarta',
            'biaya_transport' => 500000,
            'biaya_penginapan' => 800000,
            'uang_harian' => 400000,
            'biaya_lainnya' => 100000,
            'total_biaya' => 1800000,
            'sumber_dana' => 'APBD',
            'catatan_pemohon' => 'Perjalanan dinas',
            'is_urgent' => false,
            'nomor_surat_tugas' => 'ST-001',
            'tanggal_surat_tugas' => Carbon::now()->toDateString(),
            'status' => 'in_review',
            'action' => 'save',
        ];
        // Act
        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->post('/travel-requests', $data);
        // Assert
        $response->assertRedirect('/travel-requests');
        $this->assertDatabaseHas('travel_requests', [
            'user_id' => $user->id,
            'tujuan' => 'Jakarta',
            'status' => 'in_review'
        ]);
    });

    test('staff cannot create travel request', function () {
        $user = User::where('email', 'staff1@kpu.go.id')->first();
        
        if (!$user) {
            $this->markTestSkipped('Staff user not found in database');
        }
        
        $data = [
            'tempat_berangkat' => 'Cirebon',
            'tujuan' => 'Bandung',
            'keperluan' => 'Dinas Luar Kota',
            'tanggal_berangkat' => Carbon::now()->toDateString(),
            'tanggal_kembali' => Carbon::now()->addDays(2)->toDateString(),
            'lama_perjalanan' => 3,
            'transportasi' => 'Bus',
            'tempat_menginap' => 'Hotel Bandung',
            'biaya_transport' => 300000,
            'biaya_penginapan' => 800000,
            'uang_harian' => 400000,
            'biaya_lainnya' => 100000,
            'total_biaya' => 1600000,
            'sumber_dana' => 'APBD',
            'catatan_pemohon' => 'Perjalanan dinas',
            'is_urgent' => false,
            'nomor_surat_tugas' => 'ST-002',
            'tanggal_surat_tugas' => Carbon::now()->toDateString(),
            'status' => 'in_review',
            'action' => 'save',
        ];
        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->post('/travel-requests', $data);
        $response->assertStatus(403);
    });

    test('user can view their own travel requests', function () {
        // Arrange
        $user = User::where('email', 'staff1@kpu.go.id')->first();
        
        if (!$user) {
            $this->markTestSkipped('Staff user not found in database');
        }
        
        $otherUser = User::where('email', 'staff2@kpu.go.id')->first();
        
        if (!$otherUser) {
            $this->markTestSkipped('Other staff user not found in database');
        }

        $userRequest = TravelRequest::factory()->create(['user_id' => $user->id]);
        $otherRequest = TravelRequest::factory()->create(['user_id' => $otherUser->id]);

        // Act
        $response = $this->actingAs($user)->get('/my-travel-requests');

        // Assert
        $response->assertOk();
        $response->assertSee($userRequest->tujuan);
        $response->assertDontSee($otherRequest->tujuan);
    });

    test('only admins can view all travel requests', function () {
        // Arrange
        $admin = User::where('email', 'admin@kpu.go.id')->first();
        $user = User::where('email', 'staff1@kpu.go.id')->first();
        
        if (!$admin || !$user) {
            $this->markTestSkipped('Required users not found in database');
        }

        // Act
        $adminResponse = $this->actingAs($admin)->get('/travel-requests');
        $userResponse = $this->actingAs($user)->get('/travel-requests');

        // Assert
        $adminResponse->assertOk();
        $userResponse->assertForbidden();
    });

    test('user can submit travel request for approval', function () {
        // Arrange
        $user = User::where('email', 'kasubbag1@kpu.go.id')->first();
        
        if (!$user) {
            $this->markTestSkipped('Kasubbag user not found in database');
        }
        
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'tempat_berangkat' => 'Cirebon',
            'status' => 'in_review'
        ]);

        // Act
        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->post("/travel-requests/{$travelRequest->id}/submit");

        // Assert
        $response->assertRedirect('/travel-requests');

        $travelRequest->refresh();
        expect($travelRequest->status)->toBe('in_review');
        expect($travelRequest->submitted_at)->not()->toBeNull();
    });

    test('travel request validation works correctly', function () {
        // Arrange
        $user = User::where('email', 'kasubbag1@kpu.go.id')->first();
        
        if (!$user) {
            $this->markTestSkipped('Kasubbag user not found in database');
        }
        
        $invalidData = [
            'tempat_berangkat' => '', // Required field missing
            'tujuan' => '', // Required field missing
            'tanggal_berangkat' => 'invalid-date', // Invalid date
            'biaya_transport' => 'not-a-number', // Invalid number
        ];

        // Act
        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->post('/travel-requests', $invalidData);

        // Assert
        $response->assertSessionHasErrors(['tempat_berangkat', 'tujuan', 'tanggal_berangkat', 'biaya_transport']);
    });

    test('travel request calculates total budget correctly', function () {
        // Arrange
        $user = User::where('email', 'kasubbag1@kpu.go.id')->first();
        
        if (!$user) {
            $this->markTestSkipped('Kasubbag user not found in database');
        }
        
        $data = [
            'tempat_berangkat' => 'Cirebon',
            'tujuan' => 'Jakarta',
            'keperluan' => 'Dinas Luar Kota',
            'tanggal_berangkat' => Carbon::now()->toDateString(),
            'tanggal_kembali' => Carbon::now()->addDays(2)->toDateString(),
            'lama_perjalanan' => 3,
            'transportasi' => 'Bus',
            'biaya_transport' => 500000,
            'biaya_penginapan' => 800000,
            'uang_harian' => 400000,
            'biaya_lainnya' => 100000,
            'action' => 'save',
        ];

        // Act
        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->post('/travel-requests', $data);

        // Assert
        $response->assertRedirect('/travel-requests');
        
        $this->assertDatabaseHas('travel_requests', [
            'user_id' => $user->id,
            'total_biaya' => 1800000, // 500k + 800k + 400k + 100k
        ]);
    });

    test('unauthenticated user cannot access travel request routes', function () {
        // Arrange
        $user = User::where('email', 'kasubbag1@kpu.go.id')->first();
        
        if (!$user) {
            $this->markTestSkipped('Kasubbag user not found in database');
        }
        
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        // Act & Assert
        $createResponse = $this->get('/travel-requests/create');
        $indexResponse = $this->get('/travel-requests');
        $postResponse = $this->post('/travel-requests', [
            'tempat_berangkat' => 'Cirebon',
            'tujuan' => 'Jakarta',
            'keperluan' => 'Test',
            'tanggal_berangkat' => Carbon::now()->toDateString(),
            'tanggal_kembali' => Carbon::now()->addDays(1)->toDateString(),
            'lama_perjalanan' => 2,
            'transportasi' => 'Bus',
            'biaya_transport' => 100000,
            'biaya_penginapan' => 200000,
            'uang_harian' => 100000,
            'biaya_lainnya' => 50000,
        ]);
        
        // Assert
        $createResponse->assertRedirect('/login');
        $indexResponse->assertRedirect('/login');
        // Unauthenticated POST might return 419 (CSRF) or redirect to login
        $this->assertTrue(in_array($postResponse->status(), [302, 419]));
    });
});
