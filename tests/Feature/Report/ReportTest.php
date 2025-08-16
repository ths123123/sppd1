<?php

namespace Tests\Feature\Report;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ReportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function user_can_view_report_list()
    {
        $response = $this->actingAs($this->user)
            ->get('/laporan');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_view_summary_report()
    {
        $response = $this->actingAs($this->user)
            ->get('/laporan/rekapitulasi');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_export_report_to_excel()
    {
        $response = $this->actingAs($this->user)
            ->get('/laporan/export/excel/rekapitulasi');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function user_can_export_report_to_pdf()
    {
        $response = $this->actingAs($this->user)
            ->get('/laporan/export/pdf/rekapitulasi');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function guest_cannot_access_report_routes()
    {
        $response = $this->get('/laporan');
        $response->assertRedirect('/login');

        $response = $this->get('/laporan/rekapitulasi');
        $response->assertRedirect('/login');
    }
}
