<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DosenControllerTest extends TestCase
{
    use RefreshDatabase, SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('master-data.dosen'));

        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $response = $this->actingAs($this->dosen())->get(route('master-data.dosen'));

        $response->assertStatus(403);
    }

    public function test_index_returns_200_for_authorized_user(): void
    {
        $prodi = Prodi::factory()->create();
        Dosen::factory()->count(3)->create(['prodi_id' => $prodi->id]);

        $response = $this->actingAs($this->admin())->get(route('master-data.dosen'));

        $response->assertStatus(200);
    }

    public function test_store_creates_dosen_and_redirects(): void
    {
        $prodi = Prodi::factory()->create();
        $data = [
            'nidn' => '1234567890',
            'nama_depan' => 'John',
            'prodi_id' => $prodi->id,
        ];

        $response = $this->actingAs($this->admin())
            ->from(route('master-data.dosen'))
            ->post(route('master-data.dosen.store'), $data);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('m_dosen', $data);
    }

    public function test_update_updates_dosen_and_redirects(): void
    {
        $prodi = Prodi::factory()->create();
        $dosen = Dosen::factory()->create(['prodi_id' => $prodi->id]);

        $response = $this->actingAs($this->admin())
            ->from(route('master-data.dosen'))
            ->put(route('master-data.dosen.update', $dosen), [
                'nidn' => $dosen->nidn,
                'nama_depan' => 'Updated',
                'prodi_id' => $prodi->id,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('m_dosen', [
            'id' => $dosen->id,
            'nama_depan' => 'Updated',
        ]);
    }

    public function test_destroy_soft_deletes_dosen_and_redirects(): void
    {
        $prodi = Prodi::factory()->create();
        $dosen = Dosen::factory()->create(['prodi_id' => $prodi->id]);

        $response = $this->actingAs($this->admin())
            ->delete(route('master-data.dosen.destroy', $dosen));

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertSoftDeleted($dosen);
    }

    public function test_store_fails_without_prodi(): void
    {
        $data = ['nidn' => '1234567890', 'nama_depan' => 'John'];

        $response = $this->actingAs($this->admin())
            ->from(route('master-data.dosen'))
            ->post(route('master-data.dosen.store'), $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('prodi_id');
    }

    public function test_index_can_search_dosen(): void
    {
        $prodi = Prodi::factory()->create();
        Dosen::factory()->create(['nama_depan' => 'John', 'prodi_id' => $prodi->id]);
        Dosen::factory()->create(['nama_depan' => 'Jane', 'prodi_id' => $prodi->id]);

        $response = $this->actingAs($this->admin())
            ->get(route('master-data.dosen', ['search' => 'John']));

        $response->assertStatus(200);
    }
}
