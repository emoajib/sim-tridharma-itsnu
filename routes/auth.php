<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:auth');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:auth');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email')
        ->middleware('throttle:auth');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store')
        ->middleware('throttle:auth');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:auth'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:auth')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware('throttle:auth');

    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update')
        ->middleware('throttle:auth');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout')
        ->middleware('throttle:auth');

    // ── Two-Factor Authentication Routes ──────────────────────────
    Route::get('2fa/setup', [TwoFactorController::class, 'showSetupForm'])
        ->name('2fa.setup');
    Route::post('2fa/confirm', [TwoFactorController::class, 'confirmSetup'])
        ->name('2fa.confirm')
        ->middleware('throttle:auth');
    Route::post('2fa/disable', [TwoFactorController::class, 'disable'])
        ->name('2fa.disable')
        ->middleware('throttle:auth');
    Route::get('2fa/challenge', [TwoFactorController::class, 'showChallenge'])
        ->name('2fa.challenge');
    Route::post('2fa/verify', [TwoFactorController::class, 'verify'])
        ->name('2fa.verify')
        ->middleware('throttle:auth');
});
