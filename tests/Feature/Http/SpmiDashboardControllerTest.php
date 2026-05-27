<?php

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\SeedRolePermission;
use Tests\TestCase;

class SpmiDashboardControllerTest extends TestCase
{
    use RefreshDatabase, SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    #[Test]
    public function it_returns_dashboard_page()
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('SPMI dashboard requires PostgreSQL');
        }

        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $response = $this->actingAs($user)->get(route('spmi.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Spmi/Dashboard'));
    }

    #[Test]
    public function it_returns_chart_data()
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('SPMI dashboard requires PostgreSQL');
        }

        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $response = $this->actingAs($user)->get(route('spmi.dashboard.chart-data'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'temuan_per_standar',
                'temuan_per_bulan',
                'severity_distribution',
            ],
        ]);
    }
}
