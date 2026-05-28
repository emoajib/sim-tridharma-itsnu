<?php

namespace Tests\Feature\Http;

use App\Models\IndikatorIku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class IkuControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get(route('iku.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Asesor Tamu');

        $this->actingAs($user)
            ->get(route('iku.index'))
            ->assertStatus(403);
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('iku.index'))
            ->assertStatus(200);
    }

    public function test_store_creates_iku(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('iku.store'), [
                'kode_iku' => 'IKU-001',
                'nama_indikator' => 'Indikator Kinerja Utama Test',
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('iku.index'));

        $this->assertDatabaseHas('m_indikator_iku', [
            'kode_iku' => 'IKU-001',
            'nama_indikator' => 'Indikator Kinerja Utama Test',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('iku.store'), []);

        $response->assertSessionHasErrors(['kode_iku', 'nama_indikator']);
    }

    public function test_update_updates_iku(): void
    {
        $user = $this->admin();
        $iku = IndikatorIku::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('iku.update', $iku), [
                'kode_iku' => $iku->kode_iku,
                'nama_indikator' => 'Updated IKU',
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('iku.index'));

        $this->assertDatabaseHas('m_indikator_iku', [
            'id' => $iku->id,
            'nama_indikator' => 'Updated IKU',
        ]);
    }

    public function test_destroy_deletes_iku(): void
    {
        $user = $this->admin();
        $iku = IndikatorIku::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('iku.destroy', $iku));

        $response->assertSessionHas('success');
        $response->assertRedirect(route('iku.index'));

        $this->assertSoftDeleted('m_indikator_iku', ['id' => $iku->id]);
    }
}
