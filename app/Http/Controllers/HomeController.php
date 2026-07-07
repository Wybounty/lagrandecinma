<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use inertia\Inertia;
use App\Models\Movie;


class HomeController extends Controller
{
    //
    public function index()
    {
        $movies = Movie::query()
            ->where('is_active', true)
            ->latest()
            ->limit(8)
            ->get();

        return Inertia::render('Home', [
            'movies' => $movies
        ]);
    }
}
