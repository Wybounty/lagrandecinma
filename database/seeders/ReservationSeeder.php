<?php

namespace Database\Seeders;

use App\Models\CinemaSession;
use App\Models\Reservation;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sessions = CinemaSession::query()->orderBy('id')->take(4)->get();

        Reservation::insert([
            [
                'cinema_session_id' => $sessions[0]->id,
                'first_name' => 'Alice',
                'last_name' => 'Martin',
                'email' => 'alice.martin@example.com',
                'quantity' => 2,
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cinema_session_id' => $sessions[1]->id,
                'first_name' => 'Benoit',
                'last_name' => 'Leroy',
                'email' => 'benoit.leroy@example.com',
                'quantity' => 4,
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cinema_session_id' => $sessions[2]->id,
                'first_name' => 'Chloe',
                'last_name' => 'Rousseau',
                'email' => 'chloe.rousseau@example.com',
                'quantity' => 1,
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cinema_session_id' => $sessions[3]->id,
                'first_name' => 'David',
                'last_name' => 'Bernard',
                'email' => 'david.bernard@example.com',
                'quantity' => 3,
                'status' => 'cancelled',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
