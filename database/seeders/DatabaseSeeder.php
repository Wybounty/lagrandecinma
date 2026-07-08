<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('test@example.com'),
        ]);

        $this->call(MovieSeeder::class);
        $this->call(RoomSeeder::class);
        $this->call(CinemaSessionSeeder::class);
        $this->call(ReservationRequestSeeder::class);
        $this->call(ReservationSeeder::class);
        $this->call(TicketSeeder::class);
    }
}
