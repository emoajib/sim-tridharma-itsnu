<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FakultasControllerTest extends TestCase
{
    use RefreshDatabase, SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('master-data.fakultas'));

        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $response = $this->actingAs($this->dosen())->get(route('master-data.fakultas'));

        $response->assertStatus(403);
    }

    public function test_kaprodi_cannot_delete_fakultas(): void
    {
        $fakultas = Fakultas::factory()->create();

        $response = $this->actingAs($this->kaprodi())
            ->delete(route('master-data.fakultas.destroy', $fakultas));

        $response->assertStatus(403);
    }

    public function test_index_returns_200_for_authorized_user(): void
    {
        Fakultas::factory()->count(3)->create();

        $response = $this->actingAs($this->admin())->get(route('master-data.fakultas'));

        $response->assertStatus(200);
    }

    public function test_store_creates_fakultas_and_redirects(): void
    {
        $data = ['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'Fakultas Teknik'];

        $response = $this->actingAs($this->admin())
            ->from(route('master-data.fakultas'))
            ->post(route('master-data.fakultas.store'), $data);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('m_fakultas', $data);
    }

    public function test_update_updates_fakultas_and_redirects(): void
    {
        $fakultas = Fakultas::factory()->create();

        $response = $this->actingAs($this->admin())
            ->from(route('master-data.fakultas'))
            ->put(route('master-data.fakultas.update', $fakultas), [
                'kode_fakultas' => 'UPDATED',
                'nama_fakultas' => 'Updated',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('m_fakultas', [
            'id' => $fakultas->id,
            'kode_fakultas' => 'UPDATED',
            'nama_fakultas' => 'Updated',
        ]);
    }

    public function test_destroy_soft_deletes_fakultas_and_redirects(): void
    {
        $fakultas = Fakultas::factory()->create();

        $response = $this->actingAs($this->admin())
            ->delete(route('master-data.fakultas.destroy', $fakultas));

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertSoftDeleted($fakultas);
    }

    public function test_index_can_search_fakultas(): void
    {
        Fakultas::factory()->create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK']);
        Fakultas::factory()->create(['kode_fakultas' => 'FEB', 'nama_fakultas' => 'Ekonomi']);

        $response = $this->actingAs($this->admin())
            ->get(route('master-data.fakultas', ['search' => 'FTIK']));

        $response->assertStatus(200);
    }
}
