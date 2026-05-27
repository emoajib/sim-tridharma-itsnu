<?php

namespace Tests\Feature\Http;

use App\Models\Bkd;
use App\Models\Dosen;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BkdControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'bkd';
    }

    protected function modelClass(): string
    {
        return Bkd::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'total_sks' => 3,
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'total_sks' => 2,
        ];
    }

    protected function createRecord(): Bkd
    {
        return Bkd::create([
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'total_sks' => 3,
        ]);
    }
}
