<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace Tests\Feature\Services;

use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\RkatPagu;
use App\Models\User;
use App\Models\UsulanRkat;
use App\Services\Rkat\RkatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RkatServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RkatService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RkatService;
    }

    public function test_can_submit_usulan()
    {
        $user = User::factory()->create();
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();

        $data = [
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'judul_kegiatan' => 'Seminar AI',
            'estimasi_biaya' => 5000000,
        ];

        $usulan = $this->service->submitUsulan($data, $user->id);

        $this->assertDatabaseHas('trx_usulan_rkat', [
            'id' => $usulan->id,
            'status' => 'submitted',
        ]);
    }

    public function test_cannot_approve_if_pagu_not_enough()
    {
        $user = User::factory()->create();
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();

        // Setup Pagu
        RkatPagu::create([
            'unit_type' => 'Prodi',
            'unit_id' => $prodi->id,
            'periode_id' => $periode->id,
            'pagu_total' => 10000000,
            'terpakai' => 8000000, // Sisa 2jt
        ]);

        $usulan = UsulanRkat::create([
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'judul_kegiatan' => 'Kegiatan Mahal',
            'estimasi_biaya' => 3000000, // Butuh 3jt
            'user_id' => $user->id,
            'status' => 'submitted',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Plafon anggaran tidak mencukupi');

        $this->service->processApproval($usulan->id, 'approve', $user->id);
    }

    public function test_can_approve_and_update_pagu()
    {
        $user = User::factory()->create();
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();

        // Setup Pagu
        $pagu = RkatPagu::create([
            'unit_type' => 'Prodi',
            'unit_id' => $prodi->id,
            'periode_id' => $periode->id,
            'pagu_total' => 10000000,
            'terpakai' => 2000000, // Sisa 8jt
        ]);

        $usulan = UsulanRkat::create([
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'judul_kegiatan' => 'Kegiatan Murah',
            'estimasi_biaya' => 3000000, // Butuh 3jt
            'user_id' => $user->id,
            'status' => 'submitted',
        ]);

        $this->service->processApproval($usulan->id, 'approve', $user->id);

        $this->assertDatabaseHas('trx_usulan_rkat', [
            'id' => $usulan->id,
            'status' => 'approved',
        ]);

        $this->assertEquals(5000000, $pagu->fresh()->terpakai);
    }
}
