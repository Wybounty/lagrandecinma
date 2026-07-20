import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';

type Stats = {
    confirmed_reservations_count: number;
    confirmed_seats_count: number;
    upcoming_sessions_count: number;
    movies_count: number;
};

type FillRate = {
    percentage: number;
    reserved_seats: number;
    capacity: number;
};

type TopSession = {
    id: number;
    movie_title: string;
    starts_at: string;
    room_name: string;
    reserved_seats: number;
    capacity: number;
    fill_rate: number;
};

type Props = {
    stats: Stats;
    fill_rate: FillRate;
    top_sessions: TopSession[];
};

export default function Dashboard({ stats, fill_rate, top_sessions }: Props) {
    return (
        <>
            <Head title="Dashboard" />

            <div className="space-y-6 p-6">
                <section className="space-y-4">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Tableau de bord administrateur
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Aperçu rapide de la fréquentation et des séances les plus demandées.
                        </p>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <MetricCard
                            title="Réservations confirmées"
                            value={stats.confirmed_reservations_count}
                            description="Uniquement les réservations validées."
                        />
                        <MetricCard
                            title="Billets réservés"
                            value={stats.confirmed_seats_count}
                            description="Places confirmées sur l'ensemble du cinéma."
                        />
                        <MetricCard
                            title="Séances à venir"
                            value={stats.upcoming_sessions_count}
                            description="Séances encore programmées."
                        />
                        <MetricCard
                            title="Films au catalogue"
                            value={stats.movies_count}
                            description="Nombre total de films enregistrés."
                        />
                    </div>
                </section>

                <section className="grid gap-6 lg:grid-cols-[1fr_1.4fr]">
                    <Card>
                        <CardHeader>
                            <CardTitle>Taux de remplissage</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-end justify-between gap-4">
                                <div>
                                    <p className="text-4xl font-semibold">
                                        {fill_rate.percentage.toFixed(1)}%
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {fill_rate.reserved_seats} places réservées sur {fill_rate.capacity} places de capacité concernée.
                                    </p>
                                </div>

                                <Badge variant={fill_rate.percentage >= 80 ? 'default' : 'outline'}>
                                    {fill_rate.percentage >= 80 ? 'Très rempli' : 'En cours'}
                                </Badge>
                            </div>

                            <div className="h-3 overflow-hidden rounded-full bg-muted">
                                <div
                                    className="h-full rounded-full bg-primary transition-all"
                                    style={{ width: `${Math.min(fill_rate.percentage, 100)}%` }}
                                />
                            </div>

                            <p className="text-sm text-muted-foreground">
                                Le calcul prend uniquement les réservations confirmées et la capacité des séances concernées.
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Séances les plus demandées</CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[900px] text-left text-sm">
                                    <thead className="border-b bg-muted/50 text-muted-foreground">
                                        <tr>
                                            <th className="px-6 py-4 font-medium">Film</th>
                                            <th className="px-6 py-4 font-medium">Date et heure</th>
                                            <th className="px-6 py-4 font-medium">Salle</th>
                                            <th className="px-6 py-4 font-medium">Places réservées</th>
                                            <th className="px-6 py-4 font-medium">Capacité totale</th>
                                            <th className="px-6 py-4 font-medium">Taux de remplissage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {top_sessions.map((session) => (
                                            <tr key={session.id} className="border-b last:border-b-0">
                                                <td className="px-6 py-4 font-medium">
                                                    {session.movie_title}
                                                </td>
                                                <td className="px-6 py-4">
                                                    {new Date(session.starts_at).toLocaleString('fr-FR')}
                                                </td>
                                                <td className="px-6 py-4">{session.room_name}</td>
                                                <td className="px-6 py-4">{session.reserved_seats}</td>
                                                <td className="px-6 py-4">{session.capacity}</td>
                                                <td className="px-6 py-4">
                                                    <Badge
                                                        variant={
                                                            session.fill_rate >= 80
                                                                ? 'default'
                                                                : 'outline'
                                                        }
                                                    >
                                                        {session.fill_rate.toFixed(1)}%
                                                    </Badge>
                                                </td>
                                            </tr>
                                        ))}

                                        {top_sessions.length === 0 && (
                                            <tr>
                                                <td colSpan={6} className="px-6 py-10 text-center text-muted-foreground">
                                                    Aucune séance réservée pour le moment.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </section>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};

function MetricCard({
    title,
    value,
    description,
}: {
    title: string;
    value: number;
    description: string;
}) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {title}
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
                <p className="text-3xl font-semibold">{value}</p>
                <p className="text-sm text-muted-foreground">{description}</p>
            </CardContent>
        </Card>
    );
}
