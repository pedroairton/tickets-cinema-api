<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Room;
use App\Models\Screening;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScreeningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    private array $sessionTimes = ['14:00', '16:30', '19:00', '21:30'];
    

    public function run(): void
    {
        $showingMovies = Movie::showing()->get();
        $activeRooms   = Room::active()->get();

        if ($showingMovies->isEmpty() || $activeRooms->isEmpty()) {
            $this->command->warn('Nenhum filme em cartaz ou sala ativa encontrada. Pulando ScreeningSeeder.');
            return;
        }

        // ── Sessões passadas (últimos 7 dias) ──────────────────
        for ($daysAgo = 7; $daysAgo >= 1; $daysAgo--) {
            $date = Carbon::today()->subDays($daysAgo);
            $this->createScreeningsForDate($date, $showingMovies, $activeRooms);
        }

        // ── Sessões de hoje e futuras (próximos 7 dias) ────────
        for ($daysAhead = 0; $daysAhead <= 7; $daysAhead++) {
            $date = Carbon::today()->addDays($daysAhead);
            $this->createScreeningsForDate($date, $showingMovies, $activeRooms);
        }
    }

    private function createScreeningsForDate(Carbon $date, $movies, $rooms): void
    {
        $movieIndex = 0;
        $totalMovies = $movies->count();

        foreach ($rooms as $room) {
            // Cada sala recebe 2 a 3 horários por dia
            $timesForRoom = collect($this->sessionTimes)
                ->random(rand(2, min(3, count($this->sessionTimes))));

            foreach ($timesForRoom->sort()->values() as $time) {
                $movie = $movies[$movieIndex % $totalMovies];
                $movieIndex++;

                $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $time);
                $endTime   = (clone $startTime)->addMinutes($movie->duration_minutes + 15); // 15min limpeza

                // Verificar conflito antes de criar
                $conflict = Screening::where('room_id', $room->id)
                    ->where('is_active', true)
                    ->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime)
                    ->exists();

                if ($conflict) {
                    continue;
                }

                // Preço varia por sala e horário
                $basePrice = match (true) {
                    str_contains($room->name, 'VIP') => 45.00,
                    default                          => 28.00,
                };

                // Sessões noturnas mais caras
                if ((int) $startTime->format('H') >= 19) {
                    $basePrice += 7.00;
                }

                // Final de semana mais caro
                if ($date->isWeekend()) {
                    $basePrice += 5.00;
                }

                Screening::create([
                    'movie_id'   => $movie->id,
                    'room_id'    => $room->id,
                    'start_time' => $startTime,
                    'end_time'   => $endTime,
                    'price'      => $basePrice,
                    'is_active'  => true,
                ]);
            }
        }
    }
}
