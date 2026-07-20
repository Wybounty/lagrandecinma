<?php

use App\Http\Controllers\Admin\CinemaSessionController as AdminCinemaSessionController;
use App\Http\Controllers\Admin\MovieController as AdminMovieController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// Home page
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/movies/{movie:slug}', [MovieController::class, 'show'])->name('movies.show');

// Réservation
Route::get('/reservation/session/{cinemaSession}', [ReservationController::class, 'create'])->name('reservation.create');
Route::post('/reservation-requests', [ReservationController::class, 'store'])->name('reservation.store');

// Reseration code verification notice page
Route::get('reservation/verify/{token}', [ReservationController::class, 'verifyNotice'])->name('reservation.verify.notice');
Route::post('reservation/verify/{token}', [ReservationController::class, 'verify'])->name('reservation.verify');
Route::inertia('reservation/confirmed', 'reservation/Confirmed')->name('reservation.confirmed');

Route::get('/tickets/{reservation}', [TicketController::class, 'show'])
    ->middleware('signed')
    ->name('tickets.show');

Route::get('/ticket/{ticket:uuid}', [TicketController::class, 'single'])
    ->middleware('signed')
    ->name('tickets.single');

// Stripe payment routes
Route::get('/stripe/success', [StripeController::class, 'success'])
    ->name('stripe.success');

Route::get('/stripe/cancel', [StripeController::class, 'cancel'])
    ->name('stripe.cancel');

Route::post('/stripe/webhook', [StripeController::class, 'handle'])
    ->name('stripe.webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('movies', AdminMovieController::class);
            Route::resource('sessions', AdminCinemaSessionController::class);
            Route::resource('reservations', AdminReservationController::class);
        });
});

require __DIR__.'/settings.php';
