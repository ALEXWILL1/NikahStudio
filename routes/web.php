<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationStudioController;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'redirect'])
        ->name('dashboard');

    Route::get('/admin/dashboard', [DashboardController::class, 'show'])
        ->middleware(EnsureUserHasRole::class . ':admin')
        ->name('admin.dashboard');

    Route::get('/client/dashboard', [InvitationStudioController::class, 'index'])
        ->middleware(EnsureUserHasRole::class . ':client')
        ->name('client.dashboard');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/invitation-studio.php';
