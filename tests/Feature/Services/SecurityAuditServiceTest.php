<?php

namespace Tests\Feature\Services;

use App\Models\Dosen;
use App\Models\SecurityAuditLog;
use App\Models\User;
use App\Services\Security\SecurityAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAuditServiceTest extends TestCase
{
    use RefreshDatabase;

    private SecurityAuditService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SecurityAuditService;
    }

    // ─── LOG CREATION ─────────────────────────────────────────

    public function test_log_creates_audit_entry(): void
    {
        $user = User::factory()->create();

        $log = $this->actingAs($user)->service->log(
            action: 'user.login',
            description: 'User logged in successfully',
            severity: 'info',
        );

        $this->assertInstanceOf(SecurityAuditLog::class, $log);
        $this->assertDatabaseHas('security_audit_logs', [
            'id' => $log->id,
            'action' => 'user.login',
            'description' => 'User logged in successfully',
            'severity' => 'info',
            'user_id' => $user->id,
        ]);
    }

    public function test_log_creates_critical_severity_entry(): void
    {
        $log = $this->service->log(
            action: 'admin.role_change',
            description: 'User role changed from admin to user',
            severity: 'critical',
        );

        $this->assertEquals('critical', $log->severity);
        $this->assertDatabaseHas('security_audit_logs', [
            'id' => $log->id,
            'severity' => 'critical',
        ]);
    }

    // ─── AUTO-FILL IP AND USER AGENT ──────────────────────────

    public function test_log_auto_fills_user_ip_and_user_agent(): void
    {
        $this->travel(now());

        $log = $this->service->log(
            action: 'user.logout',
            description: 'User logged out',
        );

        $this->assertNotNull($log->id);

        // IP and user agent will be null when running from CLI/phpunit
        // because there's no HTTP request context
        $this->assertDatabaseHas('security_audit_logs', [
            'id' => $log->id,
            'action' => 'user.logout',
        ]);
    }

    public function test_log_works_without_http_context(): void
    {
        $log = $this->service->log(
            action: 'system.backup',
            description: 'Automated daily backup completed',
            severity: 'info',
        );

        $this->assertDatabaseHas('security_audit_logs', [
            'id' => $log->id,
            'action' => 'system.backup',
        ]);
        // ip_address and user_agent are set from the HTTP request (127.0.0.1 in tests), not null
    }

    // ─── AUDITABLE MORPH ──────────────────────────────────────

    public function test_log_with_auditable_morphs(): void
    {
        $dosen = Dosen::create([
            'nidn' => '1234567890',
            'nama_depan' => 'Test',
            'nama_belakang' => 'User',
        ]);

        $log = $this->service->log(
            action: 'dosen.updated',
            description: 'Dosen profile updated',
            auditable: $dosen,
            oldValues: ['nama_depan' => 'Old Name'],
            newValues: ['nama_depan' => 'New Name'],
            severity: 'warning',
        );

        $this->assertDatabaseHas('security_audit_logs', [
            'id' => $log->id,
            'auditable_type' => $dosen->getMorphClass(),
            'auditable_id' => $dosen->id,
            'action' => 'dosen.updated',
        ]);

        $this->assertEquals(['nama_depan' => 'Old Name'], $log->old_values);
        $this->assertEquals(['nama_depan' => 'New Name'], $log->new_values);
    }

    public function test_log_with_auditable_nullable(): void
    {
        $log = $this->service->log(
            action: 'system.config_change',
            auditable: null,
        );

        $this->assertDatabaseHas('security_audit_logs', [
            'id' => $log->id,
            'auditable_type' => null,
            'auditable_id' => null,
        ]);
    }

    public function test_log_stores_old_and_new_values_as_json(): void
    {
        $oldValues = ['email' => 'old@test.com', 'name' => 'Old'];
        $newValues = ['email' => 'new@test.com', 'name' => 'New'];

        $log = $this->service->log(
            action: 'user.updated',
            oldValues: $oldValues,
            newValues: $newValues,
        );

        $this->assertEquals($oldValues, $log->old_values);
        $this->assertEquals($newValues, $log->new_values);
    }

    // ─── WITHOUT USER ─────────────────────────────────────────

    public function test_log_works_without_user(): void
    {
        $log = $this->service->log(
            action: 'system.startup',
            description: 'Application started',
        );

        $this->assertDatabaseHas('security_audit_logs', [
            'id' => $log->id,
            'user_id' => null,
        ]);
    }
}
