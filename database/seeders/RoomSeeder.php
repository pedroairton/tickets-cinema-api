<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Seat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            [
                'name'          => 'Sala 1',
                'total_rows'    => 8,
                'total_columns' => 10,
                'is_active'     => true,
            ],
            [
                'name'          => 'Sala 2',
                'total_rows'    => 10,
                'total_columns' => 12,
                'is_active'     => true,
            ],
            [
                'name'          => 'Sala 3',
                'total_rows'    => 6,
                'total_columns' => 8,
                'is_active'     => true,
            ],
            [
                'name'          => 'Sala 4 - VIP',
                'total_rows'    => 5,
                'total_columns' => 8,
                'is_active'     => true,
            ],
            [
                'name'          => 'Sala 5',
                'total_rows'    => 8,
                'total_columns' => 10,
                'is_active'     => false, // Sala em manutenção
            ],
        ];

        foreach ($rooms as $roomData) {
            $room = Room::create($roomData);

            $seats = [];
            for ($row = 0; $row < $room->total_rows; $row++) {
                $rowLabel = chr(65 + $row);

                for ($col = 1; $col <= $room->total_columns; $col++) {
                    $seats[] = [
                        'room_id'       => $room->id,
                        'row_label'     => $rowLabel,
                        'column_number' => $col,
                        'label'         => $rowLabel . $col,
                        'is_active'     => true,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
            }

            Seat::insert($seats);
        }
    }
}
