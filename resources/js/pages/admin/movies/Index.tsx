import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type MovieRow = {
    id: number;
    title: string;
    slug: string;
    genre: string;
    duration: number;
    release_date: string;
    is_active: boolean;
    cinema_sessions_count: number;
};

type Props = {
    movies: {
        data: MovieRow[];
        links: PaginationLink[];
    };
    filters: {
        search: string;
    };
};

export default function Index({ movies, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        router.get(
            '/admin/movies',
            { search },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    }

    function destroy(movie: MovieRow) {
        if (!window.confirm(`Supprimer le film "${movie.title}" ?`)) {
            return;
        }

        router.delete(`/admin/movies/${movie.id}`, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title="Films" />

            <div className="space-y-6 p-6">
                <Card>
                    <CardHeader className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <CardTitle>Films</CardTitle>
                            <p className="text-sm text-muted-foreground">
                                Gère le catalogue des films affichés sur le site.
                            </p>
                        </div>

                        <Button asChild>
                            <Link href="/admin/movies/create">Nouveau film</Link>
                        </Button>
                    </CardHeader>

                    <CardContent>
                        <form onSubmit={submit} className="flex flex-col gap-3 md:flex-row">
                            <Input
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Rechercher un film, un genre ou un slug"
                                className="md:max-w-md"
                            />

                            <Button type="submit" variant="outline">
                                Rechercher
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[900px] text-left text-sm">
                                <thead className="border-b bg-muted/50 text-muted-foreground">
                                    <tr>
                                        <th className="px-6 py-4 font-medium">Film</th>
                                        <th className="px-6 py-4 font-medium">Genre</th>
                                        <th className="px-6 py-4 font-medium">Durée</th>
                                        <th className="px-6 py-4 font-medium">Sortie</th>
                                        <th className="px-6 py-4 font-medium">Séances</th>
                                        <th className="px-6 py-4 font-medium">Statut</th>
                                        <th className="px-6 py-4 font-medium text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {movies.data.map((movie) => (
                                        <tr key={movie.id} className="border-b last:border-b-0">
                                            <td className="px-6 py-4">
                                                <div className="font-medium text-foreground">
                                                    {movie.title}
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    /movies/{movie.slug}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">{movie.genre}</td>
                                            <td className="px-6 py-4">{movie.duration} min</td>
                                            <td className="px-6 py-4">
                                                {new Date(movie.release_date).toLocaleDateString('fr-FR')}
                                            </td>
                                            <td className="px-6 py-4">
                                                {movie.cinema_sessions_count}
                                            </td>
                                            <td className="px-6 py-4">
                                                <Badge variant={movie.is_active ? 'default' : 'outline'}>
                                                    {movie.is_active ? 'Actif' : 'Inactif'}
                                                </Badge>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex justify-end gap-2">
                                                    <Button asChild variant="outline" size="sm">
                                                        <Link href={`/admin/movies/${movie.id}`}>Voir</Link>
                                                    </Button>
                                                    <Button asChild variant="outline" size="sm">
                                                        <Link href={`/admin/movies/${movie.id}/edit`}>Modifier</Link>
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        variant="destructive"
                                                        size="sm"
                                                        onClick={() => destroy(movie)}
                                                    >
                                                        Supprimer
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}

                                    {movies.data.length === 0 && (
                                        <tr>
                                            <td colSpan={7} className="px-6 py-10 text-center text-muted-foreground">
                                                Aucun film trouvé.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <div className="flex flex-wrap gap-2">
                    {movies.links.map((link, index) => (
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
