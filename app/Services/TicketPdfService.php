<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Ticket;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;

class TicketPdfService
{
    public function forReservation(Reservation $reservation): string
    {
        $reservation->loadMissing('cinemaSession.movie', 'cinemaSession.room', 'tickets');

        $tickets = $reservation->tickets->values()->map(function (Ticket $ticket, int $index) use ($reservation): array {
            return $this->buildTicketData(
                reservation: $reservation,
                ticket: $ticket,
                ticketIndex: $index + 1,
            );
        })->all();

        return $this->renderPdf($tickets);
    }

    public function forTicket(Ticket $ticket): string
    {
        $ticket->loadMissing('reservation.cinemaSession.movie', 'reservation.cinemaSession.room');

        return $this->renderPdf([
            $this->buildTicketData(
                reservation: $ticket->reservation,
                ticket: $ticket,
                ticketIndex: 1,
            ),
        ]);
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

        if ($matrixSize <= 0) {
            return '';
        }

        $quietZone = 4;
        $targetSize = 140;
        $moduleSize = max(3, intdiv($targetSize - ($quietZone * 2), $matrixSize));
        $imageSize = max(1, ($matrixSize * $moduleSize) + ($quietZone * 2));

        $image = imagecreatetruecolor((int) $imageSize, (int) $imageSize);

        if ($image === false) {
            return '';
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        if ($white === false || $black === false) {
            imagedestroy($image);

            return '';
        }

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
