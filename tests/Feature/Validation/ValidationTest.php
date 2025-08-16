<?php

namespace Tests\Feature\Validation;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
    }

    /** @test */
    public function travel_request_requires_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', []);

        $response->assertSessionHasErrors([
            'tujuan',
            'keperluan',
            'tanggal_berangkat',
            'tanggal_kembali',
            'transportasi',
            'estimasi_biaya'
        ]);
    }

    /** @test */
    public function travel_request_tujuan_must_be_string()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 123,
                'keperluan' => 'Test Purpose',
                'tanggal_berangkat' => '2024-02-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 2000000
            ]);

        $response->assertSessionHasErrors(['tujuan']);
    }

    /** @test */
    public function travel_request_tujuan_must_not_exceed_max_length()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => str_repeat('a', 256),
                'keperluan' => 'Test Purpose',
                'tanggal_berangkat' => '2024-02-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 2000000
            ]);

        $response->assertSessionHasErrors(['tujuan']);
    }

    /** @test */
    public function travel_request_keperluan_must_be_string()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Jakarta',
                'keperluan' => 123,
                'tanggal_berangkat' => '2024-02-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 2000000
            ]);

        $response->assertSessionHasErrors(['keperluan']);
    }

    /** @test */
    public function travel_request_keperluan_must_not_exceed_max_length()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Jakarta',
                'keperluan' => str_repeat('a', 1001),
                'tanggal_berangkat' => '2024-02-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 2000000
            ]);

        $response->assertSessionHasErrors(['keperluan']);
    }

    /** @test */
    public function travel_request_tanggal_berangkat_must_be_valid_date()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Jakarta',
                'keperluan' => 'Test Purpose',
                'tanggal_berangkat' => 'invalid-date',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 2000000
            ]);

        $response->assertSessionHasErrors(['tanggal_berangkat']);
    }

    /** @test */
    public function travel_request_tanggal_berangkat_must_be_future_date()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Jakarta',
                'keperluan' => 'Test Purpose',
                'tanggal_berangkat' => '2020-01-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 2000000
            ]);

        $response->assertSessionHasErrors(['tanggal_berangkat']);
    }

    /** @test */
    public function travel_request_tanggal_kembali_must_be_after_tanggal_berangkat()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Jakarta',
                'keperluan' => 'Test Purpose',
                'tanggal_berangkat' => '2024-02-03',
                'tanggal_kembali' => '2024-02-01',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 2000000
            ]);

        $response->assertSessionHasErrors(['tanggal_kembali']);
    }

    /** @test */
    public function travel_request_transportasi_must_be_valid_option()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Jakarta',
                'keperluan' => 'Test Purpose',
                'tanggal_berangkat' => '2024-02-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Invalid Transport',
                'estimasi_biaya' => 2000000
            ]);

        $response->assertSessionHasErrors(['transportasi']);
    }

    /** @test */
    public function travel_request_estimasi_biaya_must_be_numeric()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Jakarta',
                'keperluan' => 'Test Purpose',
                'tanggal_berangkat' => '2024-02-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 'not-a-number'
            ]);

        $response->assertSessionHasErrors(['estimasi_biaya']);
    }

    /** @test */
    public function travel_request_estimasi_biaya_must_be_positive()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Jakarta',
                'keperluan' => 'Test Purpose',
                'tanggal_berangkat' => '2024-02-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => -1000
            ]);

        $response->assertSessionHasErrors(['estimasi_biaya']);
    }

    /** @test */
    public function travel_request_estimasi_biaya_must_not_exceed_maximum()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Jakarta',
                'keperluan' => 'Test Purpose',
                'tanggal_berangkat' => '2024-02-01',
                'tanggal_kembali' => '2024-02-03',
                'transportasi' => 'Pesawat',
                'estimasi_biaya' => 1000000000
            ]);

        $response->assertSessionHasErrors(['estimasi_biaya']);
    }

    /** @test */
    public function user_registration_requires_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', []);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
            'password_confirmation',
            'role'
        ]);
    }

    /** @test */
    public function user_name_must_be_string()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 123,
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'user'
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function user_name_must_not_exceed_max_length()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => str_repeat('a', 256),
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'user'
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function user_email_must_be_valid_email()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test User',
                'email' => 'invalid-email',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'user'
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function user_email_must_be_unique()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test User',
                'email' => 'existing@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'user'
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function user_password_must_be_confirmed()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'different-password',
                'role' => 'user'
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function user_password_must_be_minimum_length()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => '123',
                'password_confirmation' => '123',
                'role' => 'user'
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function user_role_must_be_valid_option()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'invalid-role'
            ]);

        $response->assertSessionHasErrors(['role']);
    }

    /** @test */
    public function user_nip_must_be_numeric()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'user',
                'nip' => 'not-a-number'
            ]);

        $response->assertSessionHasErrors(['nip']);
    }

    /** @test */
    public function user_nip_must_be_unique()
    {
        User::factory()->create(['nip' => '123456789']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'user',
                'nip' => '123456789'
            ]);

        $response->assertSessionHasErrors(['nip']);
    }

    /** @test */
    public function document_upload_requires_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post('/documents', []);

        $response->assertSessionHasErrors([
            'nama_dokumen',
            'jenis_dokumen',
            'file'
        ]);
    }

    /** @test */
    public function document_nama_dokumen_must_be_string()
    {
        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 123,
                'jenis_dokumen' => 'surat_tugas',
                'file' => UploadedFile::fake()->create('document.pdf', 100)
            ]);

        $response->assertSessionHasErrors(['nama_dokumen']);
    }

    /** @test */
    public function document_jenis_dokumen_must_be_valid_option()
    {
        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 'Test Document',
                'jenis_dokumen' => 'invalid_type',
                'file' => UploadedFile::fake()->create('document.pdf', 100)
            ]);

        $response->assertSessionHasErrors(['jenis_dokumen']);
    }

    /** @test */
    public function document_file_must_be_file()
    {
        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 'Test Document',
                'jenis_dokumen' => 'surat_tugas',
                'file' => 'not-a-file'
            ]);

        $response->assertSessionHasErrors(['file']);
    }

    /** @test */
    public function document_file_must_not_exceed_max_size()
    {
        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 'Test Document',
                'jenis_dokumen' => 'surat_tugas',
                'file' => UploadedFile::fake()->create('document.pdf', 10241) // 10MB + 1KB
            ]);

        $response->assertSessionHasErrors(['file']);
    }

    /** @test */
    public function document_file_must_be_valid_mime_type()
    {
        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 'Test Document',
                'jenis_dokumen' => 'surat_tugas',
                'file' => UploadedFile::fake()->create('document.exe', 100)
            ]);

        $response->assertSessionHasErrors(['file']);
    }

    /** @test */
    public function approval_update_requires_required_fields()
    {
        $approval = Approval::factory()->create([
            'approver_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->put("/approvals/{$approval->id}", []);

        $response->assertSessionHasErrors(['status']);
    }

    /** @test */
    public function approval_status_must_be_valid_option()
    {
        $approval = Approval::factory()->create([
            'approver_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->put("/approvals/{$approval->id}", [
                'status' => 'invalid-status'
            ]);

        $response->assertSessionHasErrors(['status']);
    }

    /** @test */
    public function approval_catatan_must_not_exceed_max_length()
    {
        $approval = Approval::factory()->create([
            'approver_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->put("/approvals/{$approval->id}", [
                'status' => 'approved',
                'catatan' => str_repeat('a', 1001)
            ]);

        $response->assertSessionHasErrors(['catatan']);
    }

    /** @test */
    public function setting_update_requires_required_fields()
    {
        $setting = Setting::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put("/admin/settings/{$setting->id}", []);

        $response->assertSessionHasErrors(['value']);
    }

    /** @test */
    public function setting_value_must_not_exceed_max_length()
    {
        $setting = Setting::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put("/admin/settings/{$setting->id}", [
                'value' => str_repeat('a', 1001)
            ]);

        $response->assertSessionHasErrors(['value']);
    }

    /** @test */
    public function profile_update_requires_valid_fields()
    {
        $response = $this->actingAs($this->user)
            ->put('/profile', [
                'name' => '',
                'email' => 'invalid-email',
                'nip' => 'not-a-number'
            ]);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'nip'
        ]);
    }

    /** @test */
    public function profile_email_must_be_unique_when_changed()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($this->user)
            ->put('/profile', [
                'name' => 'Updated Name',
                'email' => 'existing@example.com'
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function password_change_requires_current_password()
    {
        $response = $this->actingAs($this->user)
            ->put('/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password'
            ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    /** @test */
    public function password_change_requires_confirmation()
    {
        $response = $this->actingAs($this->user)
            ->put('/profile/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'different-password'
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function search_query_must_not_exceed_max_length()
    {
        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?q=' . str_repeat('a', 256));

        $response->assertStatus(422);
    }

    /** @test */
    public function date_range_must_be_valid_format()
    {
        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?start_date=invalid-date&end_date=invalid-date');

        $response->assertStatus(422);
    }

    /** @test */
    public function cost_range_must_be_numeric()
    {
        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?min_cost=not-a-number&max_cost=not-a-number');

        $response->assertStatus(422);
    }

    /** @test */
    public function cost_range_min_must_be_less_than_max()
    {
        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?min_cost=5000000&max_cost=1000000');

        $response->assertStatus(422);
    }
}
