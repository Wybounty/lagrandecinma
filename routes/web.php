<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;

// Home page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Movie details page
Route::get('/movies/{movie:slug}', [MovieController::class, 'show'])->name('movies.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
