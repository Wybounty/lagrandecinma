<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $movies = Movie::query()
            ->where('is_active', true)
            ->latest()
            ->limit(8)
            ->get();

        return Inertia::render('Home', [
            'movies' => $movies,
        ]);
    }
}
