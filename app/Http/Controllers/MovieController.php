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
            'cinemaSessions.room',
        ]);

        return Inertia::render('movie/Show', [
            'movie' => $movie,
        ]);
    }
}
