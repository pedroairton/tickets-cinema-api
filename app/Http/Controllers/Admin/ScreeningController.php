<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScreeningIndexRequest;
use App\Http\Requests\Admin\StoreScreeningRequest;
use App\Http\Requests\Admin\UpdateScreeningRequest;
use App\Models\Screening;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    /**
     * GET /api/admin/screenings
     *
     * Lista sessões agrupadas por data e separadas por sala,
     * ordenadas por horário dentro de cada sala.
     */
    public function index(ScreeningIndexRequest $request)
    {
        $period = $request->input('period', 'upcoming');
        $days = $request->input('days', 7);

        $now = Carbon::now();

        // Definir intervalo de busca
        if ($period === 'past') {
            $startDate = (clone $now)->subDays($days)->startOfDay();
            $endDate = $now;
            $sortDirection = 'desc';
        } else {
            $startDate = $now;
            $endDate = (clone $now)->addDays($days)->endOfDay();
            $sortDirection = 'asc';
        }

        $screenings = Screening::with([
            'movie:id,title,slug,image_url,duration_minutes,age_rating',
            'room:id,name',
        ])
            ->withCount([
                'tickets as tickets_sold_count' => fn($q) => $q->valid(),
            ])
            ->whereBetween('start_time', [$startDate, $endDate])
            ->orderBy('start_time', $sortDirection)
            ->get();

        // Agrupar por data → sala
        $grouped = $screenings
            ->groupBy(fn($screening) => $screening->start_time->format('Y-m-d'))
            ->when($period === 'past', fn($collection) => $collection->sortKeysDesc())
            ->when($period === 'upcoming', fn($collection) => $collection->sortKeys())
            ->map(function ($dayScreenings, $date) {

                $rooms = $dayScreenings
                    ->groupBy('room_id')
                    ->map(function ($roomScreenings) {
                        $room = $roomScreenings->first()->room;

                        return [
                            'room_id' => $room->id,
                            'room_name' => $room->name,
                            'screenings' => $roomScreenings
                                ->sortBy('start_time')
                                ->values()
                                ->map(fn($s) => [
                                    'id' => $s->id,
                                    'movie' => [
                                        'id' => $s->movie->id,
                                        'title' => $s->movie->title,
                                        'slug' => $s->movie->slug,
                                        'image_url' => $s->movie->image_url,
                                        'duration_minutes' => $s->movie->duration_minutes,
                                        'age_rating' => $s->movie->age_rating,
                                    ],
                                    'start_time' => $s->start_time->toISOString(),
                                    'end_time' => $s->end_time->toISOString(),
                                    'formatted_time' => $s->start_time->format('H:i'),
                                    'formatted_end_time' => $s->end_time->format('H:i'),
                                    'price' => $s->price,
                                    'is_active' => $s->is_active,
                                    'has_started' => $s->start_time->isPast(),
                                    'has_ended' => $s->end_time->isPast(),
                                    'tickets_sold_count' => $s->tickets_sold_count,
                                ]),
                        ];
                    })
                    ->sortBy('room_name')
                    ->values();

                $parsedDate = Carbon::parse($date);

                return [
                    'date' => $date,
                    'formatted_date' => $parsedDate->format('d/m/Y'),
                    'day_of_week' => $parsedDate->locale('pt_BR')->isoFormat('dddd'),
                    'is_today' => $parsedDate->isToday(),
                    'is_past' => $parsedDate->isPast() && !$parsedDate->isToday(),
                    'total_sessions' => $dayScreenings->count(),
                    'rooms' => $rooms,
                ];
            })
            ->values();

        return response()->json([
            'period' => $period,
            'days' => (int) $days,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total' => $screenings->count(),
            'data' => $grouped,
        ]);
    }

    public function store(StoreScreeningRequest $request)
    {
        $data = $request->validated();

        $conflict = Screening::conflictsWithTimeSlot(
            $data['room_id'],
            $data['start_time'],
            $data['end_time']
        )->exists();

        if ($conflict) {
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

    public function show(Screening $screening)
    {
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

    public function update(UpdateScreeningRequest $request, Screening $screening)
    {
        $data = $request->validated();

        $hasTickets = $screening->tickets()->valid()->exists();

        $changingCritical = isset($data['room_id']) || isset($data['start_time']) || isset($data['end_time']);

        if ($hasTickets && $changingCritical) {
            return response()->json([
                'message' => 'Não é possível alterar sala ou horário de uma sessão com ingressos vendidos.'
            ], 409);
        }

        $roomId = $data['room_id'] ?? $screening->room_id;
        $startTime = $data['start_time'] ?? $screening->start_time;
        $endTime = $data['end_time'] ?? $screening->end_time;

        $conflict = Screening::conflictsWithTimeSlot($roomId, $startTime, $endTime, $screening->id)->exists();

        if ($conflict) {
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

    public function destroy(Screening $screening)
    {

        $hasTickets = $screening->tickets()->valid()->exists();

        if ($hasTickets) {
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
