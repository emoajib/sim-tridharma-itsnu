<?php

namespace Tests\Feature\Http;

use App\Models\Fakultas;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FakultasControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'master-data.fakultas';
    }

    protected function modelClass(): string
    {
        return Fakultas::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'kode_fakultas' => 'FT-001',
            'nama_fakultas' => 'Fakultas Teknik',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'kode_fakultas' => 'FT-999',
            'nama_fakultas' => 'Fakultas Teknik',
        ];
    }

    protected function createRecord(): Fakultas
    {
        return Fakultas::factory()->create();
    }

    public function test_kaprodi_cannot_delete_fakultas(): void
    {
        $fakultas = Fakultas::factory()->create();

        $response = $this->actingAs($this->kaprodi())
            ->delete(route('master-data.fakultas.destroy', $fakultas));

        $response->assertStatus(403);
    }

    public function test_index_can_search_fakultas(): void
    {
        Fakultas::factory()->create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK']);
        Fakultas::factory()->create(['kode_fakultas' => 'FEB', 'nama_fakultas' => 'Ekonomi']);

        $response = $this->actingAs($this->admin())
            ->get(route('master-data.fakultas', ['search' => 'FTIK']));

        $response->assertStatus(200);
    }
}
