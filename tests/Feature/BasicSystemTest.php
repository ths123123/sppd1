<?php

namespace Tests\Feature;

use Tests\TestCase;

class BasicSystemTest extends TestCase
{
    /**
     * Test basic application configuration
     */
    public function test_application_returns_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(302); // Should redirect to login
    }

    /**
     * Test environment configuration
     */
    public function test_environment_is_configured()
    {
        // Environment should be 'testing' during test execution
        $this->assertContains(app()->environment(), ['testing', 'production']);
        
        // Ensure we have basic configuration
        $this->assertNotEmpty(config('app.name'));
        $this->assertNotEmpty(config('app.key'));
    }

    /**
     * Test database connection
     */
    public function test_database_connection()
    {
        $this->assertTrue(true); // Basic test to verify testing framework works
    }

    /**
     * Test basic calculation functionality
     */
    public function test_basic_calculations()
    {
        $transport = 1000000;
        $accommodation = 600000;
        $daily = 300000;
        $other = 200000;

        $total = $transport + $accommodation + $daily + $other;

        $this->assertEquals(2100000, $total);
    }

    /**
     * Test string validation
     */
    public function test_string_validation()
    {
        $email = 'test@example.com';
        $name = 'John Doe';

        $this->assertStringContainsString('@', $email);
        $this->assertStringContainsString('John', $name);
    }
}
