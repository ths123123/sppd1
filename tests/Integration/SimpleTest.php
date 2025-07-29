<?php

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(TestCase::class, RefreshDatabase::class);

describe('Simple Integration Test', function () {
    test('user can access profile edit page', function () {
        // Create roles first
        $this->artisan('db:seed', ['--class' => 'UserRoleSeeder']);

        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get('/profile');

        // Assert
        $response->assertOk();
        $response->assertViewIs('profile.show');
    });

    test('user can update profile data via ajax', function () {
        // Create roles first
        $this->artisan('db:seed', ['--class' => 'UserRoleSeeder']);

        // Arrange
        $user = User::factory()->create();
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'nip' => '198402132009121004', // valid 18 digit NIP
            'jabatan' => 'Updated Jabatan',
            'unit_kerja' => 'Updated Unit',
            'phone' => '081234567891',
            'pangkat' => 'Updated Pangkat',
            'golongan' => 'III/c',
            'address' => 'Updated Address',
            'bio' => 'Updated Bio',
            'department' => 'Updated Department',
            'employee_id' => 'EMP124',
            'birth_date' => '1991-01-01',
            'gender' => 'female',
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
    });

    test('user can update password via ajax', function () {
        // Create roles first
        $this->artisan('db:seed', ['--class' => 'UserRoleSeeder']);

        // Arrange
        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);
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
    });
});
