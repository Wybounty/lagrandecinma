<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Models\Movie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MovieController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));

        $movies = Movie::query()
            ->withCount('cinemaSessions')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('genre', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/movies/Index', [
            'movies' => $movies,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/movies/Create', [
            'movie' => [
                'title' => '',
                'description' => '',
                'genre' => '',
                'duration' => 120,
                'release_date' => now()->toDateString(),
                'poster' => '',
                'trailer_url' => '',
                'is_active' => true,
            ],
        ]);
    }

    public function store(StoreMovieRequest $request): RedirectResponse
    {
        $movie = Movie::create($request->validated());

        return redirect()
            ->route('admin.movies.index')
            ->with('success', sprintf('Le film "%s" a été créé.', $movie->title));
    }

    public function show(Movie $movie): Response
    {
        $movie->load([
            'cinemaSessions' => fn ($query) => $query
                ->with('room')
                ->orderBy('starts_at', 'desc'),
        ]);

        return Inertia::render('admin/movies/Show', [
            'movie' => $movie,
        ]);
    }

    public function edit(Movie $movie): Response
    {
        return Inertia::render('admin/movies/Edit', [
            'movie' => [
                'id' => $movie->id,
                'title' => $movie->title,
                'description' => $movie->description,
                'genre' => $movie->genre,
                'duration' => $movie->duration,
                'release_date' => $movie->release_date->toDateString(),
                'poster' => $movie->poster,
                'trailer_url' => $movie->trailer_url ?? '',
                'is_active' => $movie->is_active,
            ],
        ]);
    }

    public function update(UpdateMovieRequest $request, Movie $movie): RedirectResponse
    {
        $movie->update([
            ...$request->validated(),
            'slug' => Str::slug((string) $request->validated('title')),
        ]);

        return redirect()
            ->route('admin.movies.index')
            ->with('success', sprintf('Le film "%s" a été mis à jour.', $movie->title));
    }

    public function destroy(Movie $movie): RedirectResponse
    {
        if ($movie->cinemaSessions()->exists()) {
            return redirect()
                ->route('admin.movies.index')
                ->with('error', sprintf(
                    'Le film "%s" ne peut pas être supprimé car des séances lui sont liées.',
                    $movie->title,
                ));
        }

        $movie->delete();

        return redirect()
            ->route('admin.movies.index')
            ->with('success', sprintf('Le film "%s" a été supprimé.', $movie->title));
    }
}
