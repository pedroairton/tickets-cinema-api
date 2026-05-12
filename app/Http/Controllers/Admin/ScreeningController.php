<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScreeningRequest;
use App\Http\Requests\Admin\UpdateScreeningRequest;
use App\Models\Screening;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    public function index(){
        $screenings = Screening::with([
            'movie:id,title,slug,image_url,duration_minutes',
            'room:id,name'
        ])
        ->withCount([
            'tickets as tickets_sold_count' => fn($q) => $q->valid(),
        ])
        ->orderByDesc('start_time')
        ->paginate(20);

        return response()->json($screenings);
    }

    public function store(StoreScreeningRequest $request){
        $data = $request->validated();
        
        $conflict = Screening::conflictsWithTimeSlot(
            $data['room_id'],
            $data['start_time'],
            $data['end_time']
        )->exists();

        if($conflict){
            return response()->json([
                'message' => 'Já existe uma sessão marcada para esse horário nesta sala.',
                'data' => $conflict
            ], 409);
        }

        $screening = Screening::create($data);
        $screening->load([
            'movie:id,title,slug',
            'room:id,name'
        ]);

        return response()->json([
            'message' => 'Sessão criada com sucesso',
            'data' => $screening
        ], 201);
    }

    public function show(Screening $screening) {
        $screening->load([
            'movie:id,title,slug,image_url,duration_minutes,age_rating',
            'movie.genres:id,name',
            'room:id,name,total_rows,total_columns',
        ]);

        $screening->loadCount([
            'tickets as tickets_sold_count' => fn($q) => $q->valid(),
            'tickets as tickets_cancelled_count' => fn($q) => $q->cancelled(),
        ]);

        $screening->append(['formatted_time', 'formatted_date', 'has_started', 'has_ended']);

        return response()->json([
            'data' => $screening
        ]);
    }

    public function update(UpdateScreeningRequest $request, Screening $screening){
        $data = $request->validated();

        $hasTickets = $screening->tickets()->valid()->exists();

        $changingCritical = isset($data['room_id']) || isset($data['start_time']) || isset($data['end_time']);

        if($hasTickets && $changingCritical){
            return response()->json([
                'message' => 'Não é possível alterar sala ou horário de uma sessão com ingressos vendidos.'
            ], 409);
        }

        $roomId = $data['room_id'] ?? $screening->room_id;
        $startTime = $data['start_time'] ?? $screening->start_time;
        $endTime = $data['end_time'] ?? $screening->end_time;

        $conflict = Screening::conflictsWithTimeSlot($roomId, $startTime, $endTime, $screening->id)->exists();

        if($conflict){
            return response()->json([
                'message' => 'Já existe uma sessão marcada para esse horário nesta sala.'
            ], 409);
        }

        $screening->update($data);
        $screening->load([
            'movie:id,title,slug',
            'room:id,name',
        ]);

        return response()->json([
            'message' => 'Sessão atualizada com sucesso',
            'data' => $screening
        ]);
    }

    public function destroy(Screening $screening) {

        $hasTickets = $screening->tickets()->valid()->exists();

        if($hasTickets){
            return response()->json([
                'message' => 'Não é possível excluir uma sessão com ingressos vendidos, Desative-a em vez disso.'
            ], 409);
        }

        $screening->delete();
        return response()->json([
            'message' => 'Sessão excluída com sucesso'
        ]);
    }
}
