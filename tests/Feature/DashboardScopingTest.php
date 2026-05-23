<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\User;
use App\Models\KegiatanPendidikan;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardScopingTest extends TestCase
{
    use RefreshDatabase;

    private DashboardService $service;
    private $periode;
    private $fakultas;
    private $prodi1;
    private $prodi2;
    private $dosen1;
    private $dosen2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardService();
        
        $this->periode = PeriodeAkademik::factory()->create(['is_active' => true]);
        $this->fakultas = Fakultas::factory()->create();
        
        $this->prodi1 = Prodi::factory()->create(['fakultas_id' => $this->fakultas->id]);
        $this->prodi2 = Prodi::factory()->create(['fakultas_id' => $this->fakultas->id]);
        
        $this->dosen1 = Dosen::factory()->create(['prodi_id' => $this->prodi1->id]);
        $this->dosen2 = Dosen::factory()->create(['prodi_id' => $this->prodi2->id]);
        
        // Create some data
        KegiatanPendidikan::create([
            'dosen_id' => $this->dosen1->id,
            'prodi_id' => $this->prodi1->id,
            'periode_id' => $this->periode->id,
            'nama_kegiatan' => 'Pendidikan Dosen 1',
            'jenis_kegiatan' => 'Kuliah',
            'sks' => 3
        ]);
        
        KegiatanPendidikan::create([
            'dosen_id' => $this->dosen2->id,
            'prodi_id' => $this->prodi2->id,
            'periode_id' => $this->periode->id,
            'nama_kegiatan' => 'Pendidikan Dosen 2',
            'jenis_kegiatan' => 'Kuliah',
            'sks' => 3
        ]);
    }

    public function test_stats_are_scoped_for_kaprodi(): void
    {
        $scope = ['prodi_id' => $this->prodi1->id];
        
        $stats = $this->service->getStats($scope);
        $portofolio = $this->service->getPortofolioStats($this->periode->id, $scope);
        
        // In prodi1, there is 1 dosen and 1 prodi (itself)
        $this->assertEquals(1, $stats['dosen_count']);
        $this->assertEquals(1, $stats['prodi_count']);
        
        // In prodi1, there is 1 pendidikan activity
        $this->assertEquals(1, $portofolio['pendidikan_count']);
    }

    public function test_stats_are_scoped_for_dosen(): void
    {
        $scope = ['dosen_id' => $this->dosen1->id];
        
        $stats = $this->service->getStats($scope);
        $portofolio = $this->service->getPortofolioStats($this->periode->id, $scope);
        
        // For a Dosen scope, dosen_count should be 1
        $this->assertEquals(1, $stats['dosen_count']);
        
        // For a Dosen scope, only their activities are counted
        $this->assertEquals(1, $portofolio['pendidikan_count']);
    }

    public function test_stats_are_global_for_admin(): void
    {
        $scope = []; // Global scope
        
        $stats = $this->service->getStats($scope);
        $portofolio = $this->service->getPortofolioStats($this->periode->id, $scope);
        
        // Globally there are 2 dosens and 2 prodis
        $this->assertEquals(2, $stats['dosen_count']);
        $this->assertEquals(2, $stats['prodi_count']);
        
        // Globally there are 2 pendidikan activities
        $this->assertEquals(2, $portofolio['pendidikan_count']);
    }

    public function test_stats_are_scoped_for_dekan(): void
    {
        $scope = ['fakultas_id' => $this->fakultas->id];
        
        $stats = $this->service->getStats($scope);
        
        // In this fakultas, there are 2 prodis and 2 dosens
        $this->assertEquals(2, $stats['dosen_count']);
        $this->assertEquals(2, $stats['prodi_count']);
        $this->assertEquals(1, $stats['fakultas_count']);
    }
}
