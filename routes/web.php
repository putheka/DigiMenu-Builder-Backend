<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\SocialiteController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
//  Route::get('login/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::controller(SocialiteController::class)->group(function() {
    Route::get('auth/redirection/{provider}', 'authProviderRedirect')->name('auth.redirection');
    Route::get('api/auth/{provider}/callback', 'socialAuthentication')->name('auth.callback');
});

require __DIR__.'/auth.php';
