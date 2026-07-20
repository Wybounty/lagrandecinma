<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCinemaSessionRequest;
use App\Http\Requests\UpdateCinemaSessionRequest;
use App\Models\CinemaSession;
use App\Models\Movie;
use App\Models\Room;
use App\Services\SeatAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CinemaSessionController extends Controller
{
    public function index(
        Request $request,
        SeatAvailabilityService $seatAvailabilityService,
    ): Response {
        $search = trim((string) $request->string('search'));

        $sessions = CinemaSession::query()
            ->with(['movie', 'room'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->whereHas('movie', function ($movieQuery) use ($search): void {
                        $movieQuery->where('title', 'like', "%{$search}%");
                    })->orWhereHas('room', function ($roomQuery) use ($search): void {
                        $roomQuery->where('name', 'like', "%{$search}%");
                    })->orWhere('starts_at', 'like', "%{$search}%");
                });
            })
            ->orderBy('starts_at')
            ->get()
            ->each(fn (CinemaSession $session) => $session->setAttribute(
                'available_seats',
                $seatAvailabilityService->availableSeats($session),
            ));

        return Inertia::render('admin/sessions/Index', [
            'sessions' => $sessions,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/sessions/Create', [
            'session' => [
                'movie_id' => '',
                'room_id' => '',
                'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'price' => '12.00',
                'is_active' => true,
            ],
            'movies' => $this->movieOptions(),
            'rooms' => $this->roomOptions(),
        ]);
    }

    public function store(StoreCinemaSessionRequest $request): RedirectResponse
    {
        $session = CinemaSession::create($request->validated());

        return redirect()
            ->route('admin.sessions.index')
            ->with('success', sprintf('La séance du %s a été créée.', $session->starts_at->format('d/m/Y H:i')));
    }

    public function show(CinemaSession $session, SeatAvailabilityService $seatAvailabilityService): Response
    {
        $session->load(['movie', 'room', 'reservations']);
        $session->setAttribute('available_seats', $seatAvailabilityService->availableSeats($session));

        return Inertia::render('admin/sessions/Show', [
            'session' => $session,
        ]);
    }

    public function edit(CinemaSession $session): Response
    {
        return Inertia::render('admin/sessions/Edit', [
            'session' => [
                'id' => $session->id,
                'movie_id' => $session->movie_id,
                'room_id' => $session->room_id,
                'starts_at' => $session->starts_at->format('Y-m-d\TH:i'),
                'price' => number_format((float) $session->price, 2, '.', ''),
                'is_active' => $session->is_active,
            ],
            'movies' => $this->movieOptions(),
            'rooms' => $this->roomOptions(),
        ]);
    }

    public function update(UpdateCinemaSessionRequest $request, CinemaSession $session): RedirectResponse
    {
        $session->update($request->validated());

        return redirect()
            ->route('admin.sessions.index')
            ->with('success', 'La séance a été mise à jour.');
    }

    public function destroy(CinemaSession $session): RedirectResponse
    {
        if ($session->reservations()->exists() || $session->reservationRequests()->exists()) {
            return redirect()
                ->route('admin.sessions.index')
                ->with('error', 'Cette séance ne peut pas être supprimée car elle possède déjà des réservations ou des demandes en cours.');
        }

        $session->delete();

        return redirect()
            ->route('admin.sessions.index')
            ->with('success', 'La séance a été supprimée.');
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function movieOptions(): array
    {
        return Movie::query()
            ->orderBy('title')
            ->get()
            ->map(fn (Movie $movie): array => [
                'id' => $movie->id,
                'label' => $movie->title,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function roomOptions(): array
    {
        return Room::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Room $room): array => [
                'id' => $room->id,
                'label' => sprintf('%s - %d places', $room->name, $room->total_seats),
            ])
            ->all();
    }
}
