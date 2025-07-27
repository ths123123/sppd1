<?php

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

describe('Security Basic Tests', function () {

    test('user model hides sensitive data', function () {
        // Arrange
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Act - Test if user model hides sensitive data when serialized
        $userArray = $user->toArray();

        // Assert - Sensitive fields should not be present in array serialization
        expect($userArray)->not()->toHaveKey('password');
        expect($userArray)->not()->toHaveKey('remember_token');
        
        // Should have safe data
        expect($userArray)->toHaveKey('name');
        expect($userArray)->toHaveKey('email');
        expect($userArray)->toHaveKey('id');
    });

    test('user model hides sensitive data in json', function () {
        // Arrange
        $user = User::factory()->create([
            'name' => 'Test User JSON',
            'email' => 'testjson@example.com',
        ]);

        // Act - Test if user model hides sensitive data when converted to JSON
        $userJson = $user->toJson();
        $userData = json_decode($userJson, true);

        // Assert - Sensitive fields should not be present in JSON serialization
        expect($userData)->not()->toHaveKey('password');
        expect($userData)->not()->toHaveKey('remember_token');
        
        // Should have safe data
        expect($userData)->toHaveKey('name');
        expect($userData)->toHaveKey('email');
        expect($userData)->toHaveKey('id');
    });

});
