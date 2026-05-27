<?php

namespace Tests\Feature\Http;

use App\Models\Dosen;
use App\Models\PeriodeAkademik;
use App\Models\Pkm;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PkmControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'portofolio.pkm';
    }

    protected function modelClass(): string
    {
        return Pkm::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'judul_pkm' => 'PKM AI',
            'jenis_pkm' => 'Pengabdian',
            'tahun_pelaksanaan' => '2025',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'judul_pkm' => 'PKM AI Updated',
            'jenis_pkm' => 'Pengabdian',
            'tahun_pelaksanaan' => '2025',
        ];
    }

    protected function createRecord(): Pkm
    {
        return Pkm::create([
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'judul_pkm' => 'PKM Lama',
            'jenis_pkm' => 'Penerapan',
            'tahun_pelaksanaan' => '2024',
        ]);
    }
}
