<?php

namespace Tests\Feature\Http;

use App\Models\Dosen;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DosenControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase, \Tests\Feature\SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

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
}
