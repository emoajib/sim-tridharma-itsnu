<?php

namespace Tests\Feature\Controllers;

use App\Models\AgentPeringatanLog;
use App\Models\Bkd;
use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        $this->fakultas = Fakultas::create(['kode_fakultas' => 'FTI', 'nama_fakultas' => 'Fakultas Teknik']);
        $this->prodi = Prodi::create([
            'kode_prodi' => 'IF',
            'nama_prodi' => 'Informatika',
            'fakultas_id' => $this->fakultas->id,
            'jenjang' => 'S1',
        ]);
        $this->dosen = Dosen::create([
            'nidn' => '123456',
            'nama_depan' => 'Test',
            'prodi_id' => $this->prodi->id,
        ]);
        $this->periode = PeriodeAkademik::create([
            'kode_periode' => '2024/2025',
            'nama_periode' => 'TA 2024/2025',
        ]);
    }

    private function isSqlite(): bool
    {
        return DB::getDriverName() === 'sqlite';
    }

    // ─── GET STATS ────────────────────────────────────────────

    public function test_get_stats_returns_counts(): void
    {
        $stats = $this->service->getStats();

        $this->assertArrayHasKey('dosen_count', $stats);
        $this->assertArrayHasKey('prodi_count', $stats);
        $this->assertArrayHasKey('fakultas_count', $stats);
        $this->assertEquals(1, $stats['dosen_count']);
        $this->assertEquals(1, $stats['prodi_count']);
        $this->assertEquals(1, $stats['fakultas_count']);
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

    public function test_get_stats_scopes_by_fakultas(): void
    {
        $fakultasLain = Fakultas::create(['kode_fakultas' => 'FEB', 'nama_fakultas' => 'Ekonomi']);
        $prodiLain = Prodi::create([
            'kode_prodi' => 'MNJ',
            'nama_prodi' => 'Manajemen',
            'fakultas_id' => $fakultasLain->id,
            'jenjang' => 'S1',
        ]);
        Dosen::create(['nidn' => '999999', 'nama_depan' => 'Lain', 'prodi_id' => $prodiLain->id]);

        $stats = $this->service->getStats(['fakultas_id' => $this->fakultas->id]);

        $this->assertEquals(1, $stats['dosen_count']);
        $this->assertEquals(1, $stats['prodi_count']);
    }

    // ─── PRODI ACCREDITATION ──────────────────────────────────

    public function test_get_prodi_accreditation_returns_empty_when_no_data(): void
    {
        $result = $this->service->getProdiAccreditation(collect(), null);

        $this->assertEmpty($result);
    }

    public function test_get_prodi_accreditation_returns_empty_when_no_prodi(): void
    {
        $result = $this->service->getProdiAccreditation(collect(), $this->periode->id);

        $this->assertEmpty($result);
    }

    public function test_get_prodi_accreditation_returns_mapped_data_without_predictions(): void
    {
        if ($this->isSqlite()) {
            $this->markTestSkipped('getProdiAccreditation uses DISTINCT ON (PostgreSQL only)');
        }

        $prodis = Prodi::all();

        $result = $this->service->getProdiAccreditation($prodis, null);

        $this->assertCount(1, $result);
        $this->assertEquals('Informatika', $result[0]['nama']);
        $this->assertEquals('Fakultas Teknik', $result[0]['fakultas']);
        $this->assertEquals(0, $result[0]['skor_simulasi']);
    }

    // ─── CACHING ──────────────────────────────────────────────

    public function test_caching_works(): void
    {
        Cache::flush();

        $cacheKey = 'dashboard:test-user:' . md5(serialize([])) . ':0:0';

        $cached = Cache::get($cacheKey);
        $this->assertNull($cached);

        Cache::put($cacheKey, ['cached' => true], 300);
        $cached = Cache::get($cacheKey);
        $this->assertEquals(['cached' => true], $cached);
    }

    public function test_cache_expires_after_ttl(): void
    {
        Cache::put('test_key', 'value', 0);

        $this->assertNull(Cache::get('test_key'));
    }

    // ─── DEFAULT INSTRUMENT ───────────────────────────────────

    public function test_get_default_instrument_id_returns_zero_when_no_lembaga(): void
    {
        $id = $this->service->getDefaultInstrumentId();

        $this->assertEquals(0, $id);
    }

    // ─── BKD ──────────────────────────────────────────────────

    public function test_get_bkd_stats_aggregates_correctly(): void
    {
        $periodeLain = PeriodeAkademik::create(['kode_periode' => '2025/2026', 'nama_periode' => 'TA 2025/2026']);
        Bkd::create([
            'dosen_id' => $this->dosen->id, 'prodi_id' => $this->prodi->id,
            'periode_id' => $this->periode->id, 'status' => 'disetujui', 'total_sks' => 12,
        ]);
        Bkd::create([
            'dosen_id' => $this->dosen->id, 'prodi_id' => $this->prodi->id,
            'periode_id' => $periodeLain->id, 'status' => 'draft', 'total_sks' => 8,
        ]);

        $stats = $this->service->getBkdStats(null);

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['disetujui']);
        $this->assertEquals(1, $stats['draft']);
    }

    // ─── PERINGATAN ───────────────────────────────────────────

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
}
