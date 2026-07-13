<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\CinemaSession;
use App\Models\Reservation;
use App\Models\Ticket;
use App\Services\SeatAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));

        $reservations = Reservation::query()
            ->with(['cinemaSession.movie', 'cinemaSession.room', 'tickets'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['confirmed', 'cancelled'], true), function ($query) use ($status): void {
                $query->where('status', $status);
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/reservations/Index', [
            'reservations' => $reservations,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(SeatAvailabilityService $seatAvailabilityService): Response
    {
        return Inertia::render('admin/reservations/Create', [
            'reservation' => [
                'cinema_session_id' => '',
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'quantity' => 1,
                'status' => 'confirmed',
            ],
            'sessions' => $this->sessionOptions($seatAvailabilityService),
        ]);
    }

    public function store(
        StoreReservationRequest $request,
        SeatAvailabilityService $seatAvailabilityService,
    ): RedirectResponse {
        $reservation = DB::transaction(function () use ($request, $seatAvailabilityService): Reservation {
            $validated = $request->validated();

            $cinemaSession = CinemaSession::query()
                ->whereKey($validated['cinema_session_id'])
                ->lockForUpdate()
                ->with('movie', 'room')
                ->firstOrFail();

            $availableSeats = $seatAvailabilityService->availableSeats($cinemaSession);

            if (
                $validated['status'] === 'confirmed'
                && (int) $validated['quantity'] > $availableSeats
            ) {
                throw ValidationException::withMessages([
                    'quantity' => sprintf(
                        'Il ne reste que %d place(s) disponible(s) pour cette séance.',
                        $availableSeats,
                    ),
                ]);
            }

            $reservation = Reservation::create($validated);

            if ($reservation->status === 'confirmed') {
                $this->syncConfirmedTickets($reservation);
            }

            return $reservation;
        });

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', sprintf('La réservation #%d a été créée.', $reservation->id));
    }

    public function show(Reservation $reservation): Response
    {
        $reservation->load(['cinemaSession.movie', 'cinemaSession.room', 'tickets']);

        return Inertia::render('admin/reservations/Show', [
            'reservation' => $reservation,
        ]);
    }

    public function edit(Reservation $reservation, SeatAvailabilityService $seatAvailabilityService): Response
    {
        $reservation->load(['cinemaSession.movie', 'cinemaSession.room', 'tickets']);

        return Inertia::render('admin/reservations/Edit', [
            'reservation' => [
                'id' => $reservation->id,
                'cinema_session_id' => $reservation->cinema_session_id,
                'first_name' => $reservation->first_name,
                'last_name' => $reservation->last_name,
                'email' => $reservation->email,
                'quantity' => $reservation->quantity,
                'status' => $reservation->status,
            ],
            'sessions' => $this->sessionOptions($seatAvailabilityService, $reservation),
        ]);
    }

    public function update(
        UpdateReservationRequest $request,
        Reservation $reservation,
        SeatAvailabilityService $seatAvailabilityService,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $reservation, $seatAvailabilityService): void {
            $validated = $request->validated();

            $lockedReservation = Reservation::query()
                ->whereKey($reservation->id)
                ->lockForUpdate()
                ->firstOrFail();

            $cinemaSession = CinemaSession::query()
                ->whereKey($validated['cinema_session_id'])
                ->lockForUpdate()
                ->with('movie', 'room')
                ->firstOrFail();

            $availableSeats = $seatAvailabilityService->availableSeats($cinemaSession);

            if (
                $lockedReservation->status === 'confirmed'
                && $lockedReservation->cinema_session_id === $cinemaSession->id
            ) {
                $availableSeats += $lockedReservation->quantity;
            }

            if (
                $validated['status'] === 'confirmed'
                && (int) $validated['quantity'] > $availableSeats
            ) {
                throw ValidationException::withMessages([
                    'quantity' => sprintf(
                        'Il ne reste que %d place(s) disponible(s) pour cette séance.',
                        $availableSeats,
                    ),
                ]);
            }

            $lockedReservation->update($validated);

            if ($lockedReservation->status === 'confirmed') {
                $this->syncConfirmedTickets($lockedReservation);
            }
        });

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', sprintf('La réservation #%d a été mise à jour.', $reservation->id));
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== 'cancelled') {
            $reservation->update(['status' => 'cancelled']);
        }

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', sprintf('La réservation #%d a été annulée.', $reservation->id));
    }

    /**
     * @return array<int, array{id: int, label: string, available_seats: int}>
     */
    private function sessionOptions(
        SeatAvailabilityService $seatAvailabilityService,
        ?Reservation $currentReservation = null,
    ): array {
        return CinemaSession::query()
            ->with(['movie', 'room'])
            ->orderBy('starts_at')
            ->get()
            ->map(function (CinemaSession $session) use (
                $seatAvailabilityService,
                $currentReservation,
            ): array {
                $availableSeats = $seatAvailabilityService->availableSeats($session);

                if (
                    $currentReservation !== null
                    && $currentReservation->status === 'confirmed'
                    && $currentReservation->cinema_session_id === $session->id
                ) {
                    $availableSeats += $currentReservation->quantity;
                }

                return [
                    'id' => $session->id,
                    'label' => sprintf(
                        '%s | %s | %s | %s place(s)',
                        $session->movie->title,
                        $session->starts_at->format('d/m/Y H:i'),
                        $session->room->name,
                        $availableSeats,
                    ),
                    'available_seats' => $availableSeats,
                ];
            })
            ->all();
    }

    private function syncConfirmedTickets(Reservation $reservation): void
    {
        $reservation->loadMissing('tickets');

        $tickets = $reservation->tickets->sortBy('id')->values();
        $existingCount = $tickets->count();

        if ($existingCount > $reservation->quantity) {
            $tickets->slice($reservation->quantity)->each->delete();
            $tickets = $reservation->tickets()->orderBy('id')->get()->values();
        }

        if ($existingCount < $reservation->quantity) {
            for ($index = $existingCount; $index < $reservation->quantity; $index++) {
                Ticket::create([
                    'reservation_id' => $reservation->id,
                    'ticket_number' => sprintf('TK-%03d-%02d', $reservation->id, $index + 1),
                ]);
            }
        }

        $reservation->load('tickets');

        foreach ($reservation->tickets->values() as $index => $ticket) {
            $ticket->update([
                'ticket_number' => sprintf('TK-%03d-%02d', $reservation->id, $index + 1),
            ]);
        }
    }
}
