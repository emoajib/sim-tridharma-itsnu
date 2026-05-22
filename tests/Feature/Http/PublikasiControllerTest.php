<?php

namespace Tests\Feature\Http;

use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\Publikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PublikasiControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase, \Tests\Feature\SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    protected function routePrefix(): string
    {
        return 'portofolio.publikasi';
    }

    protected function modelClass(): string
    {
        return Publikasi::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'judul_publikasi' => 'Publikasi AI',
            'jenis_publikasi' => 'Jurnal',
            'tingkat' => 'Nasional',
            'tahun' => '2025',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'judul_publikasi' => 'Publikasi AI Updated',
            'jenis_publikasi' => 'Jurnal',
            'tingkat' => 'Nasional',
            'tahun' => '2025',
        ];
    }

    protected function createRecord(): Publikasi
    {
        return Publikasi::create([
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'judul_publikasi' => 'Publikasi Lama',
            'jenis_publikasi' => 'Prosiding',
            'tingkat' => 'Lokal',
            'tahun' => '2024',
        ]);
    }
}
