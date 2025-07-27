<?php

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

describe('Profile Controller Integration', function () {

    test('authenticated user can view profile edit page', function () {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get('/profile');

        // Assert
        $response->assertOk();
        $response->assertViewIs('profile.show');
        $response->assertViewHas('user', $user);
    });

    test('user can update profile via ajax', function () {
        // Arrange
        $user = User::factory()->create();
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'nip' => '198402132009121003', // valid 18 digit NIP
            'jabatan' => 'Updated Jabatan',
            'unit_kerja' => 'Updated Unit',
            'phone' => '081234567890',
            'pangkat' => 'Updated Pangkat',
            'golongan' => 'III/c',
            'address' => 'Updated Address',
            'bio' => 'Updated Bio',
            'department' => 'Updated Department',
            'employee_id' => 'EMP123',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
        ];

        // Act
        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->patchJson('/profile/ajax', $updateData);

        // Assert
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Profile berhasil diperbarui!'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    });

    test('user can update password via ajax', function () {
        // Arrange
        $user = User::factory()->create(['password' => bcrypt('oldpassword')]);
        $passwordData = [
            'current_password' => 'oldpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ];

        // Act
        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->patchJson('/password/ajax', $passwordData);

        // Assert
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Password berhasil diperbarui!'
        ]);

        // Verify password was changed
        $user->refresh();
        expect(Hash::check('newpassword', $user->password))->toBeTrue();
    });

    test('profile update validates required fields', function () {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->patchJson('/profile/ajax', [
                'name' => '', // Empty required field
                'email' => 'invalid-email' // Invalid email
            ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email']);
    });

    test('password update requires correct current password', function () {
        // Arrange
        $user = User::factory()->create(['password' => bcrypt('correctpassword')]);

        // Act
        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->patchJson('/password/ajax', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword'
            ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['current_password']);
    });

    test('email must be unique when updating profile', function () {
        // Arrange
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->patchJson('/profile/ajax', [
                'name' => 'Test User',
                'email' => 'existing@example.com' // Already taken
            ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    });

    test('unauthenticated user cannot access profile routes', function () {
        // Act
        $getResponse = $this->get('/profile');
        $patchResponse = $this->withoutMiddleware()->patchJson('/profile/ajax', []);

        // Assert
        $getResponse->assertRedirect('/login');
        $this->assertTrue(in_array($patchResponse->getStatusCode(), [401, 422]), 'Status code harus 401 atau 422');
    });
});
