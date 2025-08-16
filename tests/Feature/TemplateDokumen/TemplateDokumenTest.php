<?php

namespace Tests\Feature\TemplateDokumen;

use Tests\TestCase;
use App\Models\User;
use App\Models\TemplateDokumen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TemplateDokumenTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        Storage::fake('public');
    }

    /** @test */
    public function admin_can_view_template_list()
    {
        $response = $this->actingAs($this->admin)
            ->get('/template-dokumen');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_template()
    {
        $file = UploadedFile::fake()->create('template.docx', 100);

        $templateData = [
            'nama_template' => 'Template SPPD Standar',
            'jenis_dokumen' => 'sppd',
            'deskripsi' => 'Template standar untuk SPPD',
            'file' => $file
        ];

        $response = $this->actingAs($this->admin)
            ->post('/template-dokumen', $templateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('template_dokumen', [
            'nama_template' => 'Template SPPD Standar',
            'jenis_dokumen' => 'sppd',
            'deskripsi' => 'Template standar untuk SPPD'
        ]);
    }

    /** @test */
    public function admin_can_update_template()
    {
        $template = TemplateDokumen::factory()->create([
            'nama_template' => 'Old Template Name'
        ]);

        $updateData = [
            'nama_template' => 'Updated Template Name',
            'deskripsi' => 'Updated description'
        ];

        $response = $this->actingAs($this->admin)
            ->put("/template-dokumen/{$template->id}", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('template_dokumen', [
            'id' => $template->id,
            'nama_template' => 'Updated Template Name',
            'deskripsi' => 'Updated description'
        ]);
    }

    /** @test */
    public function admin_can_delete_template()
    {
        $template = TemplateDokumen::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/template-dokumen/{$template->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('template_dokumen', [
            'id' => $template->id
        ]);
    }

    /** @test */
    public function regular_user_cannot_access_template_routes()
    {
        $regularUser = User::factory()->create();

        $response = $this->actingAs($regularUser)
            ->get('/template-dokumen');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_template_routes()
    {
        $response = $this->get('/template-dokumen');
        $response->assertRedirect('/login');
    }
}
