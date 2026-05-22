<?php

namespace Tests\Feature\Http;

use App\Models\Dosen;
use App\Models\KegiatanPendidikan;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KegiatanPendidikanControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase, \Tests\Feature\SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    protected function routePrefix(): string
    {
        return 'portofolio.pendidikan';
    }

    protected function modelClass(): string
    {
        return KegiatanPendidikan::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'nama_kegiatan' => 'Mengajar',
            'jenis_kegiatan' => 'Teori',
            'sks' => 3,
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'nama_kegiatan' => 'Mengajar',
            'jenis_kegiatan' => 'Teori',
        ];
    }

    protected function createRecord(): KegiatanPendidikan
    {
        return KegiatanPendidikan::create([
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'nama_kegiatan' => 'Mengajar',
            'jenis_kegiatan' => 'Teori',
            'sks' => 3,
        ]);
    }
}
