<?php

namespace Tests\Feature;

use App\Models\AgentPeringatanLog;
use App\Models\Bkd;
use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardService $service;

    private $fakultas;

    private $prodi;

    private $dosen;

    private $periode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardService;
        $this->fakultas = Fakultas::create(['kode_fakultas' => 'FTI', 'nama_fakultas' => 'Teknik']);
        $this->prodi = Prodi::create([
            'kode_prodi' => 'IF', 'nama_prodi' => 'Informatika',
            'fakultas_id' => $this->fakultas->id, 'jenjang' => 'S1',
        ]);
        $this->dosen = Dosen::create(['nidn' => '123456', 'nama_depan' => 'Test', 'prodi_id' => $this->prodi->id]);
        $this->periode = PeriodeAkademik::create(['kode_periode' => '2024/2025', 'nama_periode' => 'TA 2024/2025']);
    }

    public function test_get_stats_returns_zero_when_empty(): void
    {
        Dosen::truncate();
        Prodi::truncate();
        Fakultas::truncate();
        $stats = $this->service->getStats();

        $this->assertEquals(0, $stats['dosen_count']);
        $this->assertEquals(0, $stats['prodi_count']);
        $this->assertEquals(0, $stats['fakultas_count']);
    }

    public function test_get_bkd_stats_aggregates_correctly(): void
    {
        Bkd::create([
            'dosen_id' => $this->dosen->id, 'prodi_id' => $this->prodi->id,
            'periode_id' => $this->periode->id, 'status' => 'disetujui', 'total_sks' => 12,
        ]);
        Bkd::create([
            'dosen_id' => $this->dosen->id, 'prodi_id' => $this->prodi->id,
            'periode_id' => $this->periode->id, 'status' => 'draft', 'total_sks' => 8,
        ]);

        $stats = $this->service->getBkdStats(null);

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['disetujui']);
        $this->assertEquals(1, $stats['draft']);
    }

    public function test_get_peringatan_stats_aggregates_correctly(): void
    {
        AgentPeringatanLog::create([
            'agent' => 'test', 'prodi_id' => $this->prodi->id, 'tingkat' => 'warning',
            'jenis_peringatan' => 'test', 'pesan' => 'test', 'is_read' => false,
        ]);
        AgentPeringatanLog::create([
            'agent' => 'test', 'prodi_id' => $this->prodi->id, 'tingkat' => 'critical',
            'jenis_peringatan' => 'test', 'pesan' => 'test', 'is_read' => true,
        ]);

        $stats = $this->service->getPeringatanStats();

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['critical']);
        $this->assertEquals(1, $stats['warning']);
        $this->assertEquals(1, $stats['unread']);
    }

    public function test_get_prodi_accreditation_returns_empty_without_prodi(): void
    {
        $result = $this->service->getProdiAccreditation(collect(), null);
        $this->assertEmpty($result);
    }

    public function test_get_default_instrument_id_returns_zero_when_no_lembaga(): void
    {
        // Truncate to test the fallback
        $this->fakultas->delete();
        Prodi::truncate();
        $id = $this->service->getDefaultInstrumentId();
        $this->assertEquals(0, $id);
    }
}
