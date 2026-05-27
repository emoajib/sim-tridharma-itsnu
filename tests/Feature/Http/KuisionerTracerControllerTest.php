<?php

namespace Tests\Feature\Http;

use App\Models\KuisionerTracer;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KuisionerTracerControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'tracer.kuisioner';
    }

    protected function modelClass(): string
    {
        return KuisionerTracer::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'prodi_id' => Prodi::factory()->create()->id,
            'judul_kuisioner' => 'Kuisioner 2025',
            'tahun' => '2025',
            'pertanyaan' => '[]',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'prodi_id' => Prodi::factory()->create()->id,
            'judul_kuisioner' => 'Kuisioner 2025 Updated',
            'tahun' => '2025',
            'pertanyaan' => '[]',
        ];
    }

    protected function createRecord(): KuisionerTracer
    {
        return KuisionerTracer::create([
            'prodi_id' => Prodi::factory()->create()->id,
            'judul_kuisioner' => 'Kuisioner Lama',
            'tahun' => '2024',
            'pertanyaan' => '[]',
        ]);
    }
}
