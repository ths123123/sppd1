<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function setting_can_be_created()
    {
        $setting = Setting::factory()->create([
            'key' => 'test_key',
            'value' => 'test_value',
            'label' => 'Test Setting'
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'test_key',
            'label' => 'Test Setting'
        ]);
        
        // Check value separately as it might be JSON encoded
        $this->assertStringContainsString('test_value', $setting->value);
    }

    /** @test */
    public function setting_has_default_type()
    {
        $setting = Setting::factory()->create();

        $this->assertContains($setting->type, ['string', 'number', 'boolean', 'json']);
    }

    /** @test */
    public function setting_can_be_public()
    {
        $setting = Setting::factory()->public()->create();

        $this->assertEquals('general', $setting->group);
        $this->assertTrue($setting->is_editable);
    }

    /** @test */
    public function setting_can_be_private()
    {
        $setting = Setting::factory()->private()->create();

        $this->assertEquals('system', $setting->group);
        $this->assertFalse($setting->is_editable);
    }

    /** @test */
    public function setting_can_have_system_name()
    {
        $setting = Setting::factory()->systemName()->create();

        $this->assertEquals('system_name', $setting->key);
        $this->assertEquals('SPPD KPU System', $setting->value);
        $this->assertEquals('string', $setting->type);
        $this->assertEquals('system', $setting->group);
        $this->assertEquals('Nama Sistem', $setting->label);
    }

    /** @test */
    public function setting_can_have_system_description()
    {
        $setting = Setting::factory()->systemDescription()->create();

        $this->assertEquals('system_description', $setting->key);
        $this->assertEquals('Sistem Pengelolaan Surat Perintah Perjalanan Dinas KPU', $setting->value);
        $this->assertEquals('string', $setting->type);
        $this->assertEquals('system', $setting->group);
        $this->assertEquals('Deskripsi Sistem', $setting->label);
    }
}
