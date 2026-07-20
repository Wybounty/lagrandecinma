<x-mail::message>
<h1>Votre réservation a été annulée</h1>

Bonjour {{ $reservation->first_name }},

Votre réservation #{{ $reservation->id }} pour <strong>{{ $reservation->cinemaSession->movie->title }}</strong> a été annulée par notre équipe.

Le remboursement correspondant à <strong>{{ $refundAmount }} €</strong> va être traité.

Si vous avez la moindre question, vous pouvez répondre à cet email.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
