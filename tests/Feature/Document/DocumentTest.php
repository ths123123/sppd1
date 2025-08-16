<?php

namespace Tests\Feature\Document;

use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use App\Models\TravelRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Storage::fake('public');
    }

    /** @test */
    public function user_can_view_document_list()
    {
        $response = $this->actingAs($this->user)
            ->get('/documents');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_upload_document()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $documentData = [
            'travel_request_id' => $travelRequest->id,
            'nama_dokumen' => 'Surat Tugas',
            'jenis_dokumen' => 'surat_tugas',
            'file' => $file
        ];

        $response = $this->actingAs($this->user)
            ->post('/documents', $documentData);

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'travel_request_id' => $travelRequest->id,
            'nama_dokumen' => 'Surat Tugas',
            'jenis_dokumen' => 'surat_tugas'
        ]);
    }

    /** @test */
    public function user_can_download_document()
    {
        $document = Document::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/documents/{$document->id}/download");

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_delete_own_document()
    {
        $document = Document::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/documents/{$document->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    /** @test */
    public function user_cannot_delete_other_user_document()
    {
        $otherUser = User::factory()->create();
        $document = Document::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/documents/{$document->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('documents', ['id' => $document->id]);
    }

    /** @test */
    public function guest_cannot_access_document_routes()
    {
        $response = $this->get('/documents');
        $response->assertRedirect('/login');
    }
}
