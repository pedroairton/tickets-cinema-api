<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = Genre::all()->keyBy('name');

        $movies = [
            [
                'title' => 'Um Sonho de Liberdade',
                'synopsis' => 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.',
                'duration_minutes' => 142,
                'age_rating' => '16',
                'image_url' => 'https://m.media-amazon.com/images/M/MV5BMDFkYTc0MGEtZmNhMC00ZDIzLWFmNTEtODM1ZmRlYWMwMWFmXkEyXkFqcGdeQXVyMTMxODk2OTU@._V1_.jpg',
                // 'trailer_url' => '',
                'release_date' => '1994-09-23',
                'original_title' => 'The Shawshank Redemption',
                'director' => 'Frank Darabont',
                'distributor' => 'Warner Bros. Pictures',
                'country_of_origin' => 'United States',
                'status' => 'showing',
                'genres' => ['Drama', 'Crime', 'Ação']
            ],
            [
                'title' => 'O Poderoso Chefão',
                'synopsis' => 'An organized crime dynasty\'s aging patriarch transfers control of his clandestine empire to his reluctant son.',
                'duration_minutes' => 175,
                'age_rating' => '18',
                'image_url' => 'https://m.media-amazon.com/images/M/MV5BM2MyNjZjNmYtNDEwNi00NTY5LWEzNDgtN2NhMjQwYjRjNjMzXkEyXkFqcGdeQXVyNTA4NzY1MzY@._V1_.jpg',
                // 'trailer_url' => '',
                'release_date' => '1972-03-24',
                'original_title' => 'The Godfather',
                'director' => 'Francis Ford Coppola',
                'distributor' => 'Warner Bros. Pictures',
                'country_of_origin' => 'United States',
                'status' => 'showing',
                'genres' => ['Drama', 'Crime', 'Ação']
            ],
            [
                'title' => 'O Poderoso Chefão II',
                'synopsis' => 'The early life and career of Vito Corleone in 1920s New York is portrayed, while his son, Michael, expands and tightens his grip on the family crime syndicate.',
                'duration_minutes' => 202,
                'age_rating' => '18',
                'image_url' => 'https://m.media-amazon.com/images/M/MV5BMWMwMGQ5ZTItY2JlNC00OWZiLWIyMDYtMjAyMjkwN2MyY2YwXkEyXkFqcGdeQXVyNzkwMjQ5NzM@._V1_.jpg',
                // 'trailer_url' => '',
                'release_date' => '1974-11-17',
                'original_title' => 'The Godfather: Part II',
                'director' => 'Francis Ford Coppola',
                'distributor' => 'Warner Bros. Pictures',
                'country_of_origin' => 'United States',
                'status' => 'showing',
                'genres' => ['Drama', 'Crime', 'Ação']
            ],
        ];

        foreach($movies as $movieData){
            $genreNames = $movieData['genres'];
            unset($movieData['genres']);

            $movie = Movie::create($movieData);
            $genreIds = collect($genreNames)
            ->map(fn ($name) => $genres->get($name)?->id)
            ->filter()
            ->toArray();

            $movie->genres()->attach($genreIds);
        }
    }
}
