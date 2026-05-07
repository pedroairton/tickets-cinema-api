<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Models\Room;
use App\Models\Seat;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(){
        $rooms = Room::withCount('seats')
        ->withCount(['screenings as active_screenings_count' => fn($q) => $q->active()->upcoming()])
        ->orderBy('name')
        ->get();

        return response()->json([
            'data' => $rooms
        ]);
    }

    public function store(StoreRoomRequest $request){
        $room = DB::transaction(function () use ($request) {
            $room = Room::create($request->validated());

            $seats = [];
            for ($row = 0; $row < $room->total_rows; $row++) {
                $rowLabel = chr(65 + $row);

                for($col = 1; $col <= $room->total_columns; $col++){
                    $seats[] = [
                        'room_id' => $room->id,
                        'row_label' => $rowLabel,
                        'column_number' => $col,
                        'label' => $rowLabel . $col,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }

            Seat::insert($seats);

            return $room;
        });

        $room->loadCount('seats');

        return response()->json([
            'message' => 'Sala criada com sucesso',
            'data' => $room
        ], 201);
    }

    public function show(Room $room) {
        $room->load(['seats' => fn($q) => $q->active()->ordered()]);
        $room->append('total_capacity');

        return response()->json([
            'data' => $room
        ]);
    }

    public function destroy(Room $room) {
        $hasFutureScreenings = $room->screenings()
            ->active()
            ->upcoming()
            ->exists();

        if ($hasFutureScreenings) {
            return response()->json([
                'message' => 'Não é possível excluir uma sala com sessões futuras'
            ], 409);
        }

        $room->delete();

        return response()->json([
            'message' => 'Sala excluída com sucesso'
        ]);
    }
}
