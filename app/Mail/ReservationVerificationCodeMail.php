<?php

namespace App\Mail;

use App\Models\ReservationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ReservationRequest $reservationRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre code de vérification',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservations.verification-code',
            with: [
                'reservationRequest' => $this->reservationRequest,
            ],
        );
    }
}
