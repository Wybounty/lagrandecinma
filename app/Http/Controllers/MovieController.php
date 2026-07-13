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
            'cinemaSessions' => fn ($query) =>
                $query->where('starts_at', '>', now())
                    ->orderBy('starts_at')
                    ->with('room'),
        ]);

        return Inertia::render('movie/Show', [
            'movie' => $movie,
        ]);
    }
}
