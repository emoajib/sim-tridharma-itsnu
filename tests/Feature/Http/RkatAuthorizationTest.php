<?php

namespace Tests\Feature\Http;

use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedRolePermission;
use Tests\TestCase;

class RkatAuthorizationTest extends TestCase
{
    use RefreshDatabase, SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    public function test_dosen_cannot_submit_rkat_for_other_prodi()
    {
        $fakultas1 = Fakultas::factory()->create();
        $fakultas2 = Fakultas::factory()->create();

        $prodi1 = Prodi::factory()->create(['fakultas_id' => $fakultas1->id]);
        $prodi2 = Prodi::factory()->create(['fakultas_id' => $fakultas2->id]);

        $dosen = Dosen::factory()->create(['prodi_id' => $prodi1->id]);
        $user = User::factory()->create([
            'dosen_id' => $dosen->id,
            'prodi_id' => $prodi1->id,
        ]);
        $user->assignRole('Dosen');

        $periode = PeriodeAkademik::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['active_role' => 'Dosen'])
            ->post(route('rkat.store'), [
                'prodi_id' => $prodi2->id,
                'periode_id' => $periode->id,
                'judul_kegiatan' => 'Unauthorized Activity',
                'estimasi_biaya' => 5000000,
            ]);

        $response->assertStatus(403);
    }

    public function test_kaprodi_can_submit_rkat_for_own_prodi()
    {
        $fakultas = Fakultas::factory()->create();
        $prodi = Prodi::factory()->create(['fakultas_id' => $fakultas->id]);

        $user = User::factory()->create([
            'prodi_id' => $prodi->id,
        ]);
        $user->assignRole('Kaprodi');

        $periode = PeriodeAkademik::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['active_role' => 'Kaprodi'])
            ->post(route('rkat.store'), [
                'prodi_id' => $prodi->id,
                'periode_id' => $periode->id,
                'judul_kegiatan' => 'Authorized Activity',
                'estimasi_biaya' => 5000000,
            ]);

        $response->assertStatus(302);
    }

    public function test_kaprodi_cannot_submit_rkat_for_other_prodi()
    {
        $fakultas1 = Fakultas::factory()->create();
        $fakultas2 = Fakultas::factory()->create();

        $prodi1 = Prodi::factory()->create(['fakultas_id' => $fakultas1->id]);
        $prodi2 = Prodi::factory()->create(['fakultas_id' => $fakultas2->id]);

        $user = User::factory()->create([
            'prodi_id' => $prodi1->id,
        ]);
        $user->assignRole('Kaprodi');

        $periode = PeriodeAkademik::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['active_role' => 'Kaprodi'])
            ->post(route('rkat.store'), [
                'prodi_id' => $prodi2->id,
                'periode_id' => $periode->id,
                'judul_kegiatan' => 'Unauthorized Activity',
                'estimasi_biaya' => 5000000,
            ]);

        $response->assertStatus(403);
    }

    public function test_dekan_cannot_submit_rkat_without_create_permission()
    {
        $fakultas = Fakultas::factory()->create();

        $prodi1 = Prodi::factory()->create(['fakultas_id' => $fakultas->id]);
        $prodi2 = Prodi::factory()->create(['fakultas_id' => $fakultas->id]);

        $user = User::factory()->create([
            'prodi_id' => $prodi1->id,
        ]);
        $user->assignRole('Dekan');

        $periode = PeriodeAkademik::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['active_role' => 'Dekan'])
            ->post(route('rkat.store'), [
                'prodi_id' => $prodi2->id,
                'periode_id' => $periode->id,
                'judul_kegiatan' => 'Unauthorized Activity',
                'estimasi_biaya' => 5000000,
            ]);

        $response->assertStatus(403);
    }
}