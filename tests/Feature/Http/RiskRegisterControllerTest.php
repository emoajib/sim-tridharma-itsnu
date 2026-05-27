<?php

namespace Tests\Feature\Http;

use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\RiskRegister;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RiskRegisterControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'spmi.risk';
    }

    protected function modelClass(): string
    {
        return RiskRegister::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'nama_risiko' => 'Risiko Baru',
            'kategori' => 'Operasional',
            'dampak' => 'sedang',
            'probabilitas' => 'sedang',
            'skor_risiko' => 'sedang',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'nama_risiko' => 'Risiko Updated',
            'kategori' => 'Operasional',
            'dampak' => 'sedang',
            'probabilitas' => 'sedang',
            'skor_risiko' => 'sedang',
            'status' => 'open',
        ];
    }

    protected function createRecord(): RiskRegister
    {
        return RiskRegister::create([
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'nama_risiko' => 'Risiko Lama',
            'kategori' => 'Operasional',
            'dampak' => 'rendah',
            'probabilitas' => 'rendah',
            'skor_risiko' => 'rendah',
        ]);
    }
}
