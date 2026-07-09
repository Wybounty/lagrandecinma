<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Ticket;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\ByteMatrix;
use BaconQrCode\Encoder\Encoder;
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
        $pages = [];

        foreach ($reservation->tickets->values() as $index => $ticket) {
            $pages[] = $this->renderTicketPage(
                reservation: $reservation,
                ticketNumber: $ticket->ticket_number,
                ticketIndex: $index + 1,
                qrContent: URL::temporarySignedRoute(
                    'tickets.single',
                    now()->addDays(7),
                    ['ticket' => $ticket->uuid],
                ),
            );
        }

        return $this->createPdfDocument($pages);
    }

    private function buildSingleTicketPdf(Ticket $ticket): string
    {
        return $this->createPdfDocument([
            $this->renderTicketPage(
                reservation: $ticket->reservation,
                ticketNumber: $ticket->ticket_number,
                ticketIndex: 1,
                qrContent: URL::temporarySignedRoute(
                    'tickets.single',
                    now()->addDays(7),
                    ['ticket' => $ticket->uuid],
                ),
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
     * @param  array<int, string>  $pages
     */
    private function createPdfDocument(array $pages): string
    {
        $objects = [];
        $kids = [];
        $fontObjectNumber = 3 + (count($pages) * 2);
        $maxObjectNumber = $fontObjectNumber;

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        foreach ($pages as $pageIndex => $pageContent) {
            $pageObjectNumber = 3 + ($pageIndex * 2);
            $contentObjectNumber = $pageObjectNumber + 1;

            $kids[] = $pageObjectNumber.' 0 R';
            $objects[$pageObjectNumber] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 '.$fontObjectNumber.' 0 R >> >> /Contents '.$contentObjectNumber.' 0 R >>';
            $objects[$contentObjectNumber] = '<< /Length '.strlen($pageContent)." >>\nstream\n".$pageContent."\nendstream";
            $maxObjectNumber = max($maxObjectNumber, $contentObjectNumber);
        }

        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($pages).' >>';
        $objects[$fontObjectNumber] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $objectNumber => $object) {
            $offsets[$objectNumber] = strlen($pdf);
            $pdf .= $objectNumber.' 0 obj'."\n".$object."\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= 'xref'."\n".'0 '.($maxObjectNumber + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($objectNumber = 1; $objectNumber <= $maxObjectNumber; $objectNumber++) {
            $offset = $offsets[$objectNumber] ?? 0;
            $pdf .= sprintf('%010d 00000 n ', $offset)."\n";
        }

        $pdf .= 'trailer'."\n".'<< /Size '.($maxObjectNumber + 1).' /Root 1 0 R >>'."\n";
        $pdf .= "startxref\n".$xrefPosition."\n%%EOF";

        return $pdf;
    }

    private function renderTicketPage(Reservation $reservation, string $ticketNumber, int $ticketIndex, string $qrContent): string
    {
        $session = $reservation->cinemaSession;
        $movie = $session->movie;
        $room = $session->room;
        $qrMatrix = Encoder::encode($qrContent, ErrorCorrectionLevel::M())->getMatrix();

        $content = "BT\n";
        $content .= "/F1 26 Tf\n72 770 Td\n(".$this->escapePdfText('Billet '.$ticketIndex).") Tj\n";
        $content .= "/F1 12 Tf\n0 -28 Td\n";
        $content .= '('.$this->escapePdfText('Réservation #'.$reservation->id).") Tj\n0 -18 Td\n";
        $content .= '('.$this->escapePdfText($movie->title).") Tj\n0 -18 Td\n";
        $content .= '('.$this->escapePdfText($session->starts_at->format('d/m/Y - H\hi')).") Tj\n0 -18 Td\n";
        $content .= '('.$this->escapePdfText('Salle '.$room->name).") Tj\n0 -18 Td\n";
        $content .= "0 -8 Td\n";
        $content .= '('.$this->escapePdfText($reservation->first_name.' '.$reservation->last_name).") Tj\n0 -18 Td\n";
        $content .= '('.$this->escapePdfText($reservation->email).") Tj\n0 -18 Td\n";
        $content .= '('.$this->escapePdfText('Places: '.$reservation->quantity).") Tj\n0 -18 Td\n";
        $content .= '('.$this->escapePdfText('Numéro billet: '.$ticketNumber).") Tj\n";
        $content .= "ET\n";
        $content .= $this->drawQrMatrix($qrMatrix, 360, 420, 14);

        return $content;
    }

    private function drawQrMatrix(ByteMatrix $matrix, int $x, int $y, int $moduleSize): string
    {
        $width = $matrix->getWidth();
        $height = $matrix->getHeight();
        $rows = $matrix->getArray();

        $content = '0 0 0 rg'."\n".'0 0 0 RG'."\n";

        for ($row = 0; $row < $height; $row++) {
            for ($col = 0; $col < $width; $col++) {
                if ($rows[$row][$col] !== 1) {
                    continue;
                }

                $rectX = $x + ($col * $moduleSize);
                $rectY = $y + (($height - $row - 1) * $moduleSize);
                $content .= $rectX.' '.$rectY.' '.$moduleSize.' '.$moduleSize.' re f'."\n";
            }
        }

        return $content;
    }

    private function escapePdfText(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
