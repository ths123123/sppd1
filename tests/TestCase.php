<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Symfony\Component\Console\Input\InputInterface;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the InputInterface to prevent interactive prompts
        $this->instance(InputInterface::class, Mockery::mock(InputInterface::class, function ($mock) {
            $mock->shouldReceive('isInteractive')->andReturn(false);
        }));

        // Bypass CSRF middleware for all tests
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    /**
     * Refresh the in-memory database.
     *
     * @return void
     */
    protected function refreshDatabase()
    {
        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true, '--no-interaction' => true]);
    }
}
