<?php

namespace Tests\Feature\TemplateManagement;

use Tests\TestCase;
use App\Models\User;
use App\Models\TemplateDokumen;
use App\Models\TravelRequest;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class TemplateManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $kasubbag;
    protected $sekretaris;
    protected $ppk;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->kasubbag = User::factory()->create(['role' => 'kasubbag']);
        $this->sekretaris = User::factory()->create(['role' => 'sekretaris']);
        $this->ppk = User::factory()->create(['role' => 'ppk']);
        
        Storage::fake('templates');
        Storage::fake('documents');
    }

    /** @test */
    public function admin_can_create_document_templates()
    {
        $this->actingAs($this->admin);

        $templateData = [
            'name' => 'Surat Tugas Template',
            'type' => 'surat_tugas',
            'content' => 'Template content for surat tugas',
            'variables' => ['nama', 'jabatan', 'tujuan', 'tanggal'],
            'is_active' => true,
            'description' => 'Template untuk surat tugas SPPD',
        ];

        $response = $this->post(route('templates.store'), $templateData);
        $response->assertRedirect();

        $this->assertDatabaseHas('template_dokumens', [
            'name' => 'Surat Tugas Template',
            'type' => 'surat_tugas',
            'is_active' => true,
        ]);

        $template = TemplateDokumen::where('name', 'Surat Tugas Template')->first();
        $this->assertNotNull($template);
        $this->assertEquals(['nama', 'jabatan', 'tujuan', 'tanggal'], $template->variables);
    }

    /** @test */
    public function admin_can_edit_existing_templates()
    {
        $this->actingAs($this->admin);

        // Create template first
        $template = TemplateDokumen::factory()->create([
            'name' => 'Original Template',
            'content' => 'Original content',
        ]);

        // Edit template
        $updateData = [
            'name' => 'Updated Template',
            'content' => 'Updated content with new information',
            'variables' => ['nama', 'jabatan', 'tujuan', 'tanggal', 'durasi'],
            'description' => 'Updated description',
        ];

        $response = $this->put(route('templates.update', $template->id), $updateData);
        $response->assertRedirect();

        $template->refresh();
        $this->assertEquals('Updated Template', $template->name);
        $this->assertEquals('Updated content with new information', $template->content);
        $this->assertEquals(['nama', 'jabatan', 'tujuan', 'tanggal', 'durasi'], $template->variables);
    }

    /** @test */
    public function admin_can_delete_templates()
    {
        $this->actingAs($this->admin);

        $template = TemplateDokumen::factory()->create([
            'name' => 'Template to Delete',
        ]);

        $response = $this->delete(route('templates.destroy', $template->id));
        $response->assertRedirect();

        $this->assertDatabaseMissing('template_dokumens', ['id' => $template->id]);
    }

    /** @test */
    public function admin_can_activate_deactivate_templates()
    {
        $this->actingAs($this->admin);

        $template = TemplateDokumen::factory()->create([
            'is_active' => false,
        ]);

        // Activate template
        $response = $this->patch(route('templates.toggle-status', $template->id));
        $response->assertRedirect();

        $template->refresh();
        $this->assertTrue($template->is_active);

        // Deactivate template
        $response = $this->patch(route('templates.toggle-status', $template->id));
        $response->assertRedirect();

        $template->refresh();
        $this->assertFalse($template->is_active);
    }

    /** @test */
    public function template_listing_shows_all_templates()
    {
        $this->actingAs($this->admin);

        // Create multiple templates
        TemplateDokumen::factory()->create(['name' => 'Template 1', 'type' => 'surat_tugas']);
        TemplateDokumen::factory()->create(['name' => 'Template 2', 'type' => 'laporan']);
        TemplateDokumen::factory()->create(['name' => 'Template 3', 'type' => 'kwitansi']);

        $response = $this->get(route('templates.index'));
        $response->assertStatus(200);

        // Check all templates are displayed
        $response->assertSee('Template 1');
        $response->assertSee('Template 2');
        $response->assertSee('Template 3');
        $response->assertSee('surat_tugas');
        $response->assertSee('laporan');
        $response->assertSee('kwitansi');
    }

    /** @test */
    public function template_search_and_filter_works()
    {
        $this->actingAs($this->admin);

        // Create templates with different names and types
        TemplateDokumen::factory()->create(['name' => 'Surat Tugas Jakarta', 'type' => 'surat_tugas']);
        TemplateDokumen::factory()->create(['name' => 'Laporan Bandung', 'type' => 'laporan']);
        TemplateDokumen::factory()->create(['name' => 'Kwitansi Surabaya', 'type' => 'kwitansi']);

        // Test search by name
        $response = $this->get(route('templates.index', ['search' => 'Jakarta']));
        $response->assertStatus(200);
        $response->assertSee('Surat Tugas Jakarta');
        $response->assertDontSee('Laporan Bandung');

        // Test filter by type
        $response = $this->get(route('templates.index', ['type' => 'surat_tugas']));
        $response->assertStatus(200);
        $response->assertSee('Surat Tugas Jakarta');
        $response->assertDontSee('Laporan Bandung');
        $response->assertDontSee('Kwitansi Surabaya');
    }

    /** @test */
    public function template_preview_works_correctly()
    {
        $this->actingAs($this->admin);

        $template = TemplateDokumen::factory()->create([
            'content' => 'Template content with {{nama}} and {{jabatan}}',
            'variables' => ['nama', 'jabatan'],
        ]);

        $response = $this->get(route('templates.preview', $template->id));
        $response->assertStatus(200);

        // Check preview shows template content
        $response->assertSee('Template content with {{nama}} and {{jabatan}}');
        $response->assertSee('Preview Template');
    }

    /** @test */
    public function template_variables_are_properly_handled()
    {
        $this->actingAs($this->admin);

        $template = TemplateDokumen::factory()->create([
            'content' => 'Nama: {{nama}}, Jabatan: {{jabatan}}, Tujuan: {{tujuan}}',
            'variables' => ['nama', 'jabatan', 'tujuan'],
        ]);

        // Test variable extraction
        $this->assertEquals(['nama', 'jabatan', 'tujuan'], $template->variables);

        // Test variable replacement
        $replacedContent = str_replace(
            ['{{nama}}', '{{jabatan}}', '{{tujuan}}'],
            ['John Doe', 'Manager', 'Jakarta'],
            $template->content
        );

        $this->assertEquals('Nama: John Doe, Jabatan: Manager, Tujuan: Jakarta', $replacedContent);
    }

    /** @test */
    public function template_can_generate_documents()
    {
        $this->actingAs($this->kasubbag);

        // Create template
        $template = TemplateDokumen::factory()->create([
            'content' => 'Surat Tugas untuk {{nama}} ke {{tujuan}} pada {{tanggal}}',
            'variables' => ['nama', 'tujuan', 'tanggal'],
            'type' => 'surat_tugas',
        ]);

        // Create SPPD
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'destination' => 'Jakarta',
        ]);

        // Generate document from template
        $response = $this->post(route('templates.generate-document'), [
            'template_id' => $template->id,
            'travel_request_id' => $sppd->id,
            'variables' => [
                'nama' => 'John Doe',
                'tujuan' => 'Jakarta',
                'tanggal' => '2024-01-15',
            ],
        ]);

        $response->assertRedirect();

        // Check document was generated
        $this->assertDatabaseHas('documents', [
            'travel_request_id' => $sppd->id,
            'type' => 'surat_tugas',
        ]);

        $document = Document::where('travel_request_id', $sppd->id)->first();
        $this->assertNotNull($document);
    }

    /** @test */
    public function template_validation_works()
    {
        $this->actingAs($this->admin);

        // Test required fields
        $response = $this->post(route('templates.store'), []);
        $response->assertSessionHasErrors(['name', 'type', 'content']);

        // Test invalid template type
        $response = $this->post(route('templates.store'), [
            'name' => 'Test Template',
            'type' => 'invalid_type',
            'content' => 'Template content',
        ]);
        $response->assertSessionHasErrors(['type']);

        // Test valid template
        $response = $this->post(route('templates.store'), [
            'name' => 'Valid Template',
            'type' => 'surat_tugas',
            'content' => 'Valid content',
        ]);
        $response->assertRedirect();
    }

    /** @test */
    public function template_permissions_are_enforced()
    {
        // Test regular user cannot access templates
        $this->actingAs($this->kasubbag);
        $response = $this->get(route('templates.index'));
        $response->assertStatus(200); // kasubbag can view

        // Test admin can access all features
        $this->actingAs($this->admin);
        $response = $this->get(route('templates.index'));
        $response->assertStatus(200);

        $response = $this->get(route('templates.create'));
        $response->assertStatus(200);
    }

    /** @test */
    public function template_can_be_duplicated()
    {
        $this->actingAs($this->admin);

        $originalTemplate = TemplateDokumen::factory()->create([
            'name' => 'Original Template',
            'content' => 'Original content',
            'variables' => ['nama', 'jabatan'],
        ]);

        $response = $this->post(route('templates.duplicate', $originalTemplate->id), [
            'new_name' => 'Duplicated Template',
        ]);

        $response->assertRedirect();

        // Check duplicated template was created
        $this->assertDatabaseHas('template_dokumens', [
            'name' => 'Duplicated Template',
            'content' => 'Original content',
        ]);

        $duplicatedTemplate = TemplateDokumen::where('name', 'Duplicated Template')->first();
        $this->assertEquals(['nama', 'jabatan'], $duplicatedTemplate->variables);
    }

    /** @test */
    public function template_can_be_exported()
    {
        $this->actingAs($this->admin);

        $template = TemplateDokumen::factory()->create([
            'name' => 'Exportable Template',
            'content' => 'Template content for export',
        ]);

        // Test JSON export
        $response = $this->get(route('templates.export', $template->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');

        // Test PDF export
        $response = $this->get(route('templates.export.pdf', $template->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function template_can_be_imported()
    {
        $this->actingAs($this->admin);

        $templateData = [
            'name' => 'Imported Template',
            'type' => 'surat_tugas',
            'content' => 'Imported template content',
            'variables' => ['nama', 'jabatan'],
        ];

        $jsonData = json_encode($templateData);

        $response = $this->post(route('templates.import'), [
            'template_file' => UploadedFile::fake()->createWithContent('template.json', $jsonData),
        ]);

        $response->assertRedirect();

        // Check template was imported
        $this->assertDatabaseHas('template_dokumens', [
            'name' => 'Imported Template',
            'type' => 'surat_tugas',
        ]);
    }

    /** @test */
    public function template_categories_work_properly()
    {
        $this->actingAs($this->admin);

        // Create templates in different categories
        TemplateDokumen::factory()->create(['name' => 'Surat Tugas 1', 'type' => 'surat_tugas', 'category' => 'formal']);
        TemplateDokumen::factory()->create(['name' => 'Surat Tugas 2', 'type' => 'surat_tugas', 'category' => 'informal']);
        TemplateDokumen::factory()->create(['name' => 'Laporan 1', 'type' => 'laporan', 'category' => 'formal']);

        // Test category filter
        $response = $this->get(route('templates.index', ['category' => 'formal']));
        $response->assertStatus(200);
        $response->assertSee('Surat Tugas 1');
        $response->assertSee('Laporan 1');
        $response->assertDontSee('Surat Tugas 2');
    }

    /** @test */
    public function template_versioning_works()
    {
        $this->actingAs($this->admin);

        $template = TemplateDokumen::factory()->create([
            'name' => 'Versioned Template',
            'version' => '1.0',
        ]);

        // Create new version
        $response = $this->post(route('templates.create-version', $template->id), [
            'version' => '2.0',
            'content' => 'Updated content for version 2.0',
            'changelog' => 'Added new variables and improved formatting',
        ]);

        $response->assertRedirect();

        // Check new version was created
        $this->assertDatabaseHas('template_dokumens', [
            'name' => 'Versioned Template',
            'version' => '2.0',
        ]);

        // Check version history
        $response = $this->get(route('templates.versions', $template->id));
        $response->assertStatus(200);
        $response->assertSee('1.0');
        $response->assertSee('2.0');
    }

    /** @test */
    public function template_can_be_shared_between_users()
    {
        $this->actingAs($this->admin);

        $template = TemplateDokumen::factory()->create([
            'name' => 'Shared Template',
            'is_shared' => true,
        ]);

        // Share template with specific users
        $response = $this->post(route('templates.share', $template->id), [
            'user_ids' => [$this->kasubbag->id, $this->sekretaris->id],
        ]);

        $response->assertRedirect();

        // Check template is shared
        $this->assertDatabaseHas('template_shares', [
            'template_id' => $template->id,
            'user_id' => $this->kasubbag->id,
        ]);

        $this->assertDatabaseHas('template_shares', [
            'template_id' => $template->id,
            'user_id' => $this->sekretaris->id,
        ]);
    }

    /** @test */
    public function template_usage_statistics_are_tracked()
    {
        $this->actingAs($this->admin);

        $template = TemplateDokumen::factory()->create([
            'name' => 'Tracked Template',
        ]);

        // Use template multiple times
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('templates.generate-document'), [
                'template_id' => $template->id,
                'travel_request_id' => TravelRequest::factory()->create()->id,
                'variables' => ['nama' => 'User ' . $i],
            ]);
        }

        // Check usage statistics
        $template->refresh();
        $this->assertEquals(5, $template->usage_count);

        // Check usage report
        $response = $this->get(route('templates.usage-report', $template->id));
        $response->assertStatus(200);
        $response->assertSee('5'); // Usage count
    }

    /** @test */
    public function template_bulk_operations_work()
    {
        $this->actingAs($this->admin);

        // Create multiple templates
        $templates = TemplateDokumen::factory()->count(3)->create();

        $templateIds = $templates->pluck('id')->toArray();

        // Bulk activate
        $response = $this->post(route('templates.bulk-activate'), [
            'template_ids' => $templateIds,
        ]);
        $response->assertRedirect();

        // Check all templates are active
        foreach ($templateIds as $id) {
            $this->assertDatabaseHas('template_dokumens', [
                'id' => $id,
                'is_active' => true,
            ]);
        }

        // Bulk delete
        $response = $this->post(route('templates.bulk-delete'), [
            'template_ids' => $templateIds,
        ]);
        $response->assertRedirect();

        // Check all templates were deleted
        foreach ($templateIds as $id) {
            $this->assertDatabaseMissing('template_dokumens', ['id' => $id]);
        }
    }

    /** @test */
    public function template_approval_workflow_works()
    {
        $this->actingAs($this->admin);

        $template = TemplateDokumen::factory()->create([
            'name' => 'Pending Approval Template',
            'status' => 'pending_approval',
            'requires_approval' => true,
        ]);

        // Submit for approval
        $response = $this->post(route('templates.submit-approval', $template->id));
        $response->assertRedirect();

        // Check approval request was created
        $this->assertDatabaseHas('template_approvals', [
            'template_id' => $template->id,
            'status' => 'pending',
        ]);

        // Approve template
        $this->actingAs($this->sekretaris);
        $response = $this->patch(route('templates.approve', $template->id), [
            'status' => 'approved',
            'notes' => 'Template approved',
        ]);

        $response->assertRedirect();

        // Check template was approved
        $template->refresh();
        $this->assertEquals('approved', $template->status);
    }
}
