<?php

use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectSlugController;
use App\Http\Controllers\Admin\TagController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::prefix('admin')->as('admin.')->group(function () {
        Route::get('projects/slug/check', [ProjectSlugController::class, 'check'])->name('projects.slug.check');
        Route::resource('projects', ProjectController::class);
        Route::resource('tags', TagController::class)->except(['show']);
    });
});

require __DIR__.'/settings.php';
