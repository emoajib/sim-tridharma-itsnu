<?php

namespace Tests\Feature\Http;

use App\Models\Rtm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class RtmControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get(route('spmi.rtm'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = $this->dosen();

        $this->actingAs($user)
            ->get(route('spmi.rtm'))
            ->assertStatus(403);
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('spmi.rtm'))
            ->assertStatus(200);
    }

    public function test_store_creates_rtm(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('spmi.rtm.store'), [
                'judul' => 'Rapat Tinjauan Mutu Test',
                'tanggal_rapat' => '2025-01-15',
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertDatabaseHas('trx_rtm', [
            'judul' => 'Rapat Tinjauan Mutu Test',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('spmi.rtm.store'), []);

        $response->assertSessionHasErrors(['judul', 'tanggal_rapat']);
    }

    public function test_show_displays_rtm(): void
    {
        $user = $this->admin();
        $rtm = \App\Models\Rtm::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('spmi.rtm.show', $rtm));

        $response->assertStatus(200);
    }

    public function test_update_updates_rtm(): void
    {
        $user = $this->admin();
        $rtm = Rtm::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('spmi.rtm.update', $rtm), [
                'judul' => 'Updated RTM Title',
                'tanggal_rapat' => '2025-01-20',
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('spmi.rtm'));

        $this->assertDatabaseHas('trx_rtm', [
            'id' => $rtm->id,
            'judul' => 'Updated RTM Title',
        ]);
    }

    public function test_destroy_deletes_rtm(): void
    {
        $user = $this->admin();
        $rtm = Rtm::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('spmi.rtm.destroy', $rtm));

        $response->assertSessionHas('success');
        $response->assertRedirect(route('spmi.rtm'));

        $this->assertDatabaseMissing('trx_rtm', ['id' => $rtm->id]);
    }
}
