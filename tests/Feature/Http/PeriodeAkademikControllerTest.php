<?php

namespace Tests\Feature\Http;

use App\Models\PeriodeAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PeriodeAkademikControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase, \Tests\Feature\SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    protected function routePrefix(): string
    {
        return 'master-data.periode-akademik';
    }

    protected function modelClass(): string
    {
        return PeriodeAkademik::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'kode_periode' => '20251',
            'nama_periode' => 'Semester Ganjil 2025',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'kode_periode' => '20252',
            'nama_periode' => 'Semester Genap 2025',
        ];
    }

    protected function createRecord(): PeriodeAkademik
    {
        return PeriodeAkademik::factory()->create();
    }
}
