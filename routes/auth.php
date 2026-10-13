<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes invité (non connecté)
|--------------------------------------------------------------------------
| ⚠️ L'inscription (register) est désactivée.
| Seule la connexion est autorisée.
*/
Route::middleware('guest')->group(function () {

    // ✅ Connexion uniquement
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Mot de passe oublié
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // Reset du mot de passe
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Routes authentifiées (connecté)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Confirmation du mot de passe
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // Mise à jour du mot de passe
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Déconnexion
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});