<?php

namespace Tests\Feature\Template;

use Tests\TestCase;
use App\Models\User;
use App\Models\TemplateDokumen;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class TemplateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();
    }

    public function test_admin_can_view_template_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/templates');

        $response->assertStatus(200);
        $response->assertViewIs('admin.templates.index');
    }

    public function test_regular_user_cannot_access_template_management()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/templates');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_template_list()
    {
        // Create some test templates
        TemplateDokumen::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/templates/list');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    public function test_admin_can_create_template()
    {
        $templateData = [
            'nama_template' => 'Template SPPD Standard',
            'jenis_dokumen' => 'sppd',
            'deskripsi' => 'Template standar untuk Surat Perintah Perjalanan Dinas',
            'is_active' => true,
            'kategori' => 'official',
            'versi' => '1.0'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/templates', $templateData);

        $response->assertStatus(201);
        
        // Verify template was created
        $this->assertDatabaseHas('template_dokumen', [
            'nama_template' => 'Template SPPD Standard',
            'jenis_dokumen' => 'sppd',
            'is_active' => true
        ]);

        // Verify activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'template_created'
        ]);
    }

    public function test_admin_can_upload_template_file()
    {
        Storage::fake('templates');

        $file = UploadedFile::fake()->create('template_sppd.docx', 1024);

        $templateData = [
            'nama_template' => 'Template SPPD dengan File',
            'jenis_dokumen' => 'sppd',
            'deskripsi' => 'Template dengan file dokumen',
            'template_file' => $file,
            'is_active' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/templates', $templateData);

        $response->assertStatus(201);
        
        // Verify file was uploaded
        Storage::disk('templates')->assertExists($file->hashName());
        
        // Verify template was created with file info
        $this->assertDatabaseHas('template_dokumen', [
            'nama_template' => 'Template SPPD dengan File',
            'file_path' => $file->hashName()
        ]);
    }

    public function test_admin_can_update_template()
    {
        $template = TemplateDokumen::factory()->create([
            'nama_template' => 'Template Lama',
            'is_active' => false
        ]);

        $updateData = [
            'nama_template' => 'Template Baru',
            'deskripsi' => 'Deskripsi yang diperbarui',
            'is_active' => true
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/templates/{$template->id}", $updateData);

        $response->assertStatus(200);
        
        // Verify template was updated
        $template->refresh();
        $this->assertEquals('Template Baru', $template->nama_template);
        $this->assertEquals('Deskripsi yang diperbarui', $template->deskripsi);
        $this->assertTrue($template->is_active);

        // Verify activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'template_updated'
        ]);
    }

    public function test_admin_can_delete_template()
    {
        $template = TemplateDokumen::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/templates/{$template->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify template was deleted
        $this->assertDatabaseMissing('template_dokumen', ['id' => $template->id]);

        // Verify activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'template_deleted'
        ]);
    }

    public function test_admin_can_activate_template()
    {
        $template = TemplateDokumen::factory()->inactive()->create();

        $response = $this->actingAs($this->admin)
            ->patch("/admin/templates/{$template->id}/activate");

        $response->assertStatus(200);
        
        // Verify template was activated
        $template->refresh();
        $this->assertTrue($template->is_active);

        // Verify activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'template_activated'
        ]);
    }

    public function test_admin_can_deactivate_template()
    {
        $template = TemplateDokumen::factory()->active()->create();

        $response = $this->actingAs($this->admin)
            ->patch("/admin/templates/{$template->id}/deactivate");

        $response->assertStatus(200);
        
        // Verify template was deactivated
        $template->refresh();
        $this->assertFalse($template->is_active);

        // Verify activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'template_deactivated'
        ]);
    }

    public function test_admin_can_download_template()
    {
        Storage::fake('templates');
        
        $template = TemplateDokumen::factory()->create([
            'file_path' => 'template_test.docx'
        ]);

        // Create fake file
        Storage::disk('templates')->put('template_test.docx', 'template content');

        $response = $this->actingAs($this->admin)
            ->get("/admin/templates/{$template->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/octet-stream');
        
        // Verify download was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'template_downloaded'
        ]);
    }

    public function test_admin_can_preview_template()
    {
        $template = TemplateDokumen::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/templates/{$template->id}/preview");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'template_info',
            'preview_data',
            'rendered_content'
        ]);
    }

    public function test_admin_can_duplicate_template()
    {
        $template = TemplateDokumen::factory()->create([
            'nama_template' => 'Template Original',
            'deskripsi' => 'Template yang akan diduplikasi'
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/templates/{$template->id}/duplicate");

        $response->assertStatus(201);
        
        // Verify template was duplicated
        $this->assertDatabaseHas('template_dokumen', [
            'nama_template' => 'Template Original (Copy)',
            'deskripsi' => 'Template yang akan diduplikasi'
        ]);

        // Verify activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'template_duplicated'
        ]);
    }

    public function test_admin_can_manage_template_categories()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/templates/categories');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'categories',
            'category_counts',
            'category_hierarchy'
        ]);
    }

    public function test_admin_can_create_template_category()
    {
        $categoryData = [
            'nama_kategori' => 'Dokumen Resmi',
            'deskripsi' => 'Kategori untuk dokumen resmi',
            'parent_id' => null,
            'is_active' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/templates/categories', $categoryData);

        $response->assertStatus(201);
        
        // Verify category was created
        $this->assertDatabaseHas('template_kategori', [
            'nama_kategori' => 'Dokumen Resmi',
            'is_active' => true
        ]);
    }

    public function test_admin_can_manage_template_versions()
    {
        $template = TemplateDokumen::factory()->create(['versi' => '1.0']);

        $response = $this->actingAs($this->admin)
            ->get("/admin/templates/{$template->id}/versions");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_version',
            'version_history',
            'version_comparison'
        ]);
    }

    public function test_admin_can_create_template_version()
    {
        $template = TemplateDokumen::factory()->create(['versi' => '1.0']);

        $versionData = [
            'versi' => '2.0',
            'deskripsi_perubahan' => 'Perbaikan format dan layout',
            'is_active' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/templates/{$template->id}/versions", $versionData);

        $response->assertStatus(201);
        
        // Verify new version was created
        $this->assertDatabaseHas('template_dokumen', [
            'nama_template' => $template->nama_template,
            'versi' => '2.0'
        ]);
    }

    public function test_admin_can_manage_template_permissions()
    {
        $template = TemplateDokumen::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/templates/{$template->id}/permissions");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'template_permissions',
            'user_permissions',
            'role_permissions',
            'inherited_permissions'
        ]);
    }

    public function test_admin_can_set_template_permissions()
    {
        $template = TemplateDokumen::factory()->create();

        $permissionData = [
            'user_id' => $this->user->id,
            'can_view' => true,
            'can_download' => true,
            'can_edit' => false
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/templates/{$template->id}/permissions", $permissionData);

        $response->assertStatus(200);
        
        // Verify permissions were set
        $this->assertDatabaseHas('template_permissions', [
            'template_id' => $template->id,
            'user_id' => $this->user->id,
            'can_view' => true,
            'can_download' => true,
            'can_edit' => false
        ]);
    }

    public function test_admin_can_manage_template_variables()
    {
        $template = TemplateDokumen::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/templates/{$template->id}/variables");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'template_variables',
            'variable_types',
            'variable_validation',
            'variable_examples'
        ]);
    }

    public function test_admin_can_add_template_variable()
    {
        $template = TemplateDokumen::factory()->create();

        $variableData = [
            'nama_variabel' => 'nama_pegawai',
            'tipe_variabel' => 'string',
            'deskripsi' => 'Nama lengkap pegawai',
            'required' => true,
            'default_value' => null
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/templates/{$template->id}/variables", $variableData);

        $response->assertStatus(201);
        
        // Verify variable was added
        $this->assertDatabaseHas('template_variables', [
            'template_id' => $template->id,
            'nama_variabel' => 'nama_pegawai',
            'tipe_variabel' => 'string',
            'required' => true
        ]);
    }

    public function test_admin_can_manage_template_layouts()
    {
        $template = TemplateDokumen::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/templates/{$template->id}/layouts");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_layout',
            'available_layouts',
            'layout_preview',
            'layout_customization'
        ]);
    }

    public function test_admin_can_change_template_layout()
    {
        $template = TemplateDokumen::factory()->create(['layout' => 'default']);

        $layoutData = [
            'layout' => 'modern',
            'customization' => [
                'header_style' => 'centered',
                'font_family' => 'Arial',
                'color_scheme' => 'blue'
            ]
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/templates/{$template->id}/layout", $layoutData);

        $response->assertStatus(200);
        
        // Verify layout was changed
        $template->refresh();
        $this->assertEquals('modern', $template->layout);
    }

    public function test_admin_can_manage_template_workflows()
    {
        $template = TemplateDokumen::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/templates/{$template->id}/workflows");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'workflow_steps',
            'approval_flow',
            'workflow_triggers',
            'workflow_conditions'
        ]);
    }

    public function test_admin_can_set_template_workflow()
    {
        $template = TemplateDokumen::factory()->create();

        $workflowData = [
            'workflow_steps' => [
                [
                    'step' => 1,
                    'action' => 'draft',
                    'approver_role' => 'user',
                    'required' => true
                ],
                [
                    'step' => 2,
                    'action' => 'review',
                    'approver_role' => 'approver',
                    'required' => true
                ],
                [
                    'step' => 3,
                    'action' => 'approve',
                    'approver_role' => 'admin',
                    'required' => true
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/templates/{$template->id}/workflows", $workflowData);

        $response->assertStatus(200);
        
        // Verify workflow was set
        $this->assertDatabaseHas('template_workflows', [
            'template_id' => $template->id,
            'step' => 1,
            'action' => 'draft'
        ]);
    }

    public function test_admin_can_manage_template_notifications()
    {
        $template = TemplateDokumen::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/templates/{$template->id}/notifications");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'notification_settings',
            'notification_triggers',
            'notification_templates',
            'notification_channels'
        ]);
    }

    public function test_admin_can_set_template_notifications()
    {
        $template = TemplateDokumen::factory()->create();

        $notificationData = [
            'notify_on_creation' => true,
            'notify_on_approval' => true,
            'notify_on_completion' => true,
            'notification_channels' => ['email', 'in_app'],
            'custom_message' => 'Template {template_name} telah {action}'
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/templates/{$template->id}/notifications", $notificationData);

        $response->assertStatus(200);
        
        // Verify notifications were set
        $this->assertDatabaseHas('template_notifications', [
            'template_id' => $template->id,
            'notify_on_creation' => true,
            'notify_on_approval' => true
        ]);
    }

    public function test_admin_can_manage_template_analytics()
    {
        $template = TemplateDokumen::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/templates/{$template->id}/analytics");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'usage_statistics',
            'popularity_metrics',
            'user_feedback',
            'performance_metrics'
        ]);
    }

    public function test_admin_can_export_template_analytics()
    {
        $template = TemplateDokumen::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/templates/{$template->id}/analytics/export");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        
        // Verify export was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'template_analytics_exported'
        ]);
    }

    public function test_admin_can_manage_template_backups()
    {
        $template = TemplateDokumen::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/templates/{$template->id}/backups");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'backup_history',
            'backup_schedule',
            'backup_retention',
            'backup_verification'
        ]);
    }

    public function test_admin_can_create_template_backup()
    {
        $template = TemplateDokumen::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post("/admin/templates/{$template->id}/backup");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify backup was created
        $this->assertDatabaseHas('template_backups', [
            'template_id' => $template->id,
            'created_by' => $this->admin->id
        ]);

        // Verify activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'template_backup_created'
        ]);
    }

    public function test_admin_can_restore_template_from_backup()
    {
        $template = TemplateDokumen::factory()->create(['versi' => '2.0']);
        $backup = DB::table('template_backups')->insertGetId([
            'template_id' => $template->id,
            'backup_data' => json_encode(['versi' => '1.0']),
            'created_by' => $this->admin->id,
            'created_at' => now()
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/templates/{$template->id}/restore/{$backup}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify template was restored
        $template->refresh();
        $this->assertEquals('1.0', $template->versi);

        // Verify activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'template_restored'
        ]);
    }
}
