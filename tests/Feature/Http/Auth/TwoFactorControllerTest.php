<?php

namespace Tests\Feature\Http\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class TwoFactorControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    // ── Unauthenticated ─────────────────────────────────────────────

    public function test_unauthenticated_user_cannot_access_setup(): void
    {
        $this->get(route('2fa.setup'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_access_challenge(): void
    {
        $this->get(route('2fa.challenge'))
            ->assertRedirect(route('login'));
    }

    // ── Setup Form ──────────────────────────────────────────────────

    public function test_setup_form_shows_secret_when_not_enabled(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('2fa.setup'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/TwoFactorSetup')
            ->has('secret')
            ->has('qrCodeUrl')
        );
    }

    public function test_setup_form_shows_enabled_state_when_already_enabled(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'test-secret',
            'two_factor_recovery_codes' => json_encode(['code1', 'code2']),
        ]);

        $response = $this->actingAs($user)->get(route('2fa.setup'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/TwoFactorSetup')
            ->where('enabled', true)
            ->has('recoveryCodes')
        );
    }

    // ── Confirm Setup ────────────────────────────────────────────────

    public function test_confirm_setup_with_valid_code(): void
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();
        $validOtp = $google2fa->getCurrentOtp($secret);

        $user = User::factory()->create();

        Cache::put('2fa:setup:' . $user->id, $secret, now()->addMinutes(10));

        $response = $this->actingAs($user)->post(route('2fa.confirm'), ['code' => $validOtp]);

        $response->assertStatus(200);
        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertNotNull($user->two_factor_recovery_codes);
    }

    public function test_confirm_setup_with_expired_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('2fa.confirm'), ['code' => '123456']);

        $response->assertSessionHasErrors('code');
    }

    public function test_confirm_setup_with_invalid_code(): void
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user = User::factory()->create();

        Cache::put('2fa:setup:' . $user->id, $secret, now()->addMinutes(10));

        $response = $this->actingAs($user)->post(route('2fa.confirm'), ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $user->refresh();
        $this->assertFalse($user->two_factor_enabled);
    }

    // ── Disable ─────────────────────────────────────────────────────

    public function test_disable_2fa_with_valid_password(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'test-secret',
            'two_factor_recovery_codes' => json_encode([]),
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('2fa.disable'), [
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Two-factor authentication disabled.']);
        $user->refresh();
        $this->assertFalse($user->two_factor_enabled);
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_disable_2fa_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'test-secret',
            'two_factor_recovery_codes' => json_encode([]),
        ]);

        $response = $this->actingAs($user)->post(route('2fa.disable'), [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');
        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
    }

    // ── Verify (Challenge) ─────────────────────────────────────────

    public function test_verify_with_valid_code(): void
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();
        $validOtp = $google2fa->getCurrentOtp($secret);

        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        $response = $this->actingAs($user)->post(route('2fa.verify'), ['code' => $validOtp]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Verified.']);
        $this->assertTrue(session('2fa_verified'));
    }

    public function test_verify_with_invalid_code(): void
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        $response = $this->actingAs($user)->post(route('2fa.verify'), ['code' => '000000']);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Invalid code.']);
    }

    public function test_verify_with_recovery_code(): void
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $recoveryCodes = ['AAAA-BBBB', 'CCCC-DDDD'];
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ]);

        $response = $this->actingAs($user)->post(route('2fa.verify'), ['code' => 'AAAA-BBBB']);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Recovery code accepted. Please set up a new device.']);
        $user->refresh();
        $codes = json_decode($user->two_factor_recovery_codes, true);
        $this->assertFalse(in_array('AAAA-BBBB', $codes));
    }

    public function test_verify_when_not_configured(): void
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);

        $response = $this->actingAs($user)->post(route('2fa.verify'), ['code' => '123456']);

        $response->assertStatus(422);
        $response->assertJson(['message' => '2FA not configured.']);
    }

    // ── Challenge Page ──────────────────────────────────────────────

    public function test_challenge_redirects_when_verified(): void
    {
        $user = User::factory()->create(['two_factor_enabled' => true]);

        session(['2fa_verified' => true]);
        $response = $this->actingAs($user)->get(route('2fa.challenge'));

        $response->assertRedirect('/dashboard');
    }

    public function test_challenge_redirects_when_2fa_not_enabled(): void
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);

        $response = $this->actingAs($user)->get(route('2fa.challenge'));

        $response->assertRedirect('/dashboard');
    }

    public function test_challenge_shows_form_when_2fa_enabled_and_not_verified(): void
    {
        $user = User::factory()->create(['two_factor_enabled' => true]);

        $response = $this->actingAs($user)->get(route('2fa.challenge'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Auth/TwoFactorChallenge'));
    }
}
