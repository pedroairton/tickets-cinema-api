<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\TicketIndexRequest;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(TicketIndexRequest $request) {
        $query = Ticket::forUser(auth()->id())
        ->with([
            'screening:id,movie_id,room_id,start_time,end_time,price',
            'screening.movie:id,title,slug,image_url,age_rating',
            'screening.room:id,name',
            'seat:id,label,row_label,column_number'
        ]);

        if($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if($request->input('period') === 'upcoming') {
            $query->forUpcomingScreenings();
        } elseif($request->input('period') === 'past') {
            $query->forPastScreenings();
        }

        $query->orderByDesc('created_at');

        $perPage = $request->input('per_page', 15);
        $tickets = $query->paginate($perPage);

        return response()->json($tickets);
    }

    public function show(Ticket $ticket){
        if($ticket->user_id !== auth()->id()){
            return response()->json([
                'message' => 'Ingresso não encontrado'
            ], 404);
        }

        $ticket->load([
            'screening:id,movie_id,room_id,start_time,end_time,price',
            'screening.movie:id,title,slug,image_url,synopsis,age_rating,duration_minutes',
            'screening.movie.genres:id,name',
            'screening.room:id,name',
            'seat:id,label,row_label,column_number',
            'order:id,total_amount,payment_method,payment_status,paid_at'
        ]);

        return response()->json([
            'data' => $ticket
        ]);
    }
}
