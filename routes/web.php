<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->resource('posts', PostController::class)->except(['index', 'show']);
Route::resource('posts', PostController::class)->only(['index', 'show']);

Route::resource('reimburses', \App\Http\Controllers\ReimburseController::class)
    ->middleware(['auth', 'verified']);
