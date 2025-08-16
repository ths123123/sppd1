<?php

namespace Tests\Feature\DocumentManagement;

use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use App\Models\TravelRequest;
use App\Models\TemplateDokumen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class DocumentManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $kasubbag;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->kasubbag = User::factory()->create(['role' => 'kasubbag']);
        $this->regularUser = User::factory()->create(['role' => 'user']);
        
        Storage::fake('documents');
        Storage::fake('templates');
    }

    /** @test */
    public function admin_can_manage_document_templates()
    {
        $this->actingAs($this->admin);

        // Create template
        $templateData = [
            'name' => 'Surat Tugas Template',
            'type' => 'surat_tugas',
            'content' => 'Template content here',
            'is_active' => true,
        ];

        $response = $this->post(route('templates.store'), $templateData);
        $response->assertRedirect();

        $this->assertDatabaseHas('template_dokumens', [
            'name' => 'Surat Tugas Template',
            'type' => 'surat_tugas',
        ]);

        // List templates
        $response = $this->get(route('templates.index'));
        $response->assertStatus(200);
        $response->assertSee('Surat Tugas Template');

        // Edit template
        $template = TemplateDokumen::where('name', 'Surat Tugas Template')->first();
        $response = $this->put(route('templates.update', $template->id), [
            'name' => 'Updated Template',
            'content' => 'Updated content',
        ]);
        $response->assertRedirect();

        // Delete template
        $response = $this->delete(route('templates.destroy', $template->id));
        $response->assertRedirect();

        $this->assertDatabaseMissing('template_dokumens', ['id' => $template->id]);
    }

    /** @test */
    public function kasubbag_can_upload_documents_to_sppd()
    {
        $this->actingAs($this->kasubbag);

        // Create SPPD first
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
        ]);

        // Upload document
        $file = UploadedFile::fake()->create('surat_tugas.pdf', 100);
        
        $response = $this->post(route('documents.store'), [
            'travel_request_id' => $sppd->id,
            'document' => $file,
            'type' => 'surat_tugas',
            'description' => 'Surat Tugas untuk SPPD',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'travel_request_id' => $sppd->id,
            'type' => 'surat_tugas',
            'filename' => 'surat_tugas.pdf',
        ]);

        // Check file was stored
        Storage::disk('documents')->assertExists('surat_tugas.pdf');
    }

    /** @test */
    public function users_can_view_their_own_documents()
    {
        $this->actingAs($this->regularUser);

        // Create SPPD and document
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->regularUser->id,
        ]);

        $document = Document::factory()->create([
            'travel_request_id' => $sppd->id,
            'user_id' => $this->regularUser->id,
            'type' => 'surat_tugas',
        ]);

        // View documents
        $response = $this->get(route('documents.index'));
        $response->assertStatus(200);
        $response->assertSee($document->filename);
    }

    /** @test */
    public function document_upload_validates_file_types()
    {
        $this->actingAs($this->kasubbag);

        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
        ]);

        // Test invalid file type
        $invalidFile = UploadedFile::fake()->create('document.exe', 100);
        
        $response = $this->post(route('documents.store'), [
            'travel_request_id' => $sppd->id,
            'document' => $invalidFile,
            'type' => 'surat_tugas',
        ]);

        $response->assertSessionHasErrors(['document']);

        // Test valid file type
        $validFile = UploadedFile::fake()->create('document.pdf', 100);
        
        $response = $this->post(route('documents.store'), [
            'travel_request_id' => $sppd->id,
            'document' => $validFile,
            'type' => 'surat_tugas',
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function document_upload_validates_file_size()
    {
        $this->actingAs($this->kasubbag);

        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
        ]);

        // Test file too large (10MB)
        $largeFile = UploadedFile::fake()->create('large_document.pdf', 10240);
        
        $response = $this->post(route('documents.store'), [
            'travel_request_id' => $sppd->id,
            'document' => $largeFile,
            'type' => 'surat_tugas',
        ]);

        $response->assertSessionHasErrors(['document']);

        // Test valid file size
        $validFile = UploadedFile::fake()->create('valid_document.pdf', 100);
        
        $response = $this->post(route('documents.store'), [
            'travel_request_id' => $sppd->id,
            'document' => $validFile,
            'type' => 'surat_tugas',
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function documents_can_be_downloaded_by_authorized_users()
    {
        $this->actingAs($this->kasubbag);

        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
        ]);

        $document = Document::factory()->create([
            'travel_request_id' => $sppd->id,
            'user_id' => $this->kasubbag->id,
            'filename' => 'test_document.pdf',
        ]);

        // Mock file content
        Storage::disk('documents')->put('test_document.pdf', 'fake pdf content');

        // Download document
        $response = $this->get(route('documents.download', $document->id));
        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=test_document.pdf');
    }

    /** @test */
    public function unauthorized_users_cannot_download_documents()
    {
        $this->actingAs($this->regularUser);

        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
        ]);

        $document = Document::factory()->create([
            'travel_request_id' => $sppd->id,
            'user_id' => $this->kasubbag->id,
        ]);

        // Try to download document
        $response = $this->get(route('documents.download', $document->id));
        $response->assertStatus(403);
    }

    /** @test */
    public function documents_can_be_deleted_by_owners()
    {
        $this->actingAs($this->kasubbag);

        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
        ]);

        $document = Document::factory()->create([
            'travel_request_id' => $sppd->id,
            'user_id' => $this->kasubbag->id,
            'filename' => 'to_delete.pdf',
        ]);

        // Mock file
        Storage::disk('documents')->put('to_delete.pdf', 'content');

        // Delete document
        $response = $this->delete(route('documents.destroy', $document->id));
        $response->assertRedirect();

        // Check document was deleted
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
        
        // Check file was deleted
        Storage::disk('documents')->assertMissing('to_delete.pdf');
    }

    /** @test */
    public function document_types_are_properly_categorized()
    {
        $this->actingAs($this->kasubbag);

        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
        ]);

        // Upload different document types
        $documentTypes = [
            'surat_tugas' => 'Surat Tugas',
            'laporan' => 'Laporan Perjalanan',
            'kwitansi' => 'Kwitansi',
            'other' => 'Dokumen Lainnya',
        ];

        foreach ($documentTypes as $type => $description) {
            $file = UploadedFile::fake()->create("document_{$type}.pdf", 100);
            
            $response = $this->post(route('documents.store'), [
                'travel_request_id' => $sppd->id,
                'document' => $file,
                'type' => $type,
                'description' => $description,
            ]);
        }

        // Check all documents were created
        $this->assertDatabaseCount('documents', 4);

        // Check document listing shows proper categorization
        $response = $this->get(route('documents.index'));
        $response->assertStatus(200);
        $response->assertSee('Surat Tugas');
        $response->assertSee('Laporan Perjalanan');
        $response->assertSee('Kwitansi');
        $response->assertSee('Dokumen Lainnya');
    }

    /** @test */
    public function document_search_and_filter_works()
    {
        $this->actingAs($this->admin);

        // Create documents with different types and descriptions
        $sppd = TravelRequest::factory()->create();
        
        Document::factory()->create([
            'travel_request_id' => $sppd->id,
            'type' => 'surat_tugas',
            'description' => 'Surat Tugas Jakarta',
        ]);

        Document::factory()->create([
            'travel_request_id' => $sppd->id,
            'type' => 'laporan',
            'description' => 'Laporan Bandung',
        ]);

        Document::factory()->create([
            'travel_request_id' => $sppd->id,
            'type' => 'kwitansi',
            'description' => 'Kwitansi Surabaya',
        ]);

        // Test search by description
        $response = $this->get(route('documents.index', ['search' => 'Jakarta']));
        $response->assertStatus(200);
        $response->assertSee('Surat Tugas Jakarta');
        $response->assertDontSee('Laporan Bandung');

        // Test filter by type
        $response = $this->get(route('documents.index', ['type' => 'surat_tugas']));
        $response->assertStatus(200);
        $response->assertSee('Surat Tugas Jakarta');
        $response->assertDontSee('Laporan Bandung');
        $response->assertDontSee('Kwitansi Surabaya');
    }

    /** @test */
    public function document_metadata_is_properly_stored()
    {
        $this->actingAs($this->kasubbag);

        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
        ]);

        $file = UploadedFile::fake()->create('metadata_test.pdf', 150);
        
        $response = $this->post(route('documents.store'), [
            'travel_request_id' => $sppd->id,
            'document' => $file,
            'type' => 'surat_tugas',
            'description' => 'Test document with metadata',
            'notes' => 'Additional notes for this document',
        ]);

        $response->assertRedirect();

        $document = Document::where('filename', 'metadata_test.pdf')->first();
        
        $this->assertNotNull($document);
        $this->assertEquals('metadata_test.pdf', $document->filename);
        $this->assertEquals('surat_tugas', $document->type);
        $this->assertEquals('Test document with metadata', $document->description);
        $this->assertEquals('Additional notes for this document', $document->notes);
        $this->assertEquals(150, $document->file_size);
        $this->assertEquals('application/pdf', $document->mime_type);
    }

    /** @test */
    public function document_versioning_works_properly()
    {
        $this->actingAs($this->kasubbag);

        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
        ]);

        // Upload first version
        $file1 = UploadedFile::fake()->create('document_v1.pdf', 100);
        
        $response = $this->post(route('documents.store'), [
            'travel_request_id' => $sppd->id,
            'document' => $file1,
            'type' => 'surat_tugas',
            'version' => '1.0',
        ]);

        $document1 = Document::where('filename', 'document_v1.pdf')->first();
        
        // Upload updated version
        $file2 = UploadedFile::fake()->create('document_v2.pdf', 120);
        
        $response = $this->post(route('documents.store'), [
            'travel_request_id' => $sppd->id,
            'document' => $file2,
            'type' => 'surat_tugas',
            'version' => '2.0',
            'replaces_document_id' => $document1->id,
        ]);

        $response->assertRedirect();

        // Check versioning relationship
        $document2 = Document::where('filename', 'document_v2.pdf')->first();
        $this->assertEquals($document1->id, $document2->replaces_document_id);
        
        // Check document count
        $this->assertDatabaseCount('documents', 2);
    }

    /** @test */
    public function document_approval_workflow_integration()
    {
        $this->actingAs($this->kasubbag);

        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
        ]);

        // Upload document
        $file = UploadedFile::fake()->create('approval_document.pdf', 100);
        
        $response = $this->post(route('documents.store'), [
            'travel_request_id' => $sppd->id,
            'document' => $file,
            'type' => 'surat_tugas',
            'requires_approval' => true,
        ]);

        $document = Document::where('filename', 'approval_document.pdf')->first();
        $this->assertTrue($document->requires_approval);

        // Submit for approval
        $response = $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // Check approval process
        $this->actingAs($this->sekretaris);
        $response = $this->get(route('approval.pimpinan.index'));
        $response->assertStatus(200);
        $response->assertSee('approval_document.pdf');
    }

    /** @test */
    public function document_export_functionality()
    {
        $this->actingAs($this->admin);

        // Create documents
        $sppd = TravelRequest::factory()->create();
        
        Document::factory()->count(5)->create([
            'travel_request_id' => $sppd->id,
            'type' => 'surat_tugas',
        ]);

        // Test PDF export
        $response = $this->get(route('documents.export.pdf'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        // Test Excel export
        $response = $this->get(route('documents.export.excel'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function document_bulk_operations()
    {
        $this->actingAs($this->admin);

        $sppd = TravelRequest::factory()->create();
        
        $documents = Document::factory()->count(3)->create([
            'travel_request_id' => $sppd->id,
        ]);

        $documentIds = $documents->pluck('id')->toArray();

        // Bulk delete
        $response = $this->post(route('documents.bulk-delete'), [
            'document_ids' => $documentIds,
        ]);

        $response->assertRedirect();

        // Check all documents were deleted
        foreach ($documentIds as $id) {
            $this->assertDatabaseMissing('documents', ['id' => $id]);
        }

        // Bulk export
        $documents = Document::factory()->count(3)->create([
            'travel_request_id' => $sppd->id,
        ]);

        $response = $this->post(route('documents.bulk-export'), [
            'document_ids' => $documents->pluck('id')->toArray(),
            'format' => 'pdf',
        ]);

        $response->assertStatus(200);
    }
}
