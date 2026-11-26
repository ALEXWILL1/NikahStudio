<?php

use App\Http\Controllers\InvitationStudioController as Studio;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', EnsureUserHasRole::class.':client'])
    ->where(['invitation' => '[0-9]{1,18}'])->prefix('client/undangan')->name('studio.')->group(function () {
        Route::get('/koleksi', [Studio::class, 'index'])->name('projects');
        Route::get('/template', [Studio::class, 'index'])->name('catalog');
        Route::get('/baru', [Studio::class, 'index'])->name('new');
        Route::post('/', [Studio::class, 'create'])->name('create');
        Route::get('/{invitation}', [Studio::class, 'edit'])->name('edit');
        Route::put('/{invitation}', [Studio::class, 'save'])->name('save');
        Route::post('/{invitation}/foto', [Studio::class, 'upload'])->middleware('throttle:30,1')->name('upload');
        Route::post('/{invitation}/terbit', [Studio::class, 'publish'])->name('publish');
    });

Route::get('/u/{slug}', [Studio::class, 'guest'])->name('studio.guest');
Route::post('/u/{slug}/rsvp', [Studio::class, 'rsvp'])->middleware('throttle:8,1')->name('studio.rsvp');
Route::get('/foto-undangan/{invitation}/{asset}', [Studio::class, 'photo'])->where(['invitation' => '[0-9]{1,18}', 'asset' => '[0-9]{1,18}'])->name('studio.photo');
