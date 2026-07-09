@component('mail::message')
# Votre code de vérification

Bonjour {{ $reservationRequest->first_name }},

Voici votre code de vérification pour finaliser votre réservation :

# {{ $reservationRequest->verification_code }}

Ce code expire à {{ $reservationRequest->expires_at->format('d/m/Y H:i') }}.

Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer ce message.

{{ config('app.name') }}
@endcomponent
