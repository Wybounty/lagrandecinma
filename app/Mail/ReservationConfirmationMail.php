<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Services\TicketPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public string $ticketDownloadUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre réservation est confirmée',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservations.confirmation',
            with: [
                'reservation' => $this->reservation,
                'ticketDownloadUrl' => $this->ticketDownloadUrl,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => app(TicketPdfService::class)->forReservation($this->reservation),
                sprintf('reservation-%d-tickets.pdf', $this->reservation->id),
            )->withMime('application/pdf'),
        ];
    }
}
