<?php

namespace Tests\Feature\Http;

use App\Models\Dosen;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DosenControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'master-data.dosen';
    }

    protected function modelClass(): string
    {
        return Dosen::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'nidn' => '1111111111',
            'nama_depan' => 'John',
            'prodi_id' => Prodi::factory()->create()->id,
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'nidn' => '9999999999',
            'nama_depan' => 'John',
            'prodi_id' => Prodi::factory()->create()->id,
        ];
    }

    protected function createRecord(): Dosen
    {
        return Dosen::factory()->create();
    }

    public function test_store_fails_without_prodi(): void
    {
        $data = ['nidn' => '1234567890', 'nama_depan' => 'John'];

        $response = $this->actingAs($this->admin())
            ->from(route('master-data.dosen'))
            ->post(route('master-data.dosen.store'), $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('prodi_id');
    }

    public function test_index_can_search_dosen(): void
    {
        $prodi = Prodi::factory()->create();
        Dosen::factory()->create(['nama_depan' => 'John', 'prodi_id' => $prodi->id]);
        Dosen::factory()->create(['nama_depan' => 'Jane', 'prodi_id' => $prodi->id]);

        $response = $this->actingAs($this->admin())
            ->get(route('master-data.dosen', ['search' => 'John']));

        $response->assertStatus(200);
    }
}
