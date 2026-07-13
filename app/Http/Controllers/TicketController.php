<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Ticket;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{
    public function show(Request $request, Reservation $reservation): Response
    {
        abort_unless($request->hasValidSignature(), 403);

        $reservation->load('cinemaSession.movie', 'cinemaSession.room', 'tickets');

        return response(
            $this->buildReservationPdf($reservation),
            200,
            $this->pdfHeaders('reservation-'.$reservation->id.'-tickets.pdf'),
        );
    }

    public function single(Request $request, Ticket $ticket): Response
    {
        abort_unless($request->hasValidSignature(), 403);

        $ticket->load('reservation.cinemaSession.movie', 'reservation.cinemaSession.room');

        return response(
            $this->buildSingleTicketPdf($ticket),
            200,
            $this->pdfHeaders('ticket-'.$ticket->uuid.'.pdf'),
        );
    }

    private function buildReservationPdf(Reservation $reservation): string
    {
        $tickets = $reservation->tickets->values()->map(function (Ticket $ticket, int $index) use ($reservation): array {
            return $this->buildTicketData(
                reservation: $reservation,
                ticket: $ticket,
                ticketIndex: $index + 1,
            );
        })->all();

        return $this->renderPdf($tickets);
    }

    private function buildSingleTicketPdf(Ticket $ticket): string
    {
        return $this->renderPdf([
            $this->buildTicketData(
                reservation: $ticket->reservation,
                ticket: $ticket,
                ticketIndex: 1,
            ),
        ]);
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

    /**
     * @param  array<int, array<string, mixed>>  $tickets
     */
    private function renderPdf(array $tickets): string
    {
        return Pdf::loadView('pdf.tickets', [
            'tickets' => $tickets,
        ])
            ->setPaper('a4', 'portrait')
            ->output();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTicketData(Reservation $reservation, Ticket $ticket, int $ticketIndex): array
    {
        $session = $reservation->cinemaSession;
        $movie = $session->movie;
        $room = $session->room;
        $qrContent = URL::temporarySignedRoute(
            'tickets.single',
            now()->addDays(7),
            ['ticket' => $ticket->uuid],
        );

        return [
            'cinema_name' => 'La Grande Cinema',
            'ticket_number' => $ticket->ticket_number,
            'reservation_number' => $reservation->id,
            'ticket_index' => $ticketIndex,
            'movie_title' => $movie->title,
            'session_date' => $session->starts_at->format('d/m/Y'),
            'session_time' => $session->starts_at->format('H:i'),
            'room_name' => $room->name,
            'customer_name' => trim($reservation->first_name.' '.$reservation->last_name),
            'customer_email' => $reservation->email,
            'qr_data_uri' => $this->generateQrDataUri($qrContent),
        ];
    }

    private function generateQrDataUri(string $content): string
    {
        $matrix = Encoder::encode($content, ErrorCorrectionLevel::M())->getMatrix();
        $matrixSize = $matrix->getWidth();
        $quietZone = 4;
        $targetSize = 140;
        $moduleSize = max(3, intdiv($targetSize - ($quietZone * 2), $matrixSize));
        $imageSize = ($matrixSize * $moduleSize) + ($quietZone * 2);

        $image = imagecreatetruecolor($imageSize, $imageSize);

        if ($image === false) {
            return '';
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        imagefilledrectangle($image, 0, 0, $imageSize, $imageSize, $white);

        $rows = $matrix->getArray();

        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if ((int) $rows[$y][$x] !== 1) {
                    continue;
                }

                $left = $quietZone + ($x * $moduleSize);
                $top = $quietZone + ($y * $moduleSize);

                imagefilledrectangle(
                    $image,
                    $left,
                    $top,
                    $left + $moduleSize - 1,
                    $top + $moduleSize - 1,
                    $black,
                );
            }
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean() ?: '';
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($png);
    }
}
