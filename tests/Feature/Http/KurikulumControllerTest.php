<?php

namespace Tests\Feature\Http;

use App\Models\Kurikulum;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KurikulumControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase, \Tests\Feature\SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    protected function routePrefix(): string
    {
        return 'master-data.kurikulum';
    }

    protected function modelClass(): string
    {
        return Kurikulum::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'nama_kurikulum' => 'Kurikulum 2025',
            'prodi_id' => Prodi::factory()->create()->id,
            'tahun_berlaku' => '2025',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'nama_kurikulum' => 'Kurikulum 2026',
            'prodi_id' => Prodi::factory()->create()->id,
            'tahun_berlaku' => '2026',
        ];
    }

    protected function createRecord(): Kurikulum
    {
        return Kurikulum::create([
            'nama_kurikulum' => 'Kurikulum 2024',
            'prodi_id' => Prodi::factory()->create()->id,
            'tahun_berlaku' => '2024',
        ]);
    }
}
