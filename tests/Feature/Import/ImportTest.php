<?php

namespace Tests\Feature\Import;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;

class ImportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    /** @test */
    public function admin_can_import_users_from_excel()
    {
        $file = UploadedFile::fake()->create('users.xlsx', 100);

        $response = $this->actingAs($this->admin)
            ->post('/import/users', [
                'file' => $file
            ]);

        $response->assertRedirect();
        // Check if users were imported
    }

    /** @test */
    public function admin_can_import_travel_requests_from_excel()
    {
        $file = UploadedFile::fake()->create('travel_requests.xlsx', 100);

        $response = $this->actingAs($this->admin)
            ->post('/import/travel-requests', [
                'file' => $file
            ]);

        $response->assertRedirect();
        // Check if travel requests were imported
    }

    /** @test */
    public function regular_user_cannot_import_data()
    {
        $regularUser = User::factory()->create();
        $file = UploadedFile::fake()->create('users.xlsx', 100);

        $response = $this->actingAs($regularUser)
            ->post('/import/users', [
                'file' => $file
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_import_data()
    {
        $file = UploadedFile::fake()->create('users.xlsx', 100);

        $response = $this->post('/import/users', [
            'file' => $file
        ]);

        $response->assertRedirect('/login');
    }
}
