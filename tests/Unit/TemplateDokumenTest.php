<?php

namespace Tests\TestCase;

use Tests\TestCase;
use App\Models\TemplateDokumen;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TemplateDokumenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function template_can_be_created()
    {
        $template = TemplateDokumen::factory()->create([
            'nama_template' => 'Test Template',
            'jenis_template' => 'sppd'
        ]);

        $this->assertDatabaseHas('template_dokumens', [
            'nama_template' => 'Test Template',
            'jenis_template' => 'sppd'
        ]);
    }

    /** @test */
    public function template_has_default_type()
    {
        $template = TemplateDokumen::factory()->create();

        $this->assertContains($template->jenis_template, ['spd', 'sppd', 'laporan_akhir']);
    }

    /** @test */
    public function template_is_active_by_default()
    {
        $template = TemplateDokumen::factory()->create();

        $this->assertTrue($template->status_aktif);
    }

    /** @test */
    public function template_can_have_sppd_type()
    {
        $template = TemplateDokumen::factory()->sppd()->create();

        $this->assertEquals('sppd', $template->jenis_template);
        $this->assertStringContainsString('Template SPPD', $template->nama_template);
    }

    /** @test */
    public function template_can_have_spd_type()
    {
        $template = TemplateDokumen::factory()->spd()->create();

        $this->assertEquals('spd', $template->jenis_template);
        $this->assertStringContainsString('Template SPD', $template->nama_template);
    }

    /** @test */
    public function template_can_have_laporan_akhir_type()
    {
        $template = TemplateDokumen::factory()->laporanAkhir()->create();

        $this->assertEquals('laporan_akhir', $template->jenis_template);
        $this->assertStringContainsString('Template Laporan Akhir', $template->nama_template);
    }

    /** @test */
    public function template_can_be_active()
    {
        $template = TemplateDokumen::factory()->active()->create();

        $this->assertTrue($template->status_aktif);
    }

    /** @test */
    public function template_can_be_inactive()
    {
        $template = TemplateDokumen::factory()->inactive()->create();

        $this->assertFalse($template->status_aktif);
    }
}
