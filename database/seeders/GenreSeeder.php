<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            'Ação',
            'Aventura',
            'Animação',
            'Comédia',
            'Crime',
            'Documentário',
            'Drama',
            'Fantasia',
            'Ficção Científica',
            'Guerra',
            'Musical',
            'Mistério',
            'Romance',
            'Suspense',
            'Terror'
        ];

        foreach ($genres as $name){
            Genre::create(['name' => $name]);
        }
    }
}
