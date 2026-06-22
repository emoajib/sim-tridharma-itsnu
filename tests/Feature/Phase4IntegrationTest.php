<?php

namespace Tests\Feature;

use App\Models\AuditMutu;
use App\Models\Edps;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\Rtm;
use App\Models\StandarMutu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class Phase4IntegrationTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_edps_auto_evaluate_success()
    {
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();
        $standar = StandarMutu::factory()->create();

        $edps = Edps::factory()->create([
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'standar_mutu_id' => $standar->id,
            'target' => 100,
            'bukti_file' => 'dummy/path.pdf'
        ]);

        Http::fake([
            '*analyze-document*' => Http::response([
                'suggested_score' => 85,
                'analysis' => 'Dokumen menunjukkan capaian baik, namun ada beberapa indikator belum optimal.'
            ], 200),
        ]);

        $response = $this->actingAs($this->admin())->postJson(route('spmi.edps.auto-evaluate', $edps));

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.capaian', '85.00');

        $edps->refresh();
        $this->assertEquals(85, $edps->capaian);
        $this->assertStringContainsString('Dokumen menunjukkan capaian baik', $edps->analisis);
    }

    public function test_rtm_auto_generate_creates_draft_and_action_items()
    {
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();
        $standar = StandarMutu::factory()->create();

        AuditMutu::factory()->create([
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'status' => 'verified',
            'judul_audit' => 'Audit Kurikulum',
        ]);

        Edps::factory()->create([
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'standar_mutu_id' => $standar->id,
            'capaian' => 75,
            'target' => 100
        ]);

        $response = $this->actingAs($this->admin())->postJson(route('spmi.rtm.auto-generate'), [
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        // Verify RTM created
        $rtm = Rtm::where('prodi_id', $prodi->id)->first();
        $this->assertNotNull($rtm);
        $this->assertEquals('draft', $rtm->status);
        
        // Verify 2 Action items created (1 for audit, 1 for edps)
        $this->assertCount(2, $rtm->actionItems);
    }
}
