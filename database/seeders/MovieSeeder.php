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
                'poster' => 'films/interstellar.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=zSWdZVtXT7E',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Inception',
                'slug' => 'inception',
                'description' => "Dom Cobb est un voleur expérimenté dans l'art périlleux de l'extraction : sa spécialité consiste à s'approprier les secrets les plus précieux d'un individu, enfouis au plus profond de son subconscient, pendant qu'il rêve et que son esprit est particulièrement vulnérable. Très recherché pour ses talents dans l'univers trouble de l'espionnage industriel, Cobb est aussi devenu un fugitif traqué dans le monde entier. Cependant, une ultime mission pourrait lui permettre de retrouver sa vie d'avant.",                'genre' => 'Science-fiction',
                'duration' => 148,
                'release_date' => '2010-07-16',
                'poster' => 'films/inception.jpg',
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
                'poster' => 'films/the-dark-knight.jpg',
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
                'poster' => 'films/avatar.jpg',
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
                'poster' => 'films/avatar.jpg',
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
                'poster' => 'films/interstellar.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=zSWdZVtXT7E',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Inception',
                'slug' => 'inception-2',
                'description' => "Dom Cobb est un voleur expérimenté dans l'art périlleux de l'extraction : sa spécialité consiste à s'approprier les secrets les plus précieux d'un individu, enfouis au plus profond de son subconscient, pendant qu'il rêve et que son esprit est particulièrement vulnérable. Très recherché pour ses talents dans l'univers trouble de l'espionnage industriel, Cobb est aussi devenu un fugitif traqué dans le monde entier. Cependant, une ultime mission pourrait lui permettre de retrouver sa vie d'avant.",                'genre' => 'Science-fiction',
                'duration' => 148,
                'release_date' => '2010-07-16',
                'poster' => 'films/inception.jpg',
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
                'poster' => 'films/the-dark-knight.jpg',
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
                'poster' => 'films/avatar.jpg',
                'trailer_url' => 'https://www.youtube.com/watch?v=5PSNL1qE6VY',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
