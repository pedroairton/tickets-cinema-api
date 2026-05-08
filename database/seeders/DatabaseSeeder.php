<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🎬 Iniciando seed do sistema de cinema...');
        $this->command->newLine();

        $this->call([
            // GenreSeeder::class,
            // UserSeeder::class,
            MovieSeeder::class,
            RoomSeeder::class,
            ScreeningSeeder::class,
            OrderAndTicketSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Seed concluído com sucesso!');
        $this->command->newLine();

        // Resumo
        $this->command->table(
            ['Tabela', 'Registros'],
            [
                ['users', \App\Models\User::count()],
                ['genres', \App\Models\Genre::count()],
                ['movies', \App\Models\Movie::count()],
                ['rooms', \App\Models\Room::count()],
                ['seats', \App\Models\Seat::count()],
                ['screenings', \App\Models\Screening::count()],
                ['orders', \App\Models\Order::count()],
                ['tickets', \App\Models\Ticket::count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('🔑 Credenciais de acesso:');
        $this->command->line('   Admin:   admin@cinema.com / password');
        $this->command->line('   Cliente: joao@email.com   / password');
    }
}
