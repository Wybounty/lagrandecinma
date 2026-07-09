<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tickets = [];

        foreach (Reservation::query()
            ->where('status', 'confirmed')
            ->orderBy('id')
            ->get() as $reservation
        ) {
            for ($index = 1; $index <= $reservation->quantity; $index++) {
                $tickets[] = [
                    'uuid' => (string) Str::uuid(),
                    'reservation_id' => $reservation->id,
                    'ticket_number' => sprintf('TK-%03d-%02d', $reservation->id, $index),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        Ticket::insert($tickets);
    }
}
