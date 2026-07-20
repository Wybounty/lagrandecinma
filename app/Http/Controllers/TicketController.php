<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Ticket;
use App\Services\TicketPdfService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{
    public function show(
        Request $request,
        Reservation $reservation,
        TicketPdfService $ticketPdfService,
    ): Response {
        abort_unless($request->hasValidSignature(), 403);

        return response(
            $ticketPdfService->forReservation($reservation),
            200,
            $this->pdfHeaders('reservation-'.$reservation->id.'-tickets.pdf'),
        );
    }

    public function single(
        Request $request,
        Ticket $ticket,
        TicketPdfService $ticketPdfService,
    ): Response {
        abort_unless($request->hasValidSignature(), 403);

        return response(
            $ticketPdfService->forTicket($ticket),
            200,
            $this->pdfHeaders('ticket-'.$ticket->uuid.'.pdf'),
        );
    }

    /**
     * @return array{Content-Type: string, Content-Disposition: string}
     */
    private function pdfHeaders(string $filename): array
    {
        return [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];
    }
}
