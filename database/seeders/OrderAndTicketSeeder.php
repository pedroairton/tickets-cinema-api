<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Screening;
use App\Models\Seat;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderAndTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = User::customers()->get();

        if ($customers->isEmpty()) {
            $this->command->warn('Nenhum cliente encontrado. Pulando OrderAndTicketSeeder.');
            return;
        }

        // Sessões passadas → gerar pedidos pagos com tickets usados
        $pastScreenings = Screening::where('end_time', '<', now())
            ->where('is_active', true)
            ->with('room')
            ->get();

        // Sessões futuras → gerar pedidos pagos com tickets ativos
        $futureScreenings = Screening::where('start_time', '>', now())
            ->where('is_active', true)
            ->with('room')
            ->get();

        $this->command->info("Gerando pedidos para {$pastScreenings->count()} sessões passadas...");
        $this->generateOrders($pastScreenings, $customers, 'past');

        $this->command->info("Gerando pedidos para {$futureScreenings->count()} sessões futuras...");
        $this->generateOrders($futureScreenings, $customers, 'future');

        // Gerar alguns pedidos cancelados
        $this->generateCancelledOrders($futureScreenings, $customers);
    }

    private function generateOrders($screenings, $customers, string $type): void
    {
        foreach ($screenings as $screening) {
            // Sortear quantos "grupos" compram para esta sessão (2 a 5)
            $groupCount = rand(2, min(5, $customers->count()));
            $selectedCustomers = $customers->random($groupCount);

            // Obter assentos disponíveis
            $availableSeats = Seat::where('room_id', $screening->room_id)
                ->active()
                ->availableForScreening($screening->id)
                ->inRandomOrder()
                ->get();

            if ($availableSeats->isEmpty()) {
                continue;
            }

            $seatCursor = 0;

            foreach ($selectedCustomers as $customer) {
                // Cada cliente compra 1 a 4 ingressos
                $ticketCount = rand(1, min(4, $availableSeats->count() - $seatCursor));

                if ($ticketCount <= 0) {
                    break;
                }

                $selectedSeats = $availableSeats->slice($seatCursor, $ticketCount);
                $seatCursor += $ticketCount;

                $totalAmount   = bcmul($screening->price, $ticketCount, 2);
                $paymentMethod = collect(['credit_card', 'debit_card', 'pix'])->random();

                // Data do pedido: para sessões passadas, 1-3 dias antes da sessão
                $orderDate = $type === 'past'
                    ? (clone $screening->start_time)->subDays(rand(1, 3))
                    : Carbon::now()->subHours(rand(1, 72));

                DB::transaction(function () use (
                    $customer, $screening, $selectedSeats,
                    $totalAmount, $paymentMethod, $orderDate, $type
                ) {
                    $order = Order::create([
                        'user_id'        => $customer->id,
                        'total_amount'   => $totalAmount,
                        'payment_method' => $paymentMethod,
                        'payment_status' => 'paid',
                        'paid_at'        => $orderDate,
                        'created_at'     => $orderDate,
                        'updated_at'     => $orderDate,
                    ]);

                    $ticketStatus = $type === 'past' ? 'used' : 'active';

                    $tickets = [];
                    foreach ($selectedSeats as $seat) {
                        $tickets[] = [
                            'code'         => Ticket::generateUniqueCode(),
                            'order_id'     => $order->id,
                            'user_id'      => $customer->id,
                            'screening_id' => $screening->id,
                            'seat_id'      => $seat->id,
                            'unit_price'   => $screening->price,
                            'status'       => $ticketStatus,
                            'created_at'   => $orderDate,
                            'updated_at'   => $orderDate,
                        ];
                    }

                    Ticket::insert($tickets);
                });
            }
        }
    }
    private function generateCancelledOrders($futureScreenings, $customers): void
    {
        // Pegar 3 sessões futuras aleatórias para gerar cancelamentos
        $selectedScreenings = $futureScreenings->random(min(3, $futureScreenings->count()));

        foreach ($selectedScreenings as $screening) {
            $customer = $customers->random();

            $availableSeats = Seat::where('room_id', $screening->room_id)
                ->active()
                ->availableForScreening($screening->id)
                ->inRandomOrder()
                ->limit(2)
                ->get();

            if ($availableSeats->isEmpty()) {
                continue;
            }

            $totalAmount = bcmul($screening->price, $availableSeats->count(), 2);
            $orderDate   = Carbon::now()->subHours(rand(12, 48));

            DB::transaction(function () use ($customer, $screening, $availableSeats, $totalAmount, $orderDate) {
                $order = Order::create([
                    'user_id'        => $customer->id,
                    'total_amount'   => $totalAmount,
                    'payment_method' => 'pix',
                    'payment_status' => 'cancelled',
                    'paid_at'        => null,
                    'created_at'     => $orderDate,
                    'updated_at'     => $orderDate,
                ]);

                $tickets = [];
                foreach ($availableSeats as $seat) {
                    $tickets[] = [
                        'code'         => Ticket::generateUniqueCode(),
                        'order_id'     => $order->id,
                        'user_id'      => $customer->id,
                        'screening_id' => $screening->id,
                        'seat_id'      => $seat->id,
                        'unit_price'   => $screening->price,
                        'status'       => 'cancelled',
                        'created_at'   => $orderDate,
                        'updated_at'   => $orderDate,
                    ];
                }

                Ticket::insert($tickets);
            });
        }
    }
}
