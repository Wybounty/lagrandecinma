import { Head, Link, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type SessionRow = {
    id: number;
    starts_at: string;
    price: string;
    is_active: boolean;
    available_seats: number;
    movie: {
        title: string;
    };
    room: {
        name: string;
    };
};

type Props = {
    sessions: SessionRow[];
};

export default function Index({ sessions }: Props) {
    function destroy(session: SessionRow) {
        if (!window.confirm('Supprimer cette séance ?')) {
            return;
        }

        router.delete(`/admin/sessions/${session.id}`, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title="Séances" />

            <div className="space-y-6 p-6">
                <Card>
                    <CardHeader className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <CardTitle>Séances</CardTitle>
                            <p className="text-sm text-muted-foreground">
                                Organise les créneaux, salles et tarifs des séances.
                            </p>
                        </div>

                        <Button asChild>
                            <Link href="/admin/sessions/create">Nouvelle séance</Link>
                        </Button>
                    </CardHeader>
                </Card>

                <Card>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[900px] text-left text-sm">
                                <thead className="border-b bg-muted/50 text-muted-foreground">
                                    <tr>
                                        <th className="px-6 py-4 font-medium">Film</th>
                                        <th className="px-6 py-4 font-medium">Date</th>
                                        <th className="px-6 py-4 font-medium">Salle</th>
                                        <th className="px-6 py-4 font-medium">Prix</th>
                                        <th className="px-6 py-4 font-medium">Places</th>
                                        <th className="px-6 py-4 font-medium">Statut</th>
                                        <th className="px-6 py-4 font-medium text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {sessions.map((session) => (
                                        <tr key={session.id} className="border-b last:border-b-0">
                                            <td className="px-6 py-4">{session.movie.title}</td>
                                            <td className="px-6 py-4">
                                                {new Date(session.starts_at).toLocaleString('fr-FR')}
                                            </td>
                                            <td className="px-6 py-4">{session.room.name}</td>
                                            <td className="px-6 py-4">
                                                {Number(session.price).toFixed(2)} €
                                            </td>
                                            <td className="px-6 py-4">{session.available_seats}</td>
                                            <td className="px-6 py-4">
                                                <Badge variant={session.is_active ? 'default' : 'outline'}>
                                                    {session.is_active ? 'Actif' : 'Inactif'}
                                                </Badge>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex justify-end gap-2">
                                                    <Button asChild variant="outline" size="sm">
                                                        <Link href={`/admin/sessions/${session.id}`}>Voir</Link>
                                                    </Button>
                                                    <Button asChild variant="outline" size="sm">
                                                        <Link href={`/admin/sessions/${session.id}/edit`}>Modifier</Link>
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        variant="destructive"
                                                        size="sm"
                                                        onClick={() => destroy(session)}
                                                    >
                                                        Supprimer
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}

                                    {sessions.length === 0 && (
                                        <tr>
                                            <td colSpan={7} className="px-6 py-10 text-center text-muted-foreground">
                                                Aucune séance disponible.
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
