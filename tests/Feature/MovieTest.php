<?php

use App\Models\Movie;
use Inertia\Testing\AssertableInertia as Assert;

test('movie page is displayed', function () {
    $movie = Movie::create([
        'title' => 'Interstellar',
        'description' => 'Une équipe d\'explorateurs traverse un trou de ver afin de sauver l\'humanité.',
        'genre' => 'Science-fiction',
        'duration' => 169,
        'release_date' => '2014-11-05',
        'poster' => 'movies/interstellar.jpg',
        'trailer_url' => 'https://www.youtube.com/watch?v=zSWdZVtXT7E',
        'is_active' => true,
    ]);

    $this->get(route('movies.show', ['movie' => $movie->slug]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('movie/Show')
            ->where('movie.id', $movie->id)
            ->where('movie.title', $movie->title)
            ->where('movie.slug', $movie->slug)
            ->where('movie.description', $movie->description)
            ->where('movie.genre', $movie->genre)
            ->where('movie.duration', $movie->duration)
            ->where('movie.poster', $movie->poster)
            ->where('movie.trailer_url', $movie->trailer_url),
        );
});

test('movie page returns a 404 for an unknown slug', function () {
    $this->get(route('movies.show', ['movie' => 'unknown-movie']))
        ->assertNotFound();
});
