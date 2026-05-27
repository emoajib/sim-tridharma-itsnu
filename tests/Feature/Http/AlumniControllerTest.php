<?php

namespace Tests\Feature\Http;

use App\Models\Alumni;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AlumniControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'alumni';
    }

    protected function modelClass(): string
    {
        return Alumni::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'prodi_id' => Prodi::factory()->create()->id,
            'nim' => '123456',
            'nama' => 'John Doe',
            'tahun_lulus' => '2025',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'prodi_id' => Prodi::factory()->create()->id,
            'nim' => '123456',
            'nama' => 'Jane Doe',
            'tahun_lulus' => '2025',
        ];
    }

    protected function createRecord(): Alumni
    {
        return Alumni::create([
            'prodi_id' => Prodi::factory()->create()->id,
            'nim' => '654321',
            'nama' => 'John Smith',
            'tahun_lulus' => '2024',
        ]);
    }
}
