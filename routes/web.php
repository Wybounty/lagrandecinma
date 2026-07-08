<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;

// Home page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Movie details page
Route::get('/movies/{movie:slug}', [MovieController::class, 'show'])->name('movies.show');

Route::get(
    '/reservation/{cinemaSession}',
    [ReservationController::class, 'create']
)->name('reservation.create');

Route::post(
    '/reservation-requests',
    [ReservationController::class, 'store']
)->name('reservation.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
