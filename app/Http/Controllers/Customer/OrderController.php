<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreOrderRequest;
use App\Models\Order;
use App\Models\Screening;
use App\Models\Seat;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::forUser(auth()->id())
            ->with(['tickets.screening.movie:id,title,slug,image_url', 'tickets.seat:id,label'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        $screening = Screening::active()->findOrFail($request->screening_id);

        if ($screening->has_started) {
            return response()->json([
                'message' => 'Esta sessão já começou.'
            ], 422);
        }

        $seatIds = $request->seat_ids;

        $validSeats = Seat::where('room_id', $screening->room_id)
            ->whereIn('id', $seatIds)
            ->active()
            ->count();

        if ($validSeats !== count($seatIds)) {
            return response()->json([
                'message' => 'Um ou mais assentos inválidos para esta sessão.'
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($screening, $seatIds, $request) {
                $occupiedSeats = Ticket::where('screening_id', $screening->id)
                    ->whereIn('seat_id', $seatIds)
                    ->whereIn('status', ['active', 'used'])
                    ->lockForUpdate()
                    ->pluck('seat_id')
                    ->toArray();

                if (!empty($occupiedSeats)) {
                    $occupiedLabels = Seat::whereIn('id', $occupiedSeats)->pluck('label')->toArray();
                    throw new \Exception(
                        "Os assentos {$occupiedLabels} já foram comprados."
                    );
                }

                $totalAmount = bcmul($screening->price, count($seatIds), 2);

                // incluir api de pagamento
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'total_amount' => $totalAmount,
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);

                $tickets = [];
                foreach ($seatIds as $seatId) {
                    $tickets[] = [
                        'code' => Ticket::generateUniqueCode(),
                        'order_id' => $order->id,
                        'user_id' => auth()->id(),
                        'screening_id' => $screening->id,
                        'seat_id' => $seatId,
                        'unit_price' => $screening->price,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Ticket::insert($tickets);

                return $order;
            });

            $order->load([
                'tickets.seat:id,label,row_label,column_number',
                'tickets.screening:id,start_time,end_time',
                'tickets.screening.movie:id,title,slug',
            ]);

            return response()->json([
                'message' => 'Pedido realizado com sucesso',
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 409);
        }
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Pedido não encontrado'
            ], 404);
        }

        $order->load([
            'tickets.seat:id,label,row_label,column_number',
            'tickets.screening:id,start_time,end_time',
            'tickets.screening.movie:id,title,slug,image_url',
            'tickets.screening.room:id,name,'
        ]);

        return response()->json([
            'data' => $order
        ]);
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Pedido não encontrado'
            ], 404);
        }

        if ($order->isCancelled()) {
            return response()->json([
                'message' => 'Pedido já cancelado'
            ], 409);
        }

        $hasStartedScreening = $order->tickets()
            ->whereHas('screening', fn($q) => $q->where('start_time', '<=', now()))
            ->exists();

        if ($hasStartedScreening) {
            return response()->json([
                'message' => 'Não é possível cancelar um pedido com sessão iniciada'
            ], 409);
        }

        $order->markAsCancelled();

        return response()->json([
            'message' => 'Pedido cancelado com sucesso',
            'data' => $order->fresh(['tickets'])
        ], 200);
    }
}
