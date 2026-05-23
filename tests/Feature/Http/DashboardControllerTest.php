<?php

namespace Tests\Feature\Http;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedRolePermission;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    public function test_unauthenticated_user_redirected(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertStatus(302);
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin())->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Dashboard'));
    }

    public function test_dashboard_redirects_to_portofolio_when_default_tab_set(): void
    {
        Setting::set('dashboard_default_tab', 'portofolio');

        $response = $this->actingAs($this->admin())->get(route('dashboard'));
        $response->assertStatus(302);
        $response->assertRedirect(route('portofolio'));
    }

    public function test_dashboard_redirects_to_bkd_when_default_tab_set(): void
    {
        Setting::set('dashboard_default_tab', 'bkd');

        $response = $this->actingAs($this->admin())->get(route('dashboard'));
        $response->assertStatus(302);
        $response->assertRedirect(route('bkd'));
    }

    public function test_dashboard_shows_overview_for_unknown_tab(): void
    {
        Setting::set('dashboard_default_tab', 'nonexistent');

        $response = $this->actingAs($this->admin())->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Dashboard'));
    }
}
