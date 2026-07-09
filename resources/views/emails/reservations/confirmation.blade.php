<x-mail::message>
# Votre réservation est confirmée

Votre réservation a bien été validée. Vous pouvez télécharger vos {{ $reservation->quantity }} billets en cliquant sur le bouton ci-dessous.

<x-mail::button :url="$ticketDownloadUrl">
Télécharger mon billet
</x-mail::button>

Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :

<x-mail::panel>
{{ $ticketDownloadUrl }}
</x-mail::panel>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
