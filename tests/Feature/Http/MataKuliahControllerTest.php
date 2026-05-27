<?php

namespace Tests\Feature\Http;

use App\Models\MataKuliah;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MataKuliahControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'master-data.mata-kuliah';
    }

    protected function modelClass(): string
    {
        return MataKuliah::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'kode_mk' => 'MK001',
            'nama_mk' => 'Algoritma',
            'prodi_id' => Prodi::factory()->create()->id,
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'kode_mk' => 'MK999',
            'nama_mk' => 'Algoritma',
            'prodi_id' => Prodi::factory()->create()->id,
        ];
    }

    protected function createRecord(): MataKuliah
    {
        return MataKuliah::factory()->create();
    }
}
