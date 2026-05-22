<?php

namespace Tests\Feature\Http;

use App\Models\Dosen;
use App\Models\Penelitian;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PenelitianControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase, \Tests\Feature\SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    protected function routePrefix(): string
    {
        return 'portofolio.penelitian';
    }

    protected function modelClass(): string
    {
        return Penelitian::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'judul_penelitian' => 'Penelitian AI',
            'jenis_penelitian' => 'Terapan',
            'tahun_pelaksanaan' => '2025',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'judul_penelitian' => 'Penelitian AI Updated',
            'jenis_penelitian' => 'Terapan',
        ];
    }

    protected function createRecord(): Penelitian
    {
        return Penelitian::create([
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'judul_penelitian' => 'Penelitian Lama',
            'jenis_penelitian' => 'Dasar',
            'tahun_pelaksanaan' => '2024',
        ]);
    }
}
