<?php

namespace Database\Seeders;

use App\Models\Movie;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Movie::insert([
            [
                'title' => 'Interstellar',
                'slug' => 'interstellar',
                'description' => 'Une équipe d\'explorateurs traverse un trou de ver afin de sauver l\'humanité.',
                'genre' => 'Science-fiction',
                'duration' => 169,
                'release_date' => '2014-11-05',
                'poster' => 'movies/interstellar.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=zSWdZVtXT7E',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Inception',
                'slug' => 'inception',
                'description' => 'Un voleur spécialisé dans l\'extraction de secrets à travers les rêves.',
                'genre' => 'Science-fiction',
                'duration' => 148,
                'release_date' => '2010-07-16',
                'poster' => 'movies/inception.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=YoHD9XEInc0',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'The Dark Knight',
                'slug' => 'the-dark-knight',
                'description' => 'Batman affronte le Joker qui sème le chaos à Gotham.',
                'genre' => 'Action',
                'duration' => 152,
                'release_date' => '2008-07-18',
                'poster' => 'movies/the-dark-knight.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=EXeTwQWrcwY',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Avatar',
                'slug' => 'avatar',
                'description' => 'Un ancien marine découvre la planète Pandora.',
                'genre' => 'Science-fiction',
                'duration' => 162,
                'release_date' => '2009-12-18',
                'poster' => 'movies/avatar.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=5PSNL1qE6VY',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Avatar',
                'slug' => 'avatar-',
                'description' => 'Un ancien marine découvre la planète Pandora.',
                'genre' => 'Science-fiction',
                'duration' => 162,
                'release_date' => '2009-12-18',
                'poster' => 'movies/avatar.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=5PSNL1qE6VY',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'title' => 'Interstellar',
                'slug' => 'interstellar-2',
                'description' => 'Une équipe d\'explorateurs traverse un trou de ver afin de sauver l\'humanité.',
                'genre' => 'Science-fiction',
                'duration' => 169,
                'release_date' => '2014-11-05',
                'poster' => 'movies/interstellar.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=zSWdZVtXT7E',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Inception',
                'slug' => 'inception-2',
                'description' => 'Un voleur spécialisé dans l\'extraction de secrets à travers les rêves.',
                'genre' => 'Science-fiction',
                'duration' => 148,
                'release_date' => '2010-07-16',
                'poster' => 'movies/inception.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=YoHD9XEInc0',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'The Dark Knight',
                'slug' => 'the-dark-knight-2',
                'description' => 'Batman affronte le Joker qui sème le chaos à Gotham.',
                'genre' => 'Action',
                'duration' => 152,
                'release_date' => '2008-07-18',
                'poster' => 'movies/the-dark-knight.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=EXeTwQWrcwY',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Avatar',
                'slug' => 'avatar-2',
                'description' => 'Un ancien marine découvre la planète Pandora.',
                'genre' => 'Science-fiction',
                'duration' => 162,
                'release_date' => '2009-12-18',
                'poster' => 'movies/avatar.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=5PSNL1qE6VY',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Top Gun: Maverick',
                'slug' => 'top-gun-maverick-2',
                'description' => 'Pete Maverick Mitchell revient former une nouvelle génération de pilotes.',
                'genre' => 'Action',
                'duration' => 131,
                'release_date' => '2022-05-25',
                'poster' => 'movies/top-gun-maverick.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=giXco2jaZ_4',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
