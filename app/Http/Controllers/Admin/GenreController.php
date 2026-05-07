<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGenreRequest;
use App\Http\Requests\Admin\UpdateGenreRequest;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('movies')->orderBy('name')->get();

        return response()->json([
            'data' => $genres
        ]);
    }

    public function store(StoreGenreRequest $request)
    {
        $genre = Genre::create($request->validated());

        return response()->json([
            'message' => 'Gênero criado com sucesso',
            'data' => $genre
        ], 201);
    }

    public function show(Genre $genre)
    {

        $genre->loadCount('movies');

        return response()->json([
            'data' => $genre
        ]);
    }

    public function update(UpdateGenreRequest $request, Genre $genre) {
        $genre->update($request->validated());

        return response()->json([
            'message' => 'Gênero atualizado com sucesso',
            'data' => $genre
        ]);
    }

    public function destroy(Genre $genre){
        if($genre->movies()->exists()){
            return response()->json([
                'message' => 'Não é possível excluir um gênero com filmes vinculados',
            ], 409);
        }

        $genre->delete();

        return response()->json([
            'message' => 'Gênero excluído com sucesso'
        ]);
    }
}
