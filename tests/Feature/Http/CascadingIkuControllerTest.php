<?php

namespace Tests\Feature\Http;

use App\Models\CascadingIku;
use App\Models\IndikatorIku;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class CascadingIkuControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get(route('iku.cascading'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Asesor Tamu');

        $this->actingAs($user)
            ->get(route('iku.cascading'))
            ->assertStatus(403);
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('iku.cascading'))
            ->assertStatus(200);
    }

    public function test_store_creates_cascading(): void
    {
        $user = $this->admin();
        $iku = IndikatorIku::factory()->create();
        $periode = PeriodeAkademik::factory()->create();
        $prodi = Prodi::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('iku.cascading.store'), [
                'iku_id' => $iku->id,
                'periode_id' => $periode->id,
                'unit_type' => 'Prodi',
                'unit_id' => $prodi->id,
                'target' => 85.5,
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('iku.cascading'));

        $this->assertDatabaseHas('trx_cascading_iku', [
            'iku_id' => $iku->id,
            'periode_id' => $periode->id,
            'unit_type' => 'Prodi',
            'unit_id' => $prodi->id,
            'target' => 85.5,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('iku.cascading.store'), []);

        $response->assertSessionHasErrors(['iku_id', 'periode_id', 'unit_type', 'unit_id', 'target']);
    }

    public function test_update_capaian_updates_cascading(): void
    {
        $user = $this->admin();
        $cascading = CascadingIku::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('iku.cascading.capaian', $cascading), [
                'capaian' => 90.0,
                'catatan' => 'Tercapai dengan baik',
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertDatabaseHas('trx_cascading_iku', [
            'id' => $cascading->id,
            'capaian' => 90.0,
            'catatan' => 'Tercapai dengan baik',
        ]);
    }

    public function test_update_capaian_validates_required_fields(): void
    {
        $user = $this->admin();
        $cascading = CascadingIku::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('iku.cascading.capaian', $cascading), []);

        $response->assertSessionHasErrors(['capaian']);
    }
}
