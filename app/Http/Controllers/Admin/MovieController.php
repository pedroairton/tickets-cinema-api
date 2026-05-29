<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DashboardRequest;
use App\Http\Requests\Admin\UpdateMovieRequest;
use App\Http\Requests\Admin\UpdateMovieStatusRequest;
use App\Http\Requests\Admin\StoreMovieRequest;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MovieController extends Controller
{
    public function index()
    {
        $movies = Movie::with('genres')
            ->withCount([
                'tickets as tickets_sold_count' => fn($q) => $q->valid(),
            ])
            ->orderByDesc('created_at')
            ->get();

        $grouped = [
            'showing' => [
                'label' => 'Em cartaz',
                'count' => 0,
                'movies' => []
            ],
            'coming_soon' => [
                'label' => 'Em breve',
                'count' => 0,
                'movies' => []
            ],
            'off_screen' => [
                'label' => 'Fora de cartaz',
                'count' => 0,
                'movies' => []
            ]
        ];

        foreach($movies as $movie){
            $status = $movie->status;

            if(isset($grouped[$status])){
                $grouped[$status]['movies'][] = $movie;
                $grouped[$status]['count']++;
            }
        }

        return response()->json([
            'total' => $movies->count(),
            'data' => $grouped
        ]);
    }

    public function store(Request $request)
    {
        dd($request->all());
        $data = $request->validated();
        $genreIds = $data['genres_ids'];
        unset($data['genres_ids']);

        if($request->hasFile('image')){
            $path = $request->file('image')->store('movies', 'public');
            $data['image_url'] = $path;
        }

        $movie = Movie::create($data);
        $movie->genres()->attach($genreIds);
        $movie->load('genres');

        return response()->json([
            'message' => 'Filme criado com sucesso',
            'data' => $movie
        ], 201);
    }

    public function show(Movie $movie)
    {
        $movie->load('genres');
        $movie->append('formatted_duration');

        return response()->json([
            'data' => $movie
        ]);
    }

    public function update(UpdateMovieRequest $request, Movie $movie)
    {
        $data = $request->validated();

        if (isset($data['genres_ids'])) {
            $movie->genres()->sync($data['genres_ids']);
            unset($data['genres_ids']);
        }

        $movie->update($data);
        $movie->load('genres');

        if($request->hasFile('image')){
            if($movie->image_url){
                Storage::disk('public')->delete($movie->image_url);
            }
            $path = $request->file('image')->store('movies', 'public');
            $movie->update(['image_url' => $path]);
        }

        return response()->json([
            'message' => 'Filme atualizado com sucesso',
            'data' => $movie
        ]);
    }
    public function destroy(Movie $movie)
    {
        $hasFutureScreenings = $movie->screenings()
            ->active()
            ->upcoming()
            ->exists();

        if ($hasFutureScreenings) {
            return response()->json([
                'message' => 'Não é possível excluir um filme com sessões futuras'
            ], 409);
        }

        $movie->delete();

        return response()->json([
            'message' => 'Filme excluído com sucesso'
        ]);
    }

    public function updateStatus(UpdateMovieStatusRequest $request, Movie $movie){
        $movie->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status do filme atualizado com sucesso',
            'data' => $movie
        ]);
    }

    public function insights(DashboardRequest $request, Movie $movie){
        $startDate = $request->periodStartDate();

        $ticketsQuery = $movie->tickets()->valid();

        if($startDate){
            $ticketsQuery->where('created_at', '>=', $startDate);
        }

        $ticketsSold = $ticketsQuery->count();
        $totalRevenue = $ticketsQuery->sum('unit_price');

        $screeningsQuery = $movie->screenings();
        if($startDate){
            $screeningsQuery->where('start_time', '>=', $startDate);
        }
        $totalScreenings = $screeningsQuery->count();

        $activeScreenings = $movie->screenings()->active()->upcoming()->count();

        return response()->json([
            'data' => [
                'movie' => [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'slug' => $movie->slug,
                ],
                'tickets_sold' => $ticketsSold,
                'total_revenue' => $totalRevenue,
                'total_screenings' => $totalScreenings,
                'active_screenings' => $activeScreenings,
                'period' => $request->input('period', 'total')
            ]
        ]);
    }
}
