<?php

namespace Tests\Unit\Models;

use App\Models\SpmiCycle;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
use App\Models\InstrumenAkreditasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SpmiCycleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_a_prodi()
    {
        $prodi = Prodi::factory()->create();
        $spmiCycle = SpmiCycle::factory()->create([
            'prodi_id' => $prodi->id,
        ]);

        $this->assertInstanceOf(Prodi::class, $spmiCycle->prodi);
        $this->assertEquals($prodi->id, $spmiCycle->prodi->id);
    }

    #[Test]
    public function it_belongs_to_a_periode()
    {
        $periode = PeriodeAkademik::factory()->create();
        $spmiCycle = SpmiCycle::factory()->create([
            'periode_id' => $periode->id,
        ]);

        $this->assertInstanceOf(PeriodeAkademik::class, $spmiCycle->periode);
        $this->assertEquals($periode->id, $spmiCycle->periode->id);
    }

    #[Test]
    public function it_belongs_to_an_instrumen()
    {
        $instrumen = InstrumenAkreditasi::factory()->create();
        $spmiCycle = SpmiCycle::factory()->create([
            'instrumen_id' => $instrumen->id,
        ]);

        $this->assertInstanceOf(InstrumenAkreditasi::class, $spmiCycle->instrumen);
        $this->assertEquals($instrumen->id, $spmiCycle->instrumen->id);
    }

    #[Test]
    public function it_can_be_created_with_all_required_fields()
    {
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();
        $instrumen = InstrumenAkreditasi::factory()->create();

        $spmiCycle = SpmiCycle::create([
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'instrumen_id' => $instrumen->id,
            'tahap' => 'penetapan',
            'kategori' => 'Akademik',
            'nama_siklus' => 'SPMI Ganjil 2026',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonth(),
            'persentase_selesai' => 0,
            'status' => 'planned',
            'catatan' => 'Test cycle',
        ]);

        $this->assertDatabaseHas('spmi_cycles', [
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'instrumen_id' => $instrumen->id,
            'tahap' => 'penetapan',
            'nama_siklus' => 'SPMI Ganjil 2026',
        ]);
    }
}