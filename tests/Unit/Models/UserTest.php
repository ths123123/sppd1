<?php

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(TestCase::class);

describe('User Model', function () {

    beforeEach(function () {
        // Seed roles for Spatie permission testing
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'staff']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'kasubbag']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'sekretaris']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'ppk']);
    });

    test('it has travel requests relationship', function () {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        expect($user->travelRequests())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    test('it can check if user is admin', function () {
        // Arrange
        $admin = User::factory()->create(['role' => 'kasubbag']);
        $user = User::factory()->create(['role' => 'staff']);

        // Act & Assert
        expect($admin->isAdmin())->toBeTrue();
        expect($user->isAdmin())->toBeFalse();
    });

    test('it can check if user can approve', function () {
        // Arrange
        $kasubbag = User::factory()->create(['role' => 'kasubbag']);
        $sekretaris = User::factory()->create(['role' => 'sekretaris']);
        $ppk = User::factory()->create(['role' => 'ppk']);
        $staff = User::factory()->create(['role' => 'staff']);

        // Act & Assert
        expect($kasubbag->canApprove())->toBeTrue();
        expect($sekretaris->canApprove())->toBeTrue();
        expect($ppk->canApprove())->toBeTrue();
        expect($staff->canApprove())->toBeFalse();
    });

    test('it has correct role scope', function () {
        // Arrange
        User::query()->delete(); // Clear existing users for this test
        User::factory()->create(['role' => 'kasubbag']);
        User::factory()->create(['role' => 'staff']);
        User::factory()->create(['role' => 'kasubbag']);

        // Act
        $admins = User::role('kasubbag')->get();

        // Assert
        expect($admins)->toHaveCount(2);
        expect($admins->first()->role)->toBe('kasubbag');
    });

    test('it can get active users', function () {
        // Arrange
        User::query()->delete(); // Clear existing users for this test
        User::factory()->create(['is_active' => true]);
        User::factory()->create(['is_active' => false]);
        User::factory()->create(['is_active' => true]);

        // Act
        $activeUsers = User::active()->get();

        // Assert
        expect($activeUsers)->toHaveCount(2);
        expect($activeUsers->first()->is_active)->toBeTrue();
    });

    test('it has full name accessor', function () {
        // Arrange
        $user = User::factory()->create(['name' => 'John Doe']);

        // Act & Assert
        expect($user->full_name)->toBe('John Doe');
    });

    test('it can get role display name', function () {
        $kasubbag = User::factory()->create(['role' => 'kasubbag', 'nip' => '198402132009121001']);
        $staff = User::factory()->create(['role' => 'staff', 'nip' => '198402132009121002']);

        // Act & Assert
        expect($kasubbag->getRoleDisplayName())->toBe('Kepala Sub Bagian');
        expect($staff->getRoleDisplayName())->toBe('Staff');
    });

    test('it hides password in array', function () {
        // Arrange
        $user = User::factory()->create();

        // Act
        $array = $user->toArray();

        // Assert
        expect($array)->not()->toHaveKey('password');
        expect($array)->not()->toHaveKey('remember_token');
    });

    test('it has correct fillable attributes', function () {
        // Arrange
        $fillable = [
            'name',
            'email',
            'nip',
            'jabatan',
            'role',
            'phone',
            'address',
            'pangkat',
            'golongan',
            'unit_kerja',
            'is_active',
            'last_login_at',
            'email_verified_at',
            'remember_token',
            // Profile fields
            'avatar',
            'bio',
            'department',
            'employee_id',
            'birth_date',
            'gender',
            'password',
        ];

        // Act
        $model = new User();

        // Assert
        expect($model->getFillable())->toEqual($fillable);
    });
});
