import { Head, Link, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type ReservationRow = {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    quantity: number;
    status: string;
};

type Session = {
    id: number;
    starts_at: string;
    price: string;
    is_active: boolean;
    available_seats: number;
    movie: {
        title: string;
        duration: number;
    };
    room: {
        name: string;
        total_seats: number;
    };
    reservations: ReservationRow[];
};

type Props = {
    session: Session;
};

export default function Show({ session }: Props) {
    function destroy() {
        if (!window.confirm('Supprimer cette séance ?')) {
            return;
        }

        router.delete(`/admin/sessions/${session.id}`, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title={`Séance du ${new Date(session.starts_at).toLocaleDateString('fr-FR')}`} />

            <div className="space-y-6 p-6">
                <Card>
                    <CardHeader className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <CardTitle>
                                {session.movie.title} - {new Date(session.starts_at).toLocaleString('fr-FR')}
                            </CardTitle>
                            <p className="text-sm text-muted-foreground">
                                {session.room.name} · {session.movie.duration} min
                            </p>
                        </div>

                        <div className="flex gap-2">
                            <Button asChild variant="outline">
                                <Link href={`/admin/sessions/${session.id}/edit`}>Modifier</Link>
                            </Button>
                            <Button variant="destructive" onClick={destroy}>
                                Supprimer
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent className="grid gap-4 md:grid-cols-4">
                        <Info label="Prix" value={`${Number(session.price).toFixed(2)} €`} />
                        <Info label="Places disponibles" value={String(session.available_seats)} />
                        <Info label="Capacité" value={String(session.room.total_seats)} />
                        <Info
                            label="Statut"
                            value={session.is_active ? 'Actif' : 'Inactif'}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Réservations</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[700px] text-left text-sm">
                                <thead className="border-b bg-muted/50 text-muted-foreground">
                                    <tr>
                                        <th className="px-4 py-3 font-medium">Client</th>
                                        <th className="px-4 py-3 font-medium">Email</th>
                                        <th className="px-4 py-3 font-medium">Quantité</th>
                                        <th className="px-4 py-3 font-medium">Statut</th>
                                        <th className="px-4 py-3 font-medium text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {session.reservations.map((reservation) => (
                                        <tr key={reservation.id} className="border-b last:border-b-0">
                                            <td className="px-4 py-3">
                                                {reservation.first_name} {reservation.last_name}
                                            </td>
                                            <td className="px-4 py-3">{reservation.email}</td>
                                            <td className="px-4 py-3">{reservation.quantity}</td>
                                            <td className="px-4 py-3">
                                                <Badge
                                                    variant={
                                                        reservation.status === 'confirmed'
                                                            ? 'default'
                                                            : 'outline'
                                                    }
                                                >
                                                    {reservation.status}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                <Button asChild variant="outline" size="sm">
                                                    <Link href={`/admin/reservations/${reservation.id}`}>Voir</Link>
                                                </Button>
                                            </td>
                                        </tr>
                                    ))}
                                    {session.reservations.length === 0 && (
                                        <tr>
                                            <td colSpan={5} className="px-4 py-8 text-center text-muted-foreground">
                                                Aucune réservation pour cette séance.
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
