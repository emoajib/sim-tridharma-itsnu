<?php

namespace Tests\Feature\Services;

use App\Models\AuditMutu;
use App\Models\Capa;
use App\Services\SPMI\CapaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CapaServiceTest extends TestCase
{
    use RefreshDatabase;

    private CapaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CapaService::class);
    }

    #[Test]
    public function it_creates_capa_from_audit()
    {
        $audit = AuditMutu::factory()->create(['severity' => 'berat']);

        $capa = $this->service->createFromAudit($audit);

        $this->assertInstanceOf(Capa::class, $capa);
        $this->assertEquals($audit->id, $capa->audit_mutu_id);
        $this->assertEquals('open', $capa->status);
    }

    #[Test]
    public function it_does_not_create_capa_for_minor_finding()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CAPA can only be created from findings with severity >= sedang');

        $audit = AuditMutu::factory()->create(['severity' => 'ringan']);

        $this->service->createFromAudit($audit);
    }

    #[Test]
    public function it_prevents_duplicate_capa()
    {
        $audit = AuditMutu::factory()->create(['severity' => 'berat']);

        $this->service->createFromAudit($audit);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CAPA already exists for audit');

        $this->service->createFromAudit($audit);
    }
}
