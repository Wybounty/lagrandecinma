import { Head, Link, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type TicketRow = {
    id: number;
    uuid: string;
    ticket_number: string;
};

type Reservation = {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    quantity: number;
    status: string;
    tickets: TicketRow[];
    cinema_session: {
        starts_at: string;
        price: string;
        movie: {
            title: string;
        };
        room: {
            name: string;
        };
    };
};

type Props = {
    reservation: Reservation;
};

export default function Show({ reservation }: Props) {
    function destroy() {
        if (!window.confirm(`Annuler la réservation #${reservation.id} ?`)) {
            return;
        }

        router.delete(`/admin/reservations/${reservation.id}`, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title={`Réservation #${reservation.id}`} />

            <div className="space-y-6 p-6">
                <Card>
                    <CardHeader className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <CardTitle>
                                Réservation #{reservation.id}
                            </CardTitle>
                            <p className="text-sm text-muted-foreground">
                                {reservation.first_name} {reservation.last_name} · {reservation.email}
                            </p>
                        </div>

                        <div className="flex gap-2">
                            <Button asChild variant="outline">
                                <Link href={`/admin/reservations/${reservation.id}/edit`}>Modifier</Link>
                            </Button>
                            <Button variant="destructive" onClick={destroy}>
                                Annuler
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent className="grid gap-4 md:grid-cols-4">
                        <Info label="Film" value={reservation.cinema_session.movie.title} />
                        <Info label="Séance" value={new Date(reservation.cinema_session.starts_at).toLocaleString('fr-FR')} />
                        <Info label="Salle" value={reservation.cinema_session.room.name} />
                        <Info label="Montant" value={`${Number(reservation.cinema_session.price).toFixed(2)} €`} />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Détails</CardTitle>
                    </CardHeader>

                    <CardContent className="grid gap-4 md:grid-cols-3">
                        <Info label="Quantité" value={String(reservation.quantity)} />
                        <Info
                            label="Statut"
                            value={reservation.status === 'confirmed' ? 'Confirmée' : 'Annulée'}
                        />
                        <Info label="Tickets générés" value={String(reservation.tickets.length)} />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Tickets</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[700px] text-left text-sm">
                                <thead className="border-b bg-muted/50 text-muted-foreground">
                                    <tr>
                                        <th className="px-4 py-3 font-medium">Numéro</th>
                                        <th className="px-4 py-3 font-medium">UUID</th>
                                        <th className="px-4 py-3 font-medium text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {reservation.tickets.map((ticket) => (
                                        <tr key={ticket.id} className="border-b last:border-b-0">
                                            <td className="px-4 py-3">{ticket.ticket_number}</td>
                                            <td className="px-4 py-3 text-xs text-muted-foreground">
                                                {ticket.uuid}
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                <Badge variant="outline">PDF signé public</Badge>
                                            </td>
                                        </tr>
                                    ))}
                                    {reservation.tickets.length === 0 && (
                                        <tr>
                                            <td colSpan={3} className="px-4 py-8 text-center text-muted-foreground">
                                                Aucun ticket généré pour cette réservation.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

function Info({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-lg border p-4">
            <p className="text-xs uppercase tracking-wide text-muted-foreground">{label}</p>
            <p className="mt-1 text-sm">{value}</p>
        </div>
    );
}
