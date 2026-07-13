import { Head, Link, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type SessionRow = {
    id: number;
    starts_at: string;
    price: string;
    is_active: boolean;
    room: {
        name: string;
    };
};

type Movie = {
    id: number;
    title: string;
    description: string;
    genre: string;
    duration: number;
    release_date: string;
    poster: string;
    trailer_url: string | null;
    is_active: boolean;
    cinema_sessions: SessionRow[];
};

type Props = {
    movie: Movie;
};

export default function Show({ movie }: Props) {
    function destroy() {
        if (!window.confirm(`Supprimer le film "${movie.title}" ?`)) {
            return;
        }

        router.delete(`/admin/movies/${movie.id}`, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title={movie.title} />

            <div className="space-y-6 p-6">
                <Card>
                    <CardHeader className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <CardTitle className="flex items-center gap-3">
                                {movie.title}
                                <Badge variant={movie.is_active ? 'default' : 'outline'}>
                                    {movie.is_active ? 'Actif' : 'Inactif'}
                                </Badge>
                            </CardTitle>
                            <p className="text-sm text-muted-foreground">
                                {movie.genre} · {movie.duration} min · Sorti le {new Date(movie.release_date).toLocaleDateString('fr-FR')}
                            </p>
                        </div>

                        <div className="flex gap-2">
                            <Button asChild variant="outline">
                                <Link href={`/admin/movies/${movie.id}/edit`}>Modifier</Link>
                            </Button>
                            <Button variant="destructive" onClick={destroy}>
                                Supprimer
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent className="space-y-4">
                        <p className="text-sm leading-6 text-muted-foreground whitespace-pre-line">
                            {movie.description}
                        </p>

                        <div className="grid gap-4 md:grid-cols-2">
                            <Info label="Affiche" value={movie.poster} />
                            <Info label="Bande-annonce" value={movie.trailer_url ?? 'Aucune'} />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Séances liées</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[700px] text-left text-sm">
                                <thead className="border-b bg-muted/50 text-muted-foreground">
                                    <tr>
                                        <th className="px-4 py-3 font-medium">Date</th>
                                        <th className="px-4 py-3 font-medium">Salle</th>
                                        <th className="px-4 py-3 font-medium">Prix</th>
                                        <th className="px-4 py-3 font-medium">Statut</th>
                                        <th className="px-4 py-3 font-medium text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {movie.cinema_sessions.map((session) => (
                                        <tr key={session.id} className="border-b last:border-b-0">
                                            <td className="px-4 py-3">
                                                {new Date(session.starts_at).toLocaleString('fr-FR')}
                                            </td>
                                            <td className="px-4 py-3">{session.room.name}</td>
                                            <td className="px-4 py-3">{Number(session.price).toFixed(2)} €</td>
                                            <td className="px-4 py-3">
                                                <Badge variant={session.is_active ? 'default' : 'outline'}>
                                                    {session.is_active ? 'Actif' : 'Inactif'}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                <Button asChild variant="outline" size="sm">
                                                    <Link href={`/admin/sessions/${session.id}`}>Voir</Link>
                                                </Button>
                                            </td>
                                        </tr>
                                    ))}
                                    {movie.cinema_sessions.length === 0 && (
                                        <tr>
                                            <td colSpan={5} className="px-4 py-8 text-center text-muted-foreground">
                                                Aucune séance liée à ce film.
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
            <p className="mt-1 break-all text-sm">{value}</p>
        </div>
    );
}
