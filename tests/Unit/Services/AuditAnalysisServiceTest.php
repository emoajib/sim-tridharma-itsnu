<?php

namespace Tests\Unit\Services;

use App\Models\AuditMutu;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Services\SPMI\AuditAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditAnalysisService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuditAnalysisService();
    }

    #[Test]
    public function it_calculates_prodi_score()
    {
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();

        AuditMutu::factory()->count(5)->create([
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'severity' => 'ringan',
            'status' => 'closed',
        ]);
        AuditMutu::factory()->count(2)->create([
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'severity' => 'sedang',
            'status' => 'closed',
        ]);

        $score = $this->service->getProdiScore($prodi->id, $periode->id);

        $this->assertIsArray($score);
        $this->assertArrayHasKey('score', $score);
        $this->assertArrayHasKey('total_temuan', $score);
        $this->assertArrayHasKey('deduction_detail', $score);
        $this->assertEquals(7, $score['total_temuan']);
        $this->assertGreaterThan(0, $score['score']);
    }

    #[Test]
    public function it_returns_zero_score_when_no_data()
    {
        $score = $this->service->getProdiScore(999, 999);

        $this->assertIsArray($score);
        $this->assertEquals(100, $score['score']);
        $this->assertEquals(0, $score['total_temuan']);
    }

    #[Test]
    public function it_detects_early_warnings()
    {
        $warnings = $this->service->getEarlyWarning();
        $this->assertIsArray($warnings);
        $this->assertArrayHasKey('kritis_findings', $warnings);
        $this->assertArrayHasKey('temuan_tanpa_capa', $warnings);
        $this->assertArrayHasKey('deadline_approaching', $warnings);
    }
}
