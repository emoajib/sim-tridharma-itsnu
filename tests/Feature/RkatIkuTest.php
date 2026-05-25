<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\IndikatorIku;
use App\Models\LembagaAkreditasi;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\User;
use App\Models\UsulanRkat;
use App\Models\RkatPagu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RkatIkuTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    protected $admin;
    protected $prodi;
    protected $periode;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create();
        $roleAdmin = Role::create(['name' => 'Admin']);
        $this->admin->assignRole($roleAdmin);
        Session::put('active_role', 'Admin');
        
        $fakultas = Fakultas::create([
            'kode_fakultas' => 'FT',
            'nama_fakultas' => 'Fakultas Teknik',
        ]);

        $this->prodi = Prodi::create([
            'kode_prodi' => 'INF',
            'nama_prodi' => 'Informatika',
            'fakultas_id' => $fakultas->id,
            'jenjang' => 'S1',
        ]);

        $this->periode = PeriodeAkademik::create([
            'kode_periode' => '20251',
            'nama_periode' => '2025/2026 Ganjil',
            'is_active' => true,
        ]);
    }

    public function test_can_access_iku_index()
    {
        $response = $this->actingAs($this->admin)->get(route('iku.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Iku/Index'));
    }

    public function test_can_create_iku()
    {
        $data = [
            'kode_iku' => 'IKU-TEST-1',
            'nama_indikator' => 'Indikator Test',
            'target' => 85,
            'bobot' => 10,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('iku.store'), $data);
        
        $response->assertRedirect(route('iku.index'));
        $this->assertDatabaseHas('m_indikator_iku', [
            'kode_iku' => 'IKU-TEST-1',
            'nama_indikator' => 'Indikator Test'
        ]);
    }

    public function test_can_access_cascading_iku()
    {
        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->admin)->get(route('iku.cascading'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Iku/Cascading'));
    }

    public function test_can_access_rkat_index()
    {
        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->admin)->get(route('rkat.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Keuangan/Rkat/Index'));
    }

    public function test_can_manage_pagu()
    {
        $data = [
            'periode_id' => $this->periode->id,
            'unit_type' => 'Prodi',
            'unit_id' => $this->prodi->id,
            'pagu_total' => 100000000,
        ];

        $response = $this->actingAs($this->admin)->post(route('rkat.pagu.store'), $data);
        
        $response->assertStatus(302);
        $this->assertDatabaseHas('trx_rkat_pagu', [
            'unit_id' => $this->prodi->id,
            'pagu_total' => 100000000,
        ]);
    }

    public function test_can_submit_rkat_usulan()
    {
        $roleKaprodi = Role::create(['name' => 'Kaprodi']);
        $user = User::factory()->create(['prodi_id' => $this->prodi->id]);
        $user->assignRole($roleKaprodi);
        Session::put('active_role', 'Kaprodi');

        // Setup Pagu first
        RkatPagu::create([
            'periode_id' => $this->periode->id,
            'unit_type' => 'Prodi',
            'unit_id' => $this->prodi->id,
            'pagu_total' => 100000000,
        ]);

        $iku = IndikatorIku::create([
            'kode_iku' => 'IKU-1',
            'nama_indikator' => 'IKU 1',
            'target' => 90,
            'bobot' => 5,
        ]);

        $data = [
            'prodi_id' => $this->prodi->id,
            'periode_id' => $this->periode->id,
            'judul_kegiatan' => 'Workshop Kurikulum',
            'deskripsi_kegiatan' => 'Workshop peningkatan kurikulum berorientasi MBKM',
            'estimasi_biaya' => 5000000,
            'iku_id' => $iku->id,
        ];

        $response = $this->actingAs($user)->post(route('rkat.store'), $data);
        
        $response->assertRedirect(route('rkat.index'));
        $this->assertDatabaseHas('trx_usulan_rkat', [
            'judul_kegiatan' => 'Workshop Kurikulum',
            'estimasi_biaya' => 5000000,
            'status' => 'submitted'
        ]);
    }

    public function test_cannot_submit_rkat_exceeding_pagu()
    {
        $roleKaprodi = Role::create(['name' => 'Kaprodi']);
        $user = User::factory()->create(['prodi_id' => $this->prodi->id]);
        $user->assignRole($roleKaprodi);
        Session::put('active_role', 'Kaprodi');

         // Setup Pagu first with small amount
         RkatPagu::create([
            'periode_id' => $this->periode->id,
            'unit_type' => 'Prodi',
            'unit_id' => $this->prodi->id,
            'pagu_total' => 1000000,
        ]);

        $data = [
            'prodi_id' => $this->prodi->id,
            'periode_id' => $this->periode->id,
            'judul_kegiatan' => 'Expensive Workshop',
            'estimasi_biaya' => 5000000, // Exceeds 1M
        ];

        $response = $this->actingAs($user)->post(route('rkat.store'), $data);
        $response->assertRedirect(route('rkat.index'));
        
        $usulan = UsulanRkat::where('judul_kegiatan', 'Expensive Workshop')->first();
        
        $response = $this->actingAs($this->admin)->post(route('rkat.approve', $usulan->id), [
            'action' => 'approve',
            'keterangan' => 'Budget exceeds'
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('submitted', $usulan->fresh()->status);
    }
}
