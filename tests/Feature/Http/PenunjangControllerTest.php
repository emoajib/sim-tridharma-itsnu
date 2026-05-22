<?php

namespace Tests\Feature\Http;

use App\Models\Dosen;
use App\Models\Penunjang;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PenunjangControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase, \Tests\Feature\SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    protected function routePrefix(): string
    {
        return 'portofolio.penunjang';
    }

    protected function modelClass(): string
    {
        return Penunjang::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'nama_kegiatan' => 'Seminar',
            'jenis_kegiatan' => 'Workshop',
            'tahun' => '2025',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'nama_kegiatan' => 'Seminar Updated',
            'jenis_kegiatan' => 'Workshop',
            'tahun' => '2025',
        ];
    }

    protected function createRecord(): Penunjang
    {
        return Penunjang::create([
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'nama_kegiatan' => 'Seminar Lama',
            'jenis_kegiatan' => 'Pelatihan',
            'tahun' => '2024',
        ]);
    }
}
