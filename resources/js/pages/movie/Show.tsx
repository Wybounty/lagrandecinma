import { Head, Link, usePoll } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { SiteFooter } from '@/components/site-footer';
import { SiteHeader } from '@/components/site-header';
import { create as createReservation } from '@/routes/reservation';
import { posterUrl } from '@/lib/poster';

interface Room {
    id: number;
    name: string;
    total_seats: number;
}

interface CinemaSession {
    id: number;
    starts_at: string;
    price: string;
    room: Room;
    available_seats: number;
}

interface Movie {
    id: number;
    title: string;
    slug: string;
    description: string;
    genre: string;
    duration: number;
    release_date: string;
    poster: string;
    trailer_url: string | null;
    cinema_sessions: CinemaSession[];
}

interface Props {
    movie: Movie;
}

export default function Index({ movie }: Props) {
    const [selectedSession, setSelectedSession] = useState<number | null>(null);

    usePoll(10000, { only: ['movie'] }, { keepAlive: true, mode: 'rest' });

    const selectedSessionData = useMemo(
        () =>
            movie.cinema_sessions.find(
                (session) => session.id === selectedSession,
            ) ?? null,
        [movie.cinema_sessions, selectedSession],
    );

    useEffect(() => {
        if (
            selectedSessionData !== null &&
            selectedSessionData.available_seats <= 0
        ) {
            setSelectedSession(null);
        }
    }, [selectedSessionData]);

    return (
        <>
            <Head title={movie.title} />
            <SiteHeader cinemaName="La Grande Cinema" />

            <div className="min-h-screen bg-white text-black">
                <div className="mx-auto max-w-7xl px-6 py-10">
                    <Link
                        href="/"
                        className="mb-8 inline-flex items-center text-sm font-medium text-neutral-600 transition hover:text-black"
                    >
                        â† Retour aux films
                    </Link>

                    <div className="grid gap-10 lg:grid-cols-[minmax(280px,360px)_minmax(0,1fr)] lg:items-start">
                        <div className="mx-auto w-full max-w-sm lg:mx-0">
                            <img
                                src={posterUrl(movie.poster)}
                                alt={movie.title}
                                className="aspect-[2/3] w-full rounded-3xl object-cover shadow-2xl"
                            />
                        </div>

                        <div className="flex flex-col">
                            <span className="mb-3 w-fit rounded-full bg-red-600 px-4 py-1 text-sm font-semibold text-white">
                                {movie.genre}
                            </span>

                            <h1 className="text-4xl font-bold text-neutral-900 sm:text-5xl">
                                {movie.title}
                            </h1>

                            <div className="mt-6 flex flex-wrap gap-4 text-neutral-500">
                                <div className="rounded-lg bg-neutral-100 px-4 py-2">
                                    ⏱ {movie.duration} min
                                </div>

                                <div className="rounded-lg bg-neutral-100 px-4 py-2">
                                    📅{' '}
                                    {new Date(movie.release_date).toLocaleDateString(
                                        'fr-FR',
                                    )}
                                </div>
                            </div>

                            <p className="mt-8 text-lg leading-8 text-neutral-700">
                                {movie.description}
                            </p>

                            <div className="mt-10">
                                <h2 className="mb-4 text-2xl font-bold text-neutral-900">
                                    Séances disponibles
                                </h2>

                                <div className="space-y-3">
                                    {movie.cinema_sessions.length > 0 ? (
                                        movie.cinema_sessions.map((session) => {
                                            const isFull =
                                                session.available_seats <= 0;
                                            const isSelected =
                                                selectedSession === session.id;

                                            return (
                                                <button
                                                    key={session.id}
                                                    type="button"
                                                    onClick={() => {
                                                        if (!isFull) {
                                                            setSelectedSession(
                                                                session.id,
                                                            );
                                                        }
                                                    }}
                                                    disabled={isFull}
                                                    className={`w-full cursor-pointer rounded-xl border p-4 text-left transition disabled:cursor-not-allowed disabled:opacity-60 ${
                                                        isSelected
                                                            ? 'border-red-600 bg-red-50'
                                                            : 'border-neutral-200 hover:border-red-500'
                                                    }`}
                                                >
                                                    <div className="flex items-center justify-between gap-4">
                                                        <div>
                                                            <p className="font-semibold capitalize">
                                                                {new Date(
                                                                    session.starts_at,
                                                                ).toLocaleDateString(
                                                                    'fr-FR',
                                                                    {
                                                                        weekday:
                                                                            'long',
                                                                        day: 'numeric',
                                                                        month: 'long',
                                                                    },
                                                                )}
                                                            </p>

                                                            <p className="text-neutral-600">
                                                                {new Date(
                                                                    session.starts_at,
                                                                ).toLocaleTimeString(
                                                                    'fr-FR',
                                                                    {
                                                                        hour: '2-digit',
                                                                        minute: '2-digit',
                                                                    },
                                                                )}{' '}
                                                                • {session.room.name}
                                                            </p>

                                                            <p
                                                                className={`mt-2 text-sm font-semibold ${
                                                                    isFull
                                                                        ? 'text-red-600'
                                                                        : 'text-green-700'
                                                                }`}
                                                            >
                                                                {isFull
                                                                    ? 'Complet'
                                                                    : `${session.available_seats} place${session.available_seats > 1 ? 's' : ''} disponible${session.available_seats > 1 ? 's' : ''}`}
                                                            </p>
                                                        </div>

                                                        <div className="text-right">
                                                            <p className="text-lg font-bold">
                                                                {Number.parseFloat(
                                                                    session.price,
                                                                ).toFixed(2)}{' '}
                                                                €
                                                            </p>
                                                        </div>
                                                    </div>
                                                </button>
                                            );
                                        })
                                    ) : (
                                        <p className="text-neutral-500">
                                            Aucune séance disponible.
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div className="mt-10 flex flex-wrap gap-4">
                                <Link
                                    href={
                                        selectedSessionData
                                            ? createReservation(selectedSessionData.id)
                                            : '#'
                                    }
                                    aria-disabled={!selectedSessionData}
                                    className={`rounded-xl px-8 py-4 font-semibold text-white transition ${
                                        selectedSessionData
                                            ? 'bg-red-600 hover:bg-red-700'
                                            : 'pointer-events-none cursor-not-allowed bg-neutral-400'
                                    }`}
                                >
                                    Réserver cette séance
                                </Link>

                                {movie.trailer_url && (
                                    <a
                                        href={movie.trailer_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="rounded-xl border border-neutral-300 px-8 py-4 font-semibold transition hover:bg-neutral-100"
                                    >
                                        Voir la bande-annonce
                                    </a>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <SiteFooter cinemaName="La Grande Cinema" />
        </>
    );
}
