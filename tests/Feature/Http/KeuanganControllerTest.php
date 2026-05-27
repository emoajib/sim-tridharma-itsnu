<?php

namespace Tests\Feature\Http;

use App\Models\Dosen;
use App\Models\Keuangan;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KeuanganControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'keuangan';
    }

    protected function modelClass(): string
    {
        return Keuangan::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'jenis_dana' => 'Bantuan',
            'jumlah' => 1000000,
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'jenis_dana' => 'Bantuan',
            'jumlah' => 2000000,
        ];
    }

    protected function createRecord(): Keuangan
    {
        return Keuangan::create([
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'jenis_dana' => 'Hibah',
            'jumlah' => 500000,
        ]);
    }
}
