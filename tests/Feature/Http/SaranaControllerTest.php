<?php

namespace Tests\Feature\Http;

use App\Models\Prodi;
use App\Models\Sarana;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SaranaControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'sarpras';
    }

    protected function modelClass(): string
    {
        return Sarana::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'prodi_id' => Prodi::factory()->create()->id,
            'nama_sarana' => 'Lab Komputer',
            'jenis_sarana' => 'Laboratorium',
            'jumlah' => 10,
            'kondisi' => 'baik',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'prodi_id' => Prodi::factory()->create()->id,
            'nama_sarana' => 'Lab Komputer Updated',
            'jenis_sarana' => 'Laboratorium',
            'jumlah' => 10,
            'kondisi' => 'baik',
        ];
    }

    protected function createRecord(): Sarana
    {
        return Sarana::create([
            'prodi_id' => Prodi::factory()->create()->id,
            'nama_sarana' => 'Lab Lama',
            'jenis_sarana' => 'Laboratorium',
            'jumlah' => 5,
            'kondisi' => 'sedang',
        ]);
    }
}
