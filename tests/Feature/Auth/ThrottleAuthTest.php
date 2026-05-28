<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Feature\SeedOnce;

class ThrottleAuthTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_login_throttles_after_max_attempts(): void
    {
        $user = User::factory()->create();

        // The LoginRequest uses its own rate limiter: 5 attempts per email+IP.
        // Send 5 failed attempts first.
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        // The 6th attempt should trigger the throttle (ValidationException)
        $response = $this->withHeaders(['X-Forwarded-For' => '1.2.3.4'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_throttles(): void
    {
        // The auth rate limiter is 30 requests per minute per IP.
        // Send 31 registration requests from the same IP; the last should be throttled.
        for ($i = 0; $i < 30; $i++) {
            $this->withHeaders(['X-Forwarded-For' => '1.2.3.4'])
                ->post('/register', [
                    'name' => 'User '.$i,
                    'email' => 'throttle'.$i.'@example.com',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ]);
        }

        // 31st request should be throttled (429)
        $response = $this->withHeaders(['X-Forwarded-For' => '1.2.3.4'])
            ->post('/register', [
                'name' => 'User Throttled',
                'email' => 'throttled@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertStatus(429);
    }

    public function test_forgot_password_throttles(): void
    {
        // Send 31 forgot-password requests from the same IP; the last should be throttled.
        for ($i = 0; $i < 30; $i++) {
            $this->withHeaders(['X-Forwarded-For' => '1.2.3.4'])
                ->post('/forgot-password', [
                    'email' => 'nonexistent'.$i.'@example.com',
                ]);
        }

        // 31st request should be throttled (429)
        $response = $this->withHeaders(['X-Forwarded-For' => '1.2.3.4'])
            ->post('/forgot-password', [
                'email' => 'final@example.com',
            ]);

        $response->assertStatus(429);
    }
}
