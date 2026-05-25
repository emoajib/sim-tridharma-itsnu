<?php

namespace Tests\Feature\Http;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\SeedRolePermission;
use Tests\TestCase;

class AdminSettingControllerTest extends TestCase
{
    use RefreshDatabase, SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('admin.settings'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->get(route('admin.settings'));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_settings_page(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.settings'));
        $response->assertStatus(200);
    }

    public function test_update_multiple_saves_settings(): void
    {
        Setting::set('theme_color', 'indigo');
        Setting::set('chat_enabled', true);

        $response = $this->actingAs($this->admin())
            ->from(route('admin.settings'))
            ->post(route('admin.settings.update'), [
                'settings' => [
                    'theme_color' => 'blue',
                    'chat_enabled' => false,
                ],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertEquals('blue', Setting::get('theme_color'));
        $this->assertFalse(Setting::get('chat_enabled'));
    }

    public function test_remove_gemini_api_key(): void
    {
        Setting::set('gemini_api_key', 'AIzaSyTestKey123');

        $response = $this->actingAs($this->admin())
            ->from(route('admin.settings'))
            ->delete(route('admin.settings.api-key.remove'), ['provider' => 'gemini']);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertNull(Setting::get('gemini_api_key'));
    }

    public function test_remove_openai_api_key(): void
    {
        Setting::set('openai_api_key', 'sk-test123');

        $response = $this->actingAs($this->admin())
            ->from(route('admin.settings'))
            ->delete(route('admin.settings.api-key.remove'), ['provider' => 'openai']);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertNull(Setting::get('openai_api_key'));
    }

    public function test_test_api_key_requires_key(): void
    {
        $response = $this->actingAs($this->admin())
            ->postJson(route('admin.settings.api-key.test'), [
                'api_key' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_test_gemini_api_key_success(): void
    {
        Http::fake([
            '*/models?key=*' => Http::response(['models' => []], 200),
        ]);

        $response = $this->actingAs($this->admin())
            ->postJson(route('admin.settings.api-key.test'), [
                'api_key' => 'AIzaSyValidKey',
                'provider' => 'gemini',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_test_gemini_api_key_failure(): void
    {
        Http::fake([
            '*/models?key=*' => Http::response([
                'error' => ['message' => 'API_KEY_INVALID'],
            ], 403),
        ]);

        $response = $this->actingAs($this->admin())
            ->postJson(route('admin.settings.api-key.test'), [
                'api_key' => 'AIzaSyInvalidKey',
                'provider' => 'gemini',
            ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_test_openai_api_key_success(): void
    {
        Http::fake([
            '*/models' => Http::response(['data' => []], 200),
        ]);

        $response = $this->actingAs($this->admin())
            ->postJson(route('admin.settings.api-key.test'), [
                'api_key' => 'sk-valid',
                'provider' => 'openai',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_test_api_key_handles_connection_error(): void
    {
        Http::fake([
            '*/models?key=*' => Http::response(null, 500),
        ]);

        $response = $this->actingAs($this->admin())
            ->postJson(route('admin.settings.api-key.test'), [
                'api_key' => 'AIzaSyTest',
                'provider' => 'gemini',
            ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }
}
