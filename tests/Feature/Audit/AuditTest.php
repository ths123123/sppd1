<?php

namespace Tests\Feature\Audit;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Approval;
use App\Models\Document;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AuditTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;
    protected $approver;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();
        $this->approver = User::factory()->approver()->create();
    }

    public function test_admin_can_view_audit_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit');

        $response->assertStatus(200);
        $response->assertViewIs('admin.audit.index');
    }

    public function test_regular_user_cannot_access_audit()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/audit');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_audit_logs()
    {
        // Create some test activity logs
        ActivityLog::factory()->count(10)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/logs');

        $response->assertStatus(200);
        $response->assertJsonCount(10);
    }

    public function test_admin_can_filter_audit_logs_by_user()
    {
        // Create logs for different users
        ActivityLog::factory()->count(3)->create(['user_id' => $this->user->id]);
        ActivityLog::factory()->count(2)->create(['user_id' => $this->approver->id]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/audit/logs?user_id={$this->user->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_admin_can_filter_audit_logs_by_action()
    {
        // Create logs for different actions
        ActivityLog::factory()->count(5)->create(['action' => 'login']);
        ActivityLog::factory()->count(3)->create(['action' => 'travel_request_created']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/logs?action=login');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    public function test_admin_can_filter_audit_logs_by_date_range()
    {
        // Create logs for different dates
        ActivityLog::factory()->create(['created_at' => now()->subDays(5)]);
        ActivityLog::factory()->create(['created_at' => now()->subDays(3)]);
        ActivityLog::factory()->create(['created_at' => now()->subDays(1)]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/logs?start_date=' . now()->subDays(4)->format('Y-m-d') . '&end_date=' . now()->subDays(2)->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_admin_can_export_audit_logs()
    {
        // Create some test logs
        ActivityLog::factory()->count(15)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/logs/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        
        // Verify export was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'audit_logs_exported'
        ]);
    }

    public function test_admin_can_view_user_activity_summary()
    {
        // Create various user activities
        ActivityLog::factory()->count(5)->create(['user_id' => $this->user->id, 'action' => 'login']);
        ActivityLog::factory()->count(3)->create(['user_id' => $this->user->id, 'action' => 'travel_request_created']);
        ActivityLog::factory()->count(2)->create(['user_id' => $this->user->id, 'action' => 'document_uploaded']);

        $response = $this->actingAs($this->admin)
            ->get("/admin/audit/user-activity/{$this->user->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user_info',
            'activity_summary',
            'recent_activities',
            'action_breakdown'
        ]);
    }

    public function test_admin_can_view_system_activity_summary()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/system-activity');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_activities',
            'activities_by_type',
            'activities_by_user',
            'activities_by_date',
            'peak_activity_times'
        ]);
    }

    public function test_admin_can_view_compliance_report()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/compliance');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'compliance_score',
            'policy_violations',
            'required_actions',
            'compliance_trends',
            'audit_recommendations'
        ]);
    }

    public function test_admin_can_generate_audit_report()
    {
        $reportData = [
            'report_type' => 'comprehensive',
            'start_date' => now()->subMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'include_users' => true,
            'include_actions' => true,
            'include_compliance' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/audit/generate-report', $reportData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify report generation was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'audit_report_generated'
        ]);
    }

    public function test_admin_can_view_data_integrity_report()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/data-integrity');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'database_consistency',
            'orphaned_records',
            'data_validation_errors',
            'referential_integrity',
            'data_quality_score'
        ]);
    }

    public function test_admin_can_run_data_integrity_check()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/audit/data-integrity-check');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify check was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'data_integrity_check_run'
        ]);
    }

    public function test_admin_can_view_security_audit()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/security');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'failed_login_attempts',
            'suspicious_activities',
            'privilege_escalation_attempts',
            'data_access_violations',
            'security_recommendations'
        ]);
    }

    public function test_admin_can_run_security_audit()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/audit/security-check');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify security check was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'security_audit_run'
        ]);
    }

    public function test_admin_can_view_change_history()
    {
        // Create some test changes
        $travelRequest = TravelRequest::factory()->create(['status' => 'submitted']);
        
        // Simulate status change
        $travelRequest->update(['status' => 'approved']);

        $response = $this->actingAs($this->admin)
            ->get("/admin/audit/changes/{$travelRequest->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'entity_type',
            'entity_id',
            'changes',
            'changed_by',
            'change_timestamp'
        ]);
    }

    public function test_admin_can_view_approval_audit_trail()
    {
        // Create travel request with approval
        $travelRequest = TravelRequest::factory()->create(['status' => 'submitted']);
        $approval = Approval::factory()->create([
            'travel_request_id' => $travelRequest->id,
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        // Approve the request
        $approval->update(['status' => 'approved']);

        $response = $this->actingAs($this->admin)
            ->get("/admin/audit/approval-trail/{$travelRequest->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'travel_request',
            'approval_history',
            'approval_timeline',
            'approval_details'
        ]);
    }

    public function test_admin_can_view_document_audit_trail()
    {
        // Create document
        $document = Document::factory()->create(['status' => 'pending']);

        // Simulate document verification
        $document->update(['status' => 'verified']);

        $response = $this->actingAs($this->admin)
            ->get("/admin/audit/document-trail/{$document->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'document',
            'verification_history',
            'access_log',
            'modification_history'
        ]);
    }

    public function test_admin_can_view_user_privilege_audit()
    {
        // Change user role
        $this->user->update(['role' => 'approver']);

        $response = $this->actingAs($this->admin)
            ->get("/admin/audit/user-privileges/{$this->user->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user_info',
            'role_changes',
            'permission_changes',
            'access_log',
            'privilege_escalation_attempts'
        ]);
    }

    public function test_admin_can_view_system_configuration_audit()
    {
        // Create some settings
        Setting::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/system-configuration');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'configuration_changes',
            'default_values',
            'custom_settings',
            'configuration_history'
        ]);
    }

    public function test_admin_can_view_backup_audit_trail()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/backup-trail');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'backup_history',
            'backup_verification',
            'restore_history',
            'backup_integrity_checks'
        ]);
    }

    public function test_admin_can_view_export_audit_trail()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/export-trail');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'export_history',
            'export_verification',
            'data_access_log',
            'export_compliance'
        ]);
    }

    public function test_admin_can_view_import_audit_trail()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/import-trail');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'import_history',
            'import_validation',
            'data_quality_checks',
            'import_compliance'
        ]);
    }

    public function test_admin_can_view_api_audit_trail()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/api-trail');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'api_requests',
            'api_responses',
            'api_errors',
            'api_usage_statistics'
        ]);
    }

    public function test_admin_can_view_session_audit_trail()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/session-trail');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'active_sessions',
            'session_history',
            'session_security',
            'session_anomalies'
        ]);
    }

    public function test_admin_can_view_file_access_audit()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/file-access');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'file_access_log',
            'file_downloads',
            'file_uploads',
            'file_modifications',
            'unauthorized_access_attempts'
        ]);
    }

    public function test_admin_can_view_database_audit_trail()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/database-trail');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'database_operations',
            'slow_queries',
            'failed_queries',
            'database_changes',
            'performance_metrics'
        ]);
    }

    public function test_admin_can_view_email_audit_trail()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/email-trail');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'email_sent',
            'email_failed',
            'email_bounces',
            'email_spam_reports',
            'email_delivery_statistics'
        ]);
    }

    public function test_admin_can_view_notification_audit_trail()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/notification-trail');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'notifications_sent',
            'notifications_read',
            'notification_delivery',
            'notification_preferences'
        ]);
    }

    public function test_admin_can_view_search_audit_trail()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/search-trail');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'search_queries',
            'search_results',
            'search_filters',
            'search_performance'
        ]);
    }

    public function test_admin_can_view_login_audit_trail()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/login-trail');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'successful_logins',
            'failed_logins',
            'login_locations',
            'login_devices',
            'suspicious_logins'
        ]);
    }

    public function test_admin_can_view_logout_audit_trail()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/logout-trail');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'logout_history',
            'session_duration',
            'logout_reasons',
            'forced_logouts'
        ]);
    }

    public function test_admin_can_view_password_change_audit()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/password-changes');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'password_changes',
            'password_resets',
            'password_policy_violations',
            'password_strength_audit'
        ]);
    }

    public function test_admin_can_view_data_retention_audit()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/data-retention');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data_retention_policies',
            'data_archived',
            'data_deleted',
            'retention_compliance',
            'data_lifecycle'
        ]);
    }

    public function test_admin_can_view_privacy_audit()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit/privacy');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'privacy_policy_compliance',
            'data_processing_activities',
            'user_consent_records',
            'data_subject_rights',
            'privacy_incidents'
        ]);
    }
}
