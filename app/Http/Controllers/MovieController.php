<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Inertia\Inertia;
use Inertia\Response;

class MovieController extends Controller
{
    public function show(Movie $movie): Response
    {
        $movie->load([
            'cinemaSessions' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->where('starts_at', '>=', now())
                    ->with('room')
                    ->orderBy('starts_at');
            },
        ]);

        return Inertia::render('movie/Show', [
            'movie' => $movie,
        ]);
    }
}
