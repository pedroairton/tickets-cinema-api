<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\MovieIndexRequest;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(MovieIndexRequest $request)
    {
        $query = Movie::with('genres');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('genre')) {
            $query->byGenre($request->genre);
        }

        if ($request->filled('age_rating')) {
            $query->ageRating($request->age_rating);
        }

        if($request->filled('date')) {
            $query->withScreeningsOnDate($request->date);
        }

        if($request->filled('duration_min' && $request->filled('duration_max'))) {
            $query->durationBetween($request->duration_min, $request->duration_max);
        } else if($request->filled('duration_min')) {
            $query->where('duration_minutes', '>=', $request->duration_min);
        } else if($request->filled('duration_max')) {
            $query->where('duration_minutes', '<=', $request->duration_max);
        }

        if($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('original_title', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->input('sort_by', 'title');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = $request->input('per_page', 15);
        $movies = $query->paginate($perPage);

        return response()->json($movies);
    }

    public function search(Request $request) {
        $search = $request['input'];
        $movies = Movie::where('title', 'like', "%{$search}%")
        ->orWhere('original_title', 'like', "%{$search}%")
        ->limit(5)->get();

        return response()->json($movies);
    }

    public function show(Movie $movie) {
        $movie->load('genres');

        $movie->append('formatted_duration');

        return response()->json($movie);
    }

    public function screenings(Movie $movie) {
        $screenings = $movie->screenings()
        ->active()
        ->upcoming()
        ->with('room:id,name')
        ->orderBy('start_time')
        ->get()
        ->groupBy(fn ($screening) => $screening->start_time->format('Y-m-d'))
        ->map(function ($dayScreenings, $date) {
            return [
                'date' => $date,
                'formatted' => \Carbon\Carbon::parse($date)->format('d/m/Y - 1'),
                'screenings' => $dayScreenings->map(fn ($s) => [
                    'id' => $s->id,
                    'start_time' => $s->start_time->format('H:i'),
                    'end_time' => $s->end_time->format('H:i'),
                    'price' => $s->price,
                    'room' => $s->room->name
                ])
            ];
        })
        ->values();

        return response()->json([
            'movie' => [
                'id' => $movie->id,
                'title' => $movie->title,
                'slug' => $movie->slug,
            ],
            'dates' => $screenings
        ]);
    }
}
