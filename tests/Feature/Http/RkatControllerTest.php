<?php

namespace Tests\Feature\Http;

use App\Models\IndikatorIku;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\RkatPagu;
use App\Models\UsulanRkat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class RkatControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get(route('rkat.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Asesor Tamu');

        $this->actingAs($user)
            ->get(route('rkat.index'))
            ->assertStatus(403);
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('rkat.index'))
            ->assertStatus(200);
    }

    public function test_authorized_user_can_access_create_page(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('rkat.create'))
            ->assertStatus(200);
    }

    public function test_authorized_user_can_access_show(): void
    {
        $user = $this->admin();
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();
        $iku = IndikatorIku::factory()->create();
        $usulan = UsulanRkat::factory()->create([
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'iku_id' => $iku->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('rkat.show', $usulan->id))
            ->assertStatus(200);
    }

    public function test_store_creates_proposal(): void
    {
        $user = $this->admin();
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('rkat.store'), [
                'prodi_id' => $prodi->id,
                'periode_id' => $periode->id,
                'judul_kegiatan' => 'Kegiatan Akreditasi Prodi',
                'deskripsi_kegiatan' => 'Deskripsi kegiatan akreditasi',
                'estimasi_biaya' => 50000000,
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('rkat.index'));

        $this->assertDatabaseHas('trx_usulan_rkat', [
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'judul_kegiatan' => 'Kegiatan Akreditasi Prodi',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('rkat.store'), []);

        $response->assertSessionHasErrors(['prodi_id', 'periode_id', 'judul_kegiatan', 'estimasi_biaya']);
    }

    public function test_pagu_index_access(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('rkat.pagu'))
            ->assertStatus(200);
    }

    public function test_pagu_store_creates_pagu(): void
    {
        $user = $this->admin();
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('rkat.pagu.store'), [
                'periode_id' => $periode->id,
                'unit_type' => 'Prodi',
                'unit_id' => $prodi->id,
                'pagu_total' => 100000000,
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertDatabaseHas('trx_rkat_pagu', [
            'periode_id' => $periode->id,
            'unit_type' => 'Prodi',
            'unit_id' => $prodi->id,
            'pagu_total' => 100000000,
        ]);
    }
}
