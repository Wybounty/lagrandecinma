import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

type ReservationRow = {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    quantity: number;
    status: string;
    tickets: Array<{ id: number }>;
    cinema_session: {
        starts_at: string;
        movie: {
            title: string;
        };
        room: {
            name: string;
        };
    };
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Props = {
    reservations: {
        data: ReservationRow[];
        links: PaginationLink[];
    };
    filters: {
        search: string;
        status: string;
    };
};

export default function Index({ reservations, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        router.get(
            '/admin/reservations',
            { search, status },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    }

    function cancelReservation(reservation: ReservationRow) {
        if (!window.confirm(`Annuler la réservation #${reservation.id} ?`)) {
            return;
        }

        router.delete(`/admin/reservations/${reservation.id}`, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title="Réservations" />

            <div className="space-y-6 p-6">
                <Card>
                    <CardHeader className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <CardTitle>Réservations</CardTitle>
                            <p className="text-sm text-muted-foreground">
                                Consulte, modifie et annule les réservations du cinéma.
                            </p>
                        </div>

                        <Button asChild>
                            <Link href="/admin/reservations/create">Nouvelle réservation</Link>
                        </Button>
                    </CardHeader>

                    <CardContent>
                        <form onSubmit={submit} className="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                            <Input
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Rechercher un client, un email ou un numéro"
                            />

                            <select
                                value={status}
                                onChange={(event) => setStatus(event.target.value)}
                                className="h-10 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            >
                                <option value="">Tous les statuts</option>
                                <option value="confirmed">Confirmée</option>
                                <option value="cancelled">Annulée</option>
                            </select>

                            <Button type="submit" variant="outline">
                                Filtrer
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[1100px] text-left text-sm">
                                <thead className="border-b bg-muted/50 text-muted-foreground">
                                    <tr>
                                        <th className="px-6 py-4 font-medium">Client</th>
                                        <th className="px-6 py-4 font-medium">Séance</th>
                                        <th className="px-6 py-4 font-medium">Email</th>
                                        <th className="px-6 py-4 font-medium">Quantité</th>
                                        <th className="px-6 py-4 font-medium">Tickets</th>
                                        <th className="px-6 py-4 font-medium">Statut</th>
                                        <th className="px-6 py-4 font-medium text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {reservations.data.map((reservation) => (
                                        <tr key={reservation.id} className="border-b last:border-b-0">
                                            <td className="px-6 py-4">
                                                {reservation.first_name} {reservation.last_name}
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="font-medium">
                                                    {reservation.cinema_session.movie.title}
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    {reservation.cinema_session.room.name} · {new Date(reservation.cinema_session.starts_at).toLocaleString('fr-FR')}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">{reservation.email}</td>
                                            <td className="px-6 py-4">{reservation.quantity}</td>
                                            <td className="px-6 py-4">{reservation.tickets.length}</td>
                                            <td className="px-6 py-4">
                                                <Badge
                                                    variant={
                                                        reservation.status === 'confirmed'
                                                            ? 'default'
                                                            : 'outline'
                                                    }
                                                >
                                                    {reservation.status === 'confirmed'
                                                        ? 'Confirmée'
                                                        : 'Annulée'}
                                                </Badge>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex justify-end gap-2">
                                                    <Button asChild variant="outline" size="sm">
                                                        <Link href={`/admin/reservations/${reservation.id}`}>Voir</Link>
                                                    </Button>
                                                    <Button asChild variant="outline" size="sm">
                                                        <Link href={`/admin/reservations/${reservation.id}/edit`}>Modifier</Link>
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        variant="destructive"
                                                        size="sm"
                                                        onClick={() => cancelReservation(reservation)}
                                                    >
                                                        Annuler
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}

                                    {reservations.data.length === 0 && (
                                        <tr>
                                            <td colSpan={7} className="px-6 py-10 text-center text-muted-foreground">
                                                Aucune réservation trouvée.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <div className="flex flex-wrap gap-2">
                    {reservations.links.map((link, index) => (
                        <Button
                            key={`${link.label}-${index}`}
                            asChild={link.url !== null}
                            size="sm"
                            variant={link.active ? 'default' : 'outline'}
                            disabled={link.url === null}
                        >
                            {link.url !== null ? (
                                <Link href={link.url} preserveScroll>
                                    <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                </Link>
                            ) : (
                                <span dangerouslySetInnerHTML={{ __html: link.label }} />
                            )}
                        </Button>
                    ))}
                </div>
            </div>
        </>
    );
}
