<?php

namespace Tests\Feature\Http;

use App\Models\Kerjasama;
use App\Models\Mitra;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KerjasamaControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase, \Tests\Feature\SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    protected function routePrefix(): string
    {
        return 'kerjasama';
    }

    protected function modelClass(): string
    {
        return Kerjasama::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'mitra_id' => Mitra::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'jenis_kerjasama' => 'Penelitian',
            'nomor_mou' => 'MOU-001',
            'tanggal_mulai' => '2025-01-01',
            'tanggal_selesai' => '2025-12-31',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'mitra_id' => Mitra::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'jenis_kerjasama' => 'Penelitian',
            'nomor_mou' => 'MOU-002',
            'tanggal_mulai' => '2025-01-01',
            'tanggal_selesai' => '2025-12-31',
        ];
    }

    protected function createRecord(): Kerjasama
    {
        return Kerjasama::create([
            'mitra_id' => Mitra::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'jenis_kerjasama' => 'Pengabdian',
            'nomor_mou' => 'MOU-LAMA',
            'tanggal_mulai' => '2024-01-01',
            'tanggal_selesai' => '2024-12-31',
        ]);
    }
}
