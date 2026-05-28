<?php

namespace Tests\Feature;

use App\Events\AuditStatusChanged;
use App\Models\AuditHistory;
use App\Models\AuditMutu;
use App\Models\CascadingIku;
use App\Models\IndikatorIku;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\User;
use App\Models\UsulanRkat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SpmiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_finding_syncs_to_iku()
    {
        $user = User::factory()->create();
        $periode = PeriodeAkademik::factory()->create();
        $prodi = Prodi::factory()->create();

        // Setup IKU 1
        $iku = IndikatorIku::factory()->create(['kode_iku' => 'IKU 1']);
        
        $cascading = CascadingIku::factory()->create([
            'iku_id' => $iku->id,
            'unit_type' => 'Prodi',
            'unit_id' => $prodi->id,
            'periode_id' => $periode->id,
            'catatan' => 'Initial notes',
        ]);

        // Create Audit with IKU 1 trigger keyword "pekerjaan layak"
        $audit = AuditMutu::factory()->create([
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'judul_audit' => 'Audit Lulusan',
            'temuan' => 'Perlu peningkatan program agar lulusan mendapat pekerjaan layak lebih cepat.',
            'status' => 'draft',
        ]);

        // Manually trigger the event that our listener listens to
        event(new AuditStatusChanged($audit, 'draft', 'verified', $user));

        // Assert Cascading IKU was updated
        $cascading->refresh();
        $this->assertStringContainsString('Terkait temuan audit', $cascading->catatan);
        $this->assertStringContainsString('Audit Lulusan', $cascading->catatan);

        // Assert History was created
        $this->assertDatabaseHas('audit_histories', [
            'audit_mutu_id' => $audit->id,
            'action' => 'iku_integration_updated',
            'new_value' => 'Synced to IKU: IKU 1',
        ]);
    }

    public function test_audit_finding_syncs_to_rkat()
    {
        $user = User::factory()->create();
        $periode = PeriodeAkademik::factory()->create();
        $prodi = Prodi::factory()->create();

        $rkat = UsulanRkat::factory()->create([
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'komentar_reviewer' => 'Initial comment',
            'user_id' => $user->id,
        ]);

        // Create Audit with RKAT trigger keyword "pengadaan sarana"
        $audit = AuditMutu::factory()->create([
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'judul_audit' => 'Audit Fasilitas',
            'temuan' => 'Dibutuhkan pengadaan sarana laboratorium baru.',
            'rekomendasi' => 'Beli mikroskop.',
            'status' => 'draft',
        ]);

        // Trigger event
        event(new AuditStatusChanged($audit, 'draft', 'closed', $user));

        // Assert RKAT was updated
        $rkat->refresh();
        $this->assertStringContainsString('[AI Sync]', $rkat->komentar_reviewer);
        $this->assertStringContainsString('Beli mikroskop', $rkat->komentar_reviewer);

        // Assert History was created
        $this->assertDatabaseHas('audit_histories', [
            'audit_mutu_id' => $audit->id,
            'action' => 'rkat_integration_updated',
        ]);
    }
}
