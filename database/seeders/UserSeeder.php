<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@teste.com',
            'password' => Hash::make('senha123'),
            'role' => 'admin',
            'cpf' => '000.000.000-00',
            'phone' => '(81) 00000-0000',
            'birth_date' => '2001-06-28'
        ]);

        $customers = [
            [
                'name' => 'João Silva',
                'email' => 'joao@email.com',
                'cpf' => '123.456.789-01',
                'phone' => '(81) 91111-1111',
                'birth_date' => '1995-03-10'
            ],
            [
                'name' => 'Maria Souza',
                'email' => 'maria@email.com',
                'cpf' => '987.654.321-00',
                'phone' => '(81) 92222-2222',
                'birth_date' => '1990-07-15'
            ],
            [
                'name' => 'Pedro Santos',
                'email' => 'pedro@email.com',
                'cpf' => '555.555.555-55',
                'phone' => '(81) 93333-3333',
                'birth_date' => '1988-09-20'
            ],
            [
                'name' => 'Ana Oliveira',
                'email' => 'ana@email.com',
                'cpf' => '777.777.777-77',
                'phone' => '(81) 94444-4444',
                'birth_date' => '1992-12-05'
            ],
            [
                'name' => 'Lucas Costa',
                'email' => 'lucas@email.com',
                'cpf' => '999.999.999-99',
                'phone' => '(81) 95555-5555',
                'birth_date' => '1987-06-30'
            ],
            [
                'name' => 'Mariana Ferreira',
                'email' => 'mariana@email.com',
                'cpf' => '666.666.666-66',
                'phone' => '(81) 96666-6666',
                'birth_date' => '1991-11-25'
            ],
            [
                'name' => 'Rafael Almeida',
                'email' => 'rafail@email.com',
                'cpf' => '888.888.888-88',
                'phone' => '(81) 97777-7777',
                'birth_date' => '1989-04-18'
            ],
            [
                'name' => 'Fernanda Lima',
                'email' => 'fernanda@email.com',
                'cpf' => '444.444.444-44',
                'phone' => '(81) 98888-8888',
                'birth_date' => '1993-08-12'
            ]
        ];

        foreach ($customers as $customer) {
            User::create(array_merge($customer, [
                'password' => Hash::make('senha123'),
                'role' => 'customer'
            ]));
        }
    }
}
