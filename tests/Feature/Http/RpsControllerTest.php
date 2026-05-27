<?php

namespace Tests\Feature\Http;

use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\Rps;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RpsControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'kurikulum.rps';
    }

    protected function modelClass(): string
    {
        return Rps::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'mata_kuliah_id' => MataKuliah::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'kode_rps' => 'RPS-001',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'mata_kuliah_id' => MataKuliah::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'kode_rps' => 'RPS-002',
            'status' => 'draft',
        ];
    }

    protected function createRecord(): Rps
    {
        return Rps::create([
            'mata_kuliah_id' => MataKuliah::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'kode_rps' => 'RPS-LAMA',
        ]);
    }
}
