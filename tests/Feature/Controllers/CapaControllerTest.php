<?php

namespace Tests\Feature\Controllers;

use App\Models\AuditMutu;
use App\Models\Capa;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedRolePermission;
use Tests\TestCase;

class CapaControllerTest extends TestCase
{
    use RefreshDatabase, SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    protected function createCapa(): Capa
    {
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();
        $audit = AuditMutu::factory()->create([
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'severity' => 'berat',
        ]);

        return Capa::factory()->create([
            'audit_mutu_id' => $audit->id,
        ]);
    }

    // ─── AUTHENTICATION ───────────────────────────────────────

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('spmi.capa'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $response = $this->actingAs($this->dosen())->get(route('spmi.capa'));
        $response->assertStatus(403);
    }

    // ─── INDEX ────────────────────────────────────────────────

    public function test_can_list_capas(): void
    {
        Capa::factory()->count(3)->create();

        $response = $this->actingAs($this->admin())->get(route('spmi.capa'));

        $response->assertStatus(200);
    }

    public function test_index_filters_by_status(): void
    {
        Capa::factory()->create(['status' => 'open']);
        Capa::factory()->create(['status' => 'verified']);

        $response = $this->actingAs($this->admin())
            ->get(route('spmi.capa', ['status' => 'open']));

        $response->assertStatus(200);
    }

    // ─── SHOW ─────────────────────────────────────────────────

    public function test_can_show_capa(): void
    {
        $capa = $this->createCapa();

        $response = $this->actingAs($this->admin())
            ->get(route('spmi.capa.show', $capa));

        $response->assertStatus(200);
    }

    // ─── UPDATE ───────────────────────────────────────────────

    public function test_can_update_capa(): void
    {
        $capa = $this->createCapa();

        $response = $this->actingAs($this->admin())
            ->from(route('spmi.capa'))
            ->put(route('spmi.capa.update', $capa), [
                'root_cause_analysis' => 'Analisis akar masalah diperbarui.',
                'corrective_action' => 'Tindakan korektif baru.',
                'status' => 'in_progress',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('trx_capa', [
            'id' => $capa->id,
            'root_cause_analysis' => 'Analisis akar masalah diperbarui.',
            'corrective_action' => 'Tindakan korektif baru.',
            'status' => 'in_progress',
        ]);
    }

    public function test_can_update_capa_status_to_verified(): void
    {
        $capa = $this->createCapa();

        $response = $this->actingAs($this->admin())
            ->from(route('spmi.capa'))
            ->put(route('spmi.capa.update', $capa), [
                'status' => 'verified',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('trx_capa', [
            'id' => $capa->id,
            'status' => 'verified',
        ]);
    }

    // ─── VALIDATION ───────────────────────────────────────────

    public function test_update_with_invalid_status_fails(): void
    {
        $capa = $this->createCapa();

        $response = $this->actingAs($this->admin())
            ->from(route('spmi.capa'))
            ->put(route('spmi.capa.update', $capa), [
                'status' => 'nonexistent_status',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('status');
    }

    // ─── TIMELINE ─────────────────────────────────────────────

    public function test_can_get_timeline(): void
    {
        $capa = $this->createCapa();

        $response = $this->actingAs($this->admin())
            ->get(route('spmi.capa.timeline', $capa));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'action', 'field', 'old_value', 'new_value', 'user_name', 'created_at', 'created_at_formatted'],
            ],
        ]);
    }

    // ─── SUBMIT VERIFICATION ─────────────────────────────────

    public function test_can_submit_for_verification(): void
    {
        $capa = $this->createCapa();
        $capa->update(['status' => 'in_progress']);

        $response = $this->actingAs($this->admin())
            ->from(route('spmi.capa'))
            ->post(route('spmi.capa.submit-verification', $capa), [
                'note' => 'Mohon diverifikasi.',
            ]);

        $response->assertStatus(302);
    }

    // ─── VERIFY / REJECT ─────────────────────────────────────

    public function test_can_verify_capa(): void
    {
        $capa = $this->createCapa();
        $capa->update(['status' => 'awaiting_verification']);

        $response = $this->actingAs($this->admin())
            ->from(route('spmi.capa'))
            ->post(route('spmi.capa.verify', $capa), [
                'action' => 'approved',
                'note' => 'CAPA disetujui. Tindakan sudah sesuai.',
            ]);

        $response->assertStatus(302);
    }

    public function test_can_reject_capa(): void
    {
        $capa = $this->createCapa();
        $capa->update(['status' => 'awaiting_verification']);

        $response = $this->actingAs($this->admin())
            ->from(route('spmi.capa'))
            ->post(route('spmi.capa.verify', $capa), [
                'action' => 'rejected',
                'note' => 'Tindakan korektif kurang tepat.',
            ]);

        $response->assertStatus(302);
    }

    public function test_verify_requires_action_and_note(): void
    {
        $capa = $this->createCapa();
        $capa->update(['status' => 'awaiting_verification']);

        $response = $this->actingAs($this->admin())
            ->from(route('spmi.capa'))
            ->post(route('spmi.capa.verify', $capa), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['action', 'note']);
    }

    public function test_verify_rejects_invalid_action(): void
    {
        $capa = $this->createCapa();
        $capa->update(['status' => 'awaiting_verification']);

        $response = $this->actingAs($this->admin())
            ->from(route('spmi.capa'))
            ->post(route('spmi.capa.verify', $capa), [
                'action' => 'unknown',
                'note' => 'Test',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('action');
    }
}
