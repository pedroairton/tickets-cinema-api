<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Screening;
use App\Models\Seat;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function available(Screening $screening)
    {
        $screening->load('room');

        $availableSeats = Seat::where('room_id', $screening->room_id)
            ->availableForScreening($screening->id)
            ->ordered()
            ->get(['id', 'row_label', 'column_label', 'label']);

        return response()->json([
            'screening_id' => $screening->id,
            'room' => $screening->room->name,
            'price' => $screening->price,
            'seats' => $availableSeats
        ]);
    }
}
