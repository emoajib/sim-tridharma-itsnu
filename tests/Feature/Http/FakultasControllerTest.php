<?php

namespace Tests\Feature\Http;

use App\Models\Fakultas;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FakultasControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase, \Tests\Feature\SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

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
}
