<?php

namespace Tests\Feature\Http;

use App\Http\Controllers\Api\MasterDataController;
use App\Models\Cpl;
use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\Kurikulum;
use App\Models\MataKuliah;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class MasterDataControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    /**
     * No route is registered for MasterDataController.
     * Test the controller class directly.
     */
    public function test_summary_returns_json_structure(): void
    {
        $this->seedOnce();

        // Create some master data (only models with HasFactory can use factory())
        $fakultas = Fakultas::factory()->count(2)->create();
        Prodi::factory()->count(3)->create();
        Dosen::factory()->count(5)->create();
        MataKuliah::factory()->count(4)->create();
        PeriodeAkademik::factory()->count(1)->create();

        $firstProdi = Prodi::first();

        // Kurikulum and Cpl don't have factories, create directly
        Kurikulum::create([
            'nama_kurikulum' => 'Kurikulum 2024',
            'prodi_id' => $firstProdi->id,
            'tahun_berlaku' => '2024',
            'is_active' => true,
        ]);

        Cpl::create([
            'kode_cpl' => 'CPL-01',
            'prodi_id' => $firstProdi->id,
            'deskripsi' => 'Test CPL',
            'jenis' => 'Sikap',
            'is_active' => true,
        ]);
        Cpl::create([
            'kode_cpl' => 'CPL-02',
            'prodi_id' => $firstProdi->id,
            'deskripsi' => 'Test CPL 2',
            'jenis' => 'Pengetahuan',
            'is_active' => true,
        ]);

        $controller = new MasterDataController();
        $response = $controller->summary();

        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = $response->getData(true);

        $this->assertArrayHasKey('fakultas_count', $data);
        $this->assertArrayHasKey('prodi_count', $data);
        $this->assertArrayHasKey('dosen_count', $data);
        $this->assertArrayHasKey('mata_kuliah_count', $data);
        $this->assertArrayHasKey('kurikulum_count', $data);
        $this->assertArrayHasKey('cpl_count', $data);
        $this->assertArrayHasKey('periode_count', $data);

        // Verify data is reflected in the summary
        $this->assertGreaterThanOrEqual(2, $data['fakultas_count']);
        $this->assertGreaterThanOrEqual(3, $data['prodi_count']);
        $this->assertGreaterThanOrEqual(5, $data['dosen_count']);
        $this->assertGreaterThanOrEqual(4, $data['mata_kuliah_count']);
        $this->assertGreaterThanOrEqual(1, $data['kurikulum_count']);
        $this->assertGreaterThanOrEqual(2, $data['cpl_count']);
        $this->assertGreaterThanOrEqual(1, $data['periode_count']);
    }

    public function test_summary_returns_zero_counts_when_no_data(): void
    {
        $this->seedOnce();

        $controller = new MasterDataController();
        $response = $controller->summary();

        $data = $response->getData(true);

        $this->assertEquals(0, $data['fakultas_count']);
        $this->assertEquals(0, $data['prodi_count']);
        $this->assertEquals(0, $data['dosen_count']);
        $this->assertEquals(0, $data['mata_kuliah_count']);
        $this->assertEquals(0, $data['kurikulum_count']);
        $this->assertEquals(0, $data['cpl_count']);
        $this->assertEquals(0, $data['periode_count']);
    }
}
