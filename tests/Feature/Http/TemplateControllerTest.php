<?php

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class TemplateControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_user_redirected_from_index(): void
    {
        $response = $this->get(route('admin.templates.index'));
        $response->assertStatus(302);
    }

    public function test_unauthenticated_user_redirected_from_download(): void
    {
        $response = $this->get(route('admin.templates.download', 'test.xlsx'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403_on_index(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->get(route('admin.templates.index'));
        $response->assertStatus(403);
    }

    public function test_unauthorized_user_gets_403_on_download(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->get(route('admin.templates.download', 'test.xlsx'));
        $response->assertStatus(403);
    }

    public function test_index_returns_200_for_authorized_user(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.templates.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/Templates/Index'));
    }

    public function test_download_fails_for_non_existent_file(): void
    {
        $response = $this->actingAs($this->admin())
            ->from(route('admin.templates.index'))
            ->get(route('admin.templates.download', 'non-existent-file.xlsx'));

        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }
}
