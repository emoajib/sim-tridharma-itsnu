<?php

namespace Tests\Feature\Http;

use App\Models\Mitra;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MitraControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase, \Tests\Feature\SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    protected function routePrefix(): string
    {
        return 'mitra';
    }

    protected function modelClass(): string
    {
        return Mitra::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'nama_mitra' => 'PT Mitra Sejahtera',
            'jenis_mitra' => 'Industri',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'nama_mitra' => 'PT Mitra Sejahtera Updated',
            'jenis_mitra' => 'Industri',
        ];
    }

    protected function createRecord(): Mitra
    {
        return Mitra::create([
            'nama_mitra' => 'Mitra Lama',
            'jenis_mitra' => 'Pendidikan',
        ]);
    }
}
