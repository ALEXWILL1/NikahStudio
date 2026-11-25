<?php

use App\Http\Controllers\DashboardController;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'redirect'])
        ->name('dashboard');

    Route::get('/admin/dashboard', [DashboardController::class, 'show'])
        ->middleware(EnsureUserHasRole::class . ':admin')
        ->name('admin.dashboard');

    Route::get('/client/dashboard', [DashboardController::class, 'show'])
        ->middleware(EnsureUserHasRole::class . ':client')
        ->name('client.dashboard');
});