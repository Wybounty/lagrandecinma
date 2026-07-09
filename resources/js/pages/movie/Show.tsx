import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { create as createReservation } from '@/routes/reservation';
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

    return (
        <>
            <Head title={movie.title} />

            <div className="min-h-screen bg-white text-black">
                <div className="mx-auto max-w-7xl px-6 py-10">
                    <Link
                        href="/"
                        className="mb-8 inline-flex items-center text-sm font-medium text-neutral-600 transition hover:text-black"
                    >
                        ← Retour aux films
                    </Link>

                    <div className="grid gap-10 lg:grid-cols-2">
                        {/* Poster */}
                        <div>
                            <img
                                src={
                                    movie.poster.startsWith('http') ||
                                    movie.poster.startsWith('/')
                                        ? movie.poster
                                        : `/${movie.poster}`
                                }
                                alt={movie.title}
                                className="w-full rounded-3xl shadow-2xl"
                            />
                        </div>

                        {/* Informations */}
                        <div className="flex flex-col justify-center">
                            <span className="mb-3 w-fit rounded-full bg-red-600 px-4 py-1 text-sm font-semibold text-white">
                                {movie.genre}
                            </span>

                            <h1 className="text-5xl font-bold text-neutral-900">
                                {movie.title}
                            </h1>

                            <div className="mt-6 flex flex-wrap gap-4 text-neutral-500">
                                <div className="rounded-lg bg-neutral-100 px-4 py-2">
                                    ⏱ {movie.duration} min
                                </div>

                                <div className="rounded-lg bg-neutral-100 px-4 py-2">
                                    📅{' '}
                                    {new Date(
                                        movie.release_date,
                                    ).toLocaleDateString('fr-FR')}
                                </div>
                            </div>

                            <p className="mt-8 text-lg leading-8 text-neutral-700">
                                {movie.description}
                            </p>

                            {/* Séances */}
                            <div className="mt-10">
                                <h2 className="mb-4 text-2xl font-bold text-neutral-900">
                                    Séances disponibles
                                </h2>

                                <div className="space-y-3">
                                    {movie.cinema_sessions.length > 0 ? (
                                        movie.cinema_sessions.map((session) => (
                                            <button
                                                key={session.id}
                                                type="button"
                                                onClick={() =>
                                                    setSelectedSession(
                                                        session.id,
                                                    )
                                                }
                                                className={`w-full rounded-xl border p-4 text-left transition cursor-pointer ${
                                                    selectedSession ===
                                                    session.id
                                                        ? 'border-red-600 bg-red-50'
                                                        : 'border-neutral-200 hover:border-red-500'
                                                }`}
                                            >
                                                <div className="flex items-center justify-between">
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
                                                            •{' '}
                                                            {session.room.name}
                                                        </p>
                                                    </div>

                                                    <div className="text-right">
                                                        <p className="text-lg font-bold">
                                                            {parseFloat(
                                                                session.price,
                                                            ).toFixed(2)}{' '}
                                                            €
                                                        </p>
                                                    </div>
                                                </div>
                                            </button>
                                        ))
                                    ) : (
                                        <p className="text-neutral-500">
                                            Aucune séance disponible.
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="mt-10 flex flex-wrap gap-4">
                                <Link
                                    href={
                                        selectedSession
                                            ? createReservation(selectedSession)
                                            : '#'
                                    }
                                    className={`rounded-xl px-8 py-4 font-semibold text-white transition ${
                                        selectedSession
                                            ? 'bg-red-600 hover:bg-red-700'
                                            : 'cursor-not-allowed bg-neutral-400'
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
        </>
    );
}
