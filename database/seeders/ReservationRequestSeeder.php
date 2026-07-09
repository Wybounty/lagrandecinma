<?php

namespace Database\Seeders;

use App\Models\CinemaSession;
use App\Models\ReservationRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReservationRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sessions = CinemaSession::query()->orderBy('id')->take(5)->get();
        $codes = ['A1B2C3', 'D4E5F6', 'G7H8J9', 'K1L2M3', 'N4P5Q6'];
        $customers = [
            ['first_name' => 'Alice', 'last_name' => 'Martin', 'email' => 'alice.martin@example.com', 'quantity' => 2],
            ['first_name' => 'Benoit', 'last_name' => 'Leroy', 'email' => 'benoit.leroy@example.com', 'quantity' => 4],
            ['first_name' => 'Chloe', 'last_name' => 'Rousseau', 'email' => 'chloe.rousseau@example.com', 'quantity' => 1],
            ['first_name' => 'David', 'last_name' => 'Bernard', 'email' => 'david.bernard@example.com', 'quantity' => 3],
            ['first_name' => 'Emma', 'last_name' => 'Petit', 'email' => 'emma.petit@example.com', 'quantity' => 2],
        ];

        $requests = [];

        foreach ($sessions as $index => $session) {
            $customer = $customers[$index];
            $requests[] = [
                'token' => Str::uuid(),
                'cinema_session_id' => $session->id,
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'],
                'quantity' => $customer['quantity'],
                'verification_code' => $codes[$index],
                'expires_at' => CarbonImmutable::parse($session->starts_at)->subHours(2),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        ReservationRequest::insert($requests);
    }
}
