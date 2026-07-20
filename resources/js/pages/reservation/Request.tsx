import { Head, useForm, usePoll } from '@inertiajs/react';
import { useEffect } from 'react';
import { SiteFooter } from '@/components/site-footer';
import { SiteHeader } from '@/components/site-header';

interface Movie {
    title: string;
}

interface CinemaSession {
    id: number;
    starts_at: string;
    price: string;
    room: {
        name: string;
    };
    available_seats: number;
}

interface Props {
    movie: Movie;
    session: CinemaSession;
}

export default function Create({ movie, session }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        cinema_session_id: session.id,
        first_name: '',
        last_name: '',
        email: '',
        quantity: session.available_seats > 0 ? 1 : 0,
    });

    usePoll(10000, { only: ['session'] }, { keepAlive: true, mode: 'rest' });

    const isSoldOut = session.available_seats <= 0;

    useEffect(() => {
        if (isSoldOut && data.quantity !== 0) {
            setData('quantity', 0);
            return;
        }

        if (!isSoldOut && data.quantity < 1) {
            setData('quantity', 1);
            return;
        }

        if (!isSoldOut && data.quantity > session.available_seats) {
            setData('quantity', session.available_seats);
        }
    }, [data.quantity, isSoldOut, session.available_seats, setData]);

    const total = Number.parseFloat(session.price) * data.quantity;

    function submit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();

        post('/reservation-requests');
    }

    return (
        <>
            <Head title="Réserver une séance" />
            <SiteHeader cinemaName="La Grande Cinema" />

            <div className="min-h-screen bg-neutral-100 py-12 text-black">
                <div className="mx-auto max-w-2xl rounded-3xl bg-white p-10 shadow-xl">
                    <h1 className="text-3xl font-bold">Réserver une séance</h1>

                    <div className="mt-8 rounded-xl bg-neutral-100 p-5">
                        <p className="text-xl font-semibold">{movie.title}</p>

                        <p className="mt-2 text-neutral-600">
                            {new Date(session.starts_at).toLocaleDateString(
                                'fr-FR',
                                {
                                    weekday: 'long',
                                    day: 'numeric',
                                    month: 'long',
                                },
                            )}
                        </p>

                        <p className="text-neutral-600">
                            {new Date(session.starts_at).toLocaleTimeString(
                                'fr-FR',
                                {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                },
                            )}{' '}
                            • {session.room.name}
                        </p>

                        <p className="mt-2 font-semibold">
                            {Number.parseFloat(session.price).toFixed(2)} € / billet
                        </p>

                        <p
                            className={`mt-2 text-sm font-semibold ${
                                isSoldOut ? 'text-red-600' : 'text-green-700'
                            }`}
                        >
                            {isSoldOut
                                ? 'Cette séance est complète'
                                : `${session.available_seats} place${session.available_seats > 1 ? 's' : ''} disponible${session.available_seats > 1 ? 's' : ''}`}
                        </p>
                    </div>

                    <form onSubmit={submit} className="mt-8 space-y-6">
                        <div>
                            <label className="mb-2 block font-medium">
                                Nombre de billets
                            </label>

                            <input
                                type="number"
                                value={data.quantity}
                                onChange={(e) =>
                                    setData('quantity', Number(e.target.value))
                                }
                                min={isSoldOut ? 0 : 1}
                                max={session.available_seats}
                                disabled={isSoldOut}
                                className="w-full rounded-xl border border-neutral-300 px-4 py-3"
                            />

                            {errors.quantity && (
                                <p className="mt-2 text-sm text-red-600">
                                    {errors.quantity}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="mb-2 block font-medium">
                                Prénom
                            </label>

                            <input
                                type="text"
                                value={data.first_name}
                                onChange={(e) =>
                                    setData('first_name', e.target.value)
                                }
                                className="w-full rounded-xl border border-neutral-300 px-4 py-3"
                            />

                            {errors.first_name && (
                                <p className="mt-2 text-sm text-red-600">
                                    {errors.first_name}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="mb-2 block font-medium">Nom</label>

                            <input
                                type="text"
                                value={data.last_name}
                                onChange={(e) =>
                                    setData('last_name', e.target.value)
                                }
                                className="w-full rounded-xl border border-neutral-300 px-4 py-3"
                            />

                            {errors.last_name && (
                                <p className="mt-2 text-sm text-red-600">
                                    {errors.last_name}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="mb-2 block font-medium">
                                Adresse e-mail
                            </label>

                            <input
                                type="email"
                                value={data.email}
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                                className="w-full rounded-xl border border-neutral-300 px-4 py-3"
                            />

                            {errors.email && (
                                <p className="mt-2 text-sm text-red-600">
                                    {errors.email}
                                </p>
                            )}
                        </div>

                        <div className="rounded-xl bg-red-50 p-5">
                            <div className="flex items-center justify-between">
                                <span className="font-medium">Total</span>

                                <span className="text-2xl font-bold text-red-600">
                                    {total.toFixed(2)} €
                                </span>
                            </div>
                        </div>

                        <button
                            type="submit"
                            disabled={processing || isSoldOut}
                            className="w-full rounded-xl bg-red-600 px-8 py-4 font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {processing
                                ? 'Envoi...'
                                : isSoldOut
                                    ? 'Séance indisponible'
                                    : 'Recevoir mon code de validation'}
                        </button>
                    </form>
                </div>
            </div>

            <SiteFooter cinemaName="La Grande Cinema" />
        </>
    );
}
