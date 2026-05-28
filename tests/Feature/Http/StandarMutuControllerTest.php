<?php

namespace Tests\Feature\Http;

use App\Models\StandarMutu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class StandarMutuControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get(route('spmi.standar-mutu'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = $this->dosen();

        $this->actingAs($user)
            ->get(route('spmi.standar-mutu'))
            ->assertStatus(403);
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('spmi.standar-mutu'))
            ->assertStatus(200);
    }

    public function test_store_creates_standar_mutu(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('spmi.standar-mutu.store'), [
                'kategori' => 'Akademik',
                'kode_standar' => 'STD-001',
                'nama_standar' => 'Standar Mutu Test',
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertDatabaseHas('m_standar_mutu', [
            'kode_standar' => 'STD-001',
            'nama_standar' => 'Standar Mutu Test',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('spmi.standar-mutu.store'), []);

        $response->assertSessionHasErrors(['kategori', 'kode_standar', 'nama_standar']);
    }

    public function test_store_validates_unique_kode_standar(): void
    {
        $user = $this->admin();
        StandarMutu::factory()->create(['kode_standar' => 'STD-001']);

        $response = $this->actingAs($user)
            ->post(route('spmi.standar-mutu.store'), [
                'kategori' => 'Akademik',
                'kode_standar' => 'STD-001',
                'nama_standar' => 'Duplicate',
            ]);

        $response->assertSessionHasErrors('kode_standar');
    }

    public function test_update_updates_standar_mutu(): void
    {
        $user = $this->admin();
        $standar = StandarMutu::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('spmi.standar-mutu.update', $standar), [
                'nama_standar' => 'Updated Standar',
                'kategori' => 'Non-Akademik',
                'kode_standar' => $standar->kode_standar,
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertDatabaseHas('m_standar_mutu', [
            'id' => $standar->id,
            'nama_standar' => 'Updated Standar',
        ]);
    }

    public function test_destroy_deletes_standar_mutu(): void
    {
        $user = $this->admin();
        $standar = StandarMutu::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('spmi.standar-mutu.destroy', $standar));

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertSoftDeleted('m_standar_mutu', ['id' => $standar->id]);
    }
}
