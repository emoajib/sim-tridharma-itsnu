<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function __construct(
        private readonly Google2FA $google2fa,
    ) {}

    public function showSetupForm()
    {
        $user = Auth::user();

        // If 2FA is already enabled, show the enabled state instead
        if ($user->two_factor_enabled) {
            return inertia('Auth/TwoFactorSetup', [
                'secret' => '',
                'qrCodeUrl' => '',
                'enabled' => true,
                'recoveryCodes' => json_decode($user->two_factor_recovery_codes ?? '[]', true),
            ]);
        }

        $secret = $this->google2fa->generateSecretKey();

        // Cache secret temporarily until confirmed
        Cache::put('2fa:setup:' . $user->id, $secret, now()->addMinutes(10));

        $otpUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
        $qrCodeDataUrl = $this->generateQRCodeDataUrl($otpUrl);

        return inertia('Auth/TwoFactorSetup', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeDataUrl,
        ]);
    }

    public function confirmSetup(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $secret = Cache::get('2fa:setup:' . $user->id);

        if (!$secret) {
            return back()->withErrors(['code' => 'Session expired. Please restart setup.']);
        }

        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
            'two_factor_confirmed_at' => now(),
        ]);

        Cache::forget('2fa:setup:' . $user->id);

        return inertia('Auth/TwoFactorSetup', [
            'secret' => '',
            'qrCodeUrl' => '',
            'enabled' => true,
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    public function disable(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        // Clear 2FA verified session if it exists
        session()->forget('2fa_verified');

        return response()->json(['message' => 'Two-factor authentication disabled.']);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user->two_factor_secret) {
            return response()->json(['message' => '2FA not configured.'], 422);
        }

        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid) {
            // Check recovery codes
            $recoveryCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true);
            $found = array_search($request->code, $recoveryCodes);
            if ($found !== false) {
                unset($recoveryCodes[$found]);
                $user->update(['two_factor_recovery_codes' => json_encode(array_values($recoveryCodes))]);
                session(['2fa_verified' => true]);
                return response()->json(['message' => 'Recovery code accepted. Please set up a new device.']);
            }

            return response()->json(['message' => 'Invalid code.'], 422);
        }

        session(['2fa_verified' => true]);
        return response()->json(['message' => 'Verified.']);
    }

    public function showChallenge()
    {
        // If already verified, redirect to dashboard
        if (session('2fa_verified')) {
            return redirect()->intended('/dashboard');
        }

        // If user doesn't have 2FA enabled, redirect to dashboard
        if (!Auth::user()?->two_factor_enabled) {
            return redirect()->intended('/dashboard');
        }

        return inertia('Auth/TwoFactorChallenge');
    }

    private function generateQRCodeDataUrl(string $data): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($data);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(implode('-', [
                substr(bin2hex(random_bytes(3)), 0, 4),
                substr(bin2hex(random_bytes(3)), 0, 4),
            ]));
        }
        return $codes;
    }
}
