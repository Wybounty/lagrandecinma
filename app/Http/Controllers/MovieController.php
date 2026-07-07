<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Inertia\Inertia;

class MovieController extends Controller
{
    //
    public function show(Movie $movie)
    {
        return Inertia::render('movie/Show', [
            'movie' => $movie,
        ]);
    }
}
