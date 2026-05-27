<?php

namespace Tests\Feature\Http;

use App\Models\Fakultas;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProdiControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'master-data.prodi';
    }

    protected function modelClass(): string
    {
        return Prodi::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'kode_prodi' => 'TI-001',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => Fakultas::factory()->create()->id,
            'jenjang' => 'S1',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'kode_prodi' => 'TI-999',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => Fakultas::factory()->create()->id,
            'jenjang' => 'S1',
        ];
    }

    protected function createRecord(): Prodi
    {
        return Prodi::factory()->create();
    }
}
