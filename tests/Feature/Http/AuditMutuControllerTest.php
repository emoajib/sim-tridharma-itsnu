<?php

namespace Tests\Feature\Http;

use App\Models\AuditMutu;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditMutuControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'spmi.audit';
    }

    protected function modelClass(): string
    {
        return AuditMutu::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'judul_audit' => 'Audit Mutu Internal',
            'tanggal_audit' => '2025-01-01',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'judul_audit' => 'Audit Mutu Internal Updated',
            'tanggal_audit' => '2025-01-01',
            'status' => 'open',
        ];
    }

    protected function createRecord(): AuditMutu
    {
        return AuditMutu::create([
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'judul_audit' => 'Audit Mutu Lama',
            'tanggal_audit' => '2024-06-01',
        ]);
    }
}
