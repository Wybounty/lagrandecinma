<?php

namespace Database\Seeders;

use App\Models\CinemaSession;
use App\Models\Movie;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class CinemaSessionSeeder extends Seeder
{
    private const BUFFER_MINUTES = 30;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movies = Movie::query()->orderBy('id')->get();
        $rooms = Room::query()->orderBy('id')->get();
        $showtimes = [
            ['days' => 0, 'time' => '14:30:00'],
            ['days' => 3, 'time' => '17:45:00'],
            ['days' => 6, 'time' => '20:45:00'],
            ['days' => 9, 'time' => '15:15:00'],
            ['days' => 13, 'time' => '18:30:00'],
        ];

        $startingPoint = CarbonImmutable::parse('2026-07-07 10:00:00');

        $sessions = [];
        $nextAvailableAt = $rooms->mapWithKeys(
            fn (Room $room): array => [$room->id => $startingPoint]
        );

        foreach ($movies as $movieIndex => $movie) {
            foreach ($showtimes as $showtimeIndex => $showtime) {
                $targetStart = $startingPoint
                    ->addDays($showtime['days'])
                    ->setTimeFromTimeString($showtime['time']);

                $room = $rooms
                    ->sortBy(fn (Room $candidate): CarbonImmutable => $nextAvailableAt[$candidate->id])
                    ->first(function (Room $candidate) use ($nextAvailableAt, $targetStart): bool {
                        return $nextAvailableAt[$candidate->id]->lessThanOrEqualTo($targetStart);
                    });

                $room ??= $rooms
                    ->sortBy(fn (Room $candidate): CarbonImmutable => $nextAvailableAt[$candidate->id])
                    ->first();

                $startsAt = $nextAvailableAt[$room->id]->greaterThan($targetStart)
                    ? $nextAvailableAt[$room->id]
                    : $targetStart;

                $sessions[] = [
                    'movie_id' => $movie->id,
                    'room_id' => $room->id,
                    'starts_at' => $startsAt,
                    'price' => 12.00,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $nextAvailableAt[$room->id] = $startsAt->addMinutes(
                    $movie->duration + self::BUFFER_MINUTES
                );
            }
        }

        CinemaSession::insert($sessions);
    }
}
