<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ScreeningIndexRequest;
use App\Models\Screening;
use App\Models\Seat;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    public function index(ScreeningIndexRequest $request)
    {
        $query = Screening::active()
            ->upcoming()
            ->with(['movie:id,title,slug,image_url,age_rating,duration_minutes', 'room:id,name']);

        if ($request->filled('date')) {
            $query->onDate($request->date);
        }

        if ($request->filled('movie_id')) {
            $query->forMovie($request->movie_id);
        }

        if ($request->filled('room_id')) {
            $query->forRoom($request->room_id);
        }

        $query->orderBy('start_time');

        $perPage = $request->input('per_page', 20);
        $screenings = $query->paginate($perPage);

        return response()->json($screenings);
    }

    public function show(Screening $screening)
    {
        $screening->load([
            'movie:id,title,slug,synopsis,image_url,age_rating,duration_minutes',
            'movie.genres:id,name,slug',
            'room:id,name,total_rows,total_columns'
        ]);

        $screening->append(['formatted_time', 'formatted_date']);

        $totalSeats = $screening->room->seats()->active()->count();
        $occupiedSeats = Seat::occupiedForScreening($screening->id)->count();
        $availableSeats = $totalSeats - $occupiedSeats;

        return response()->json([
            'data' => $screening,
            'seats' => [
                'total' => $totalSeats,
                'available' => $availableSeats,
                'occupied' => $occupiedSeats
            ]
        ]);
    }

    public function seats(Screening $screening)
    {
        $screening->load('room');

        $allSeats = $screening->room->seats()
            ->active()
            ->ordered()
            ->get();

        $occupiedSeatsIds = Seat::occupiedForScreening($screening->id)->pluck('id')->toArray();

        $seatMap = $allSeats->map(fn ($seat) => [
            'id' => $seat->id,
            'label' => $seat->label,
            'row_label' => $seat->row_label,
            'column_label' => $seat->column_label,
            'is_occupied' => in_array($seat->id, $occupiedSeatsIds),
        ])->groupBy('row_label');

        return response()->json([
            'screening' => [
                'id' => $screening->id,
                'room' => $screening->room->name,
                'price' => $screening->price,
            ],
            'seat_map' => $seatMap
        ]);
    }
}
