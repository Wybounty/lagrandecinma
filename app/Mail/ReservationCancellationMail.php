<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCancellationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre réservation a été annulée',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservations.cancellation',
            with: [
                'reservation' => $this->reservation,
                'refundAmount' => number_format(
                    ((float) $this->reservation->cinemaSession->price) * $this->reservation->quantity,
                    2,
                    ',',
                    ' ',
                ),
            ],
        );
    }
}
