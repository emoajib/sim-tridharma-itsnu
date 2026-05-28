<?php

namespace Tests\Feature\Http;

use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\StandarMutu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class EdpsControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get(route('spmi.edps'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = $this->dosen();

        $this->actingAs($user)
            ->get(route('spmi.edps'))
            ->assertStatus(403);
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('spmi.edps'))
            ->assertStatus(200);
    }

    public function test_store_creates_edps(): void
    {
        $user = $this->admin();
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();
        $standar = StandarMutu::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('spmi.edps.store'), [
                'prodi_id' => $prodi->id,
                'periode_id' => $periode->id,
                'standar_mutu_id' => $standar->id,
                'target' => 80,
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertDatabaseHas('trx_edps', [
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'standar_mutu_id' => $standar->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('spmi.edps.store'), []);

        $response->assertSessionHasErrors(['prodi_id', 'periode_id', 'standar_mutu_id', 'target']);
    }

    public function test_destroy_deletes_edps(): void
    {
        $user = $this->admin();
        $edps = \App\Models\Edps::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('spmi.edps.destroy', $edps));

        $response->assertSessionHas('success');
        $response->assertRedirect(route('spmi.edps'));

        $this->assertDatabaseMissing('trx_edps', ['id' => $edps->id]);
    }
}
