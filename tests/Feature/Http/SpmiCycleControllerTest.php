<?php

namespace Tests\Feature\Http;

use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\SpmiCycle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class SpmiCycleControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get(route('spmi.cycle'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = $this->dosen();

        $this->actingAs($user)
            ->get(route('spmi.cycle'))
            ->assertStatus(403);
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('spmi.cycle'))
            ->assertStatus(200);
    }

    public function test_store_creates_cycle(): void
    {
        $user = $this->admin();
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('spmi.cycle.store'), [
                'prodi_id' => $prodi->id,
                'periode_id' => $periode->id,
                'tahap' => 'penetapan',
                'kategori' => 'Akademik',
                'nama_siklus' => 'Siklus Test',
                'tanggal_mulai' => '2025-01-01',
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertDatabaseHas('spmi_cycles', [
            'nama_siklus' => 'Siklus Test',
            'tahap' => 'penetapan',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('spmi.cycle.store'), []);

        $response->assertSessionHasErrors(['prodi_id', 'periode_id', 'tahap', 'kategori', 'nama_siklus', 'tanggal_mulai']);
    }

    public function test_update_updates_cycle(): void
    {
        $user = $this->admin();
        $cycle = SpmiCycle::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('spmi.cycle.update', $cycle), [
                'nama_siklus' => 'Siklus Updated',
                'status' => 'in_progress',
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertDatabaseHas('spmi_cycles', [
            'id' => $cycle->id,
            'nama_siklus' => 'Siklus Updated',
            'status' => 'in_progress',
        ]);
    }

    public function test_destroy_deletes_cycle(): void
    {
        $user = $this->admin();
        $cycle = SpmiCycle::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('spmi.cycle.destroy', $cycle));

        $response->assertSessionHas('success');
        $response->assertRedirect(route('spmi.cycle'));

        $this->assertDatabaseMissing('spmi_cycles', ['id' => $cycle->id]);
    }
}
