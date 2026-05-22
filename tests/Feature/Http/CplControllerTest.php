<?php

namespace Tests\Feature\Http;

use App\Models\Cpl;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CplControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase, \Tests\Feature\SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    protected function routePrefix(): string
    {
        return 'master-data.cpl';
    }

    protected function modelClass(): string
    {
        return Cpl::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'kode_cpl' => 'CPL-01',
            'prodi_id' => Prodi::factory()->create()->id,
            'deskripsi' => 'Deskripsi CPL',
            'jenis' => 'Sikap',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'kode_cpl' => 'CPL-02',
            'prodi_id' => Prodi::factory()->create()->id,
            'deskripsi' => 'Deskripsi CPL Updated',
            'jenis' => 'Pengetahuan',
        ];
    }

    protected function createRecord(): Cpl
    {
        return Cpl::create([
            'kode_cpl' => 'CPL-A1',
            'prodi_id' => Prodi::factory()->create()->id,
            'deskripsi' => 'Deskripsi CPL',
            'jenis' => 'Sikap',
        ]);
    }
}
