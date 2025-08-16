<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use App\Models\TravelRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $travelRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function document_can_be_created()
    {
        $document = Document::factory()->create([
            'uploaded_by' => $this->user->id,
            'travel_request_id' => $this->travelRequest->id,
            'filename' => 'Test Document.pdf',
            'document_type' => 'supporting'
        ]);

        $this->assertDatabaseHas('documents', [
            'uploaded_by' => $this->user->id,
            'travel_request_id' => $this->travelRequest->id,
            'filename' => 'Test Document.pdf',
            'document_type' => 'supporting'
        ]);
    }

    /** @test */
    public function document_has_default_type()
    {
        $document = Document::factory()->create();

        $this->assertContains($document->document_type, ['supporting', 'proof', 'receipt', 'photo', 'report', 'generated_pdf']);
    }

    /** @test */
    public function document_is_unverified_by_default()
    {
        $document = Document::factory()->create();

        $this->assertFalse($document->is_verified);
        $this->assertNull($document->verified_at);
        $this->assertNull($document->verified_by);
    }

    /** @test */
    public function document_can_be_verified()
    {
        $verifier = User::factory()->approver()->create();
        $document = Document::factory()->create([
            'uploaded_by' => $this->user->id,
            'is_verified' => false
        ]);

        $document->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $verifier->id
        ]);

        $this->assertTrue($document->fresh()->is_verified);
        $this->assertNotNull($document->fresh()->verified_at);
        $this->assertEquals($verifier->id, $document->fresh()->verified_by);
    }

    /** @test */
    public function document_can_have_supporting_type()
    {
        $document = Document::factory()->supporting()->create([
            'uploaded_by' => $this->user->id
        ]);

        $this->assertEquals('supporting', $document->document_type);
        $this->assertStringContainsString('Dokumen Pendukung', $document->description);
    }

    /** @test */
    public function document_can_have_proof_type()
    {
        $document = Document::factory()->proof()->create([
            'uploaded_by' => $this->user->id
        ]);

        $this->assertEquals('proof', $document->document_type);
        $this->assertStringContainsString('Bukti Perjalanan', $document->description);
    }

    /** @test */
    public function document_can_have_receipt_type()
    {
        $document = Document::factory()->receipt()->create([
            'uploaded_by' => $this->user->id
        ]);

        $this->assertEquals('receipt', $document->document_type);
        $this->assertStringContainsString('Kwitansi', $document->description);
    }

    /** @test */
    public function document_belongs_to_user()
    {
        $document = Document::factory()->create([
            'uploaded_by' => $this->user->id
        ]);

        $this->assertInstanceOf(User::class, $document->uploader);
        $this->assertEquals($this->user->id, $document->uploader->id);
    }

    /** @test */
    public function document_belongs_to_travel_request()
    {
        $document = Document::factory()->create([
            'travel_request_id' => $this->travelRequest->id
        ]);

        $this->assertInstanceOf(TravelRequest::class, $document->travelRequest);
        $this->assertEquals($this->travelRequest->id, $document->travelRequest->id);
    }
}
