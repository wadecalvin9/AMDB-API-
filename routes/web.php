<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\AddonClientController;
use App\Livewire\Settings\{Appearance, Password, Profile, TwoFactor};

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home + Discover
Route::get('/', [MovieController::class, 'discover'])->name('home');
Route::get('/discover', [MovieController::class, 'discover'])->name('discover');

// Movies & TV Shows
Route::prefix('/')->group(function () {
    // Movies
    Route::get('/movie/{id}', [MovieController::class, 'show'])
        ->name('movie.show')
        ->defaults('type', 'movie');

    // TV Shows (use same controller method)
    Route::get('/tv/{id}', [MovieController::class, 'show'])
        ->name('tv.show')
        ->defaults('type', 'tv');
});

/*--------------------------------------------------------------
*/

Route::get('/client/stream/{type}/{id}', [MovieController::class, 'clientStream']);



// Optional direct stream route


Route::get('/tv/{tvId}/season/{season}/episode/{episode}/streams', [MovieController::class, 'episodeStreams'])
    ->name('tv.episode.streams');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                []
            )
        )
        ->name('two-factor.show');
});

require __DIR__ . '/auth.php';
