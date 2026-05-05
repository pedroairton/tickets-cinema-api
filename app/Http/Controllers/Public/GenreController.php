<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index(){
        $genres = Genre::withCount(['movies' => fn ($q) => $q->where('status', 'showing')])->get();

        return response()->json([
            'data' => $genres
        ]);
    }

    public function movies(Genre $genre){
        $movies = $genre->movies()
        ->showing()
        ->with('genres')
        ->orderBy('title')
        ->get();

        return response()->json([
            'genre' => $genre,
            'movies' => $movies
        ]);
    }
}
