import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

interface Props {
    email?: string;
    token?: string;
    expires_at?: string;
}

export default function VerifyNotice({
    email = 'votre adresse e-mail',
    token = '',
    expires_at = '',

}: Props) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(`/reservation/verify/${token}`);
    }


    const [timeLeft, setTimeLeft] = useState('');

    useEffect(() => {
        const interval = setInterval(() => {
            const now = new Date().getTime();
            const expiration = new Date(expires_at).getTime();

            const difference = expiration - now;

            if (difference <= 0) {
                setTimeLeft('Expiré');
                clearInterval(interval);
                return;
            }

            const minutes = Math.floor(difference / 1000 / 60);
            const seconds = Math.floor((difference / 1000) % 60);

            setTimeLeft(
                `${minutes}:${seconds.toString().padStart(2, '0')}`,
            );
        }, 1000);

        return () => clearInterval(interval);
    }, [expires_at]);

    return (
        <>
            <Head title="Vérification de réservation" />

            <div className="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(239,68,68,0.10),_transparent_45%),linear-gradient(135deg,_#fef2f2_0%,_#fafafa_100%)] px-4 py-12 sm:px-6 lg:px-8">
                <div className="mx-auto flex max-w-5xl flex-col gap-6 lg:flex-row">
                    <section className="flex-1 rounded-[32px] border border-neutral-200 bg-white p-8 shadow-[0_20px_80px_-30px_rgba(0,0,0,0.25)] sm:p-10 lg:p-12">
                        <div className="inline-flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-2xl shadow-sm">
                            ✉️
                        </div>

                        <p className="mt-6 text-sm font-semibold uppercase tracking-[0.35em] text-red-600">
                            Étape 2
                        </p>

                        <h1 className="mt-3 text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                            Vérifiez votre boîte mail
                        </h1>

                        <p className="mt-4 text-lg leading-8 text-neutral-600">
                            Nous avons envoyé un code de validation à{' '}
                            <span className="font-semibold text-neutral-900">
                                {email}
                            </span>
                            . Saisissez-le ci-dessous pour confirmer votre réservation.
                        </p>

                        <form
                            onSubmit={submit}
                            className="mt-8 rounded-2xl border border-neutral-200 bg-neutral-50 p-5 sm:p-6"
                        >
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p className="text-sm font-semibold text-neutral-800">
                                        Code de validation
                                    </p>
                                    <p className="text-sm text-neutral-500">
                                        Valide pendant 2 minutes
                                    </p>
                                </div>

                                <span className="rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700">
                                    Expire bientôt
                                </span>
                            </div>

                            <div className="mt-5">
                                <input
                                    type="text"
                                    value={data.code}
                                    onChange={(e) =>
                                        setData('code', e.target.value.toUpperCase())
                                    }
                                    maxLength={6}
                                    placeholder="Entrez votre code"
                                    className="w-full rounded-xl border border-neutral-300 bg-white px-4 py-3 text-center text-lg font-semibold tracking-[0.35em] text-neutral-900 shadow-sm outline-none focus:border-red-500"
                                />

                                {errors.code && (
                                    <p className="mt-2 text-sm text-red-600">
                                        {errors.code}
                                    </p>
                                )}
                            </div>

                            <p>{timeLeft}</p>

                            <div className="mt-6 flex flex-col gap-3 sm:flex-row">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-2xl bg-red-600 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-50"
                                >
                                    {processing ? 'Vérification...' : 'Valider mon code'}
                                </button>
                            </div>
                        </form>

                        <p className="mt-6 text-sm leading-7 text-neutral-500">
                            Si vous ne trouvez pas l’e-mail, vérifiez vos spams ou
                            contactez notre support.
                        </p>
                    </section>

                    <aside className="flex-1 rounded-[32px] border border-neutral-900/10 bg-neutral-900 p-8 text-white shadow-[0_20px_80px_-30px_rgba(0,0,0,0.45)] sm:p-10 lg:p-12">
                        <p className="text-sm font-semibold uppercase tracking-[0.35em] text-neutral-400">
                            À retenir
                        </p>

                        <h2 className="mt-3 text-2xl font-semibold">
                            Une réservation confirmée, en un instant
                        </h2>

                        <ul className="mt-6 space-y-4 text-sm leading-7 text-neutral-300">
                            <li className="flex gap-3">
                                <span className="mt-1 text-red-400">•</span>
                                <span>Confirmation envoyée directement à votre adresse mail.</span>
                            </li>
                            <li className="flex gap-3">
                                <span className="mt-1 text-red-400">•</span>
                                <span>Accès rapide à votre billet et aux détails de la séance.</span>
                            </li>
                            <li className="flex gap-3">
                                <span className="mt-1 text-red-400">•</span>
                                <span>Support disponible si vous rencontrez un souci.</span>
                            </li>
                        </ul>

                        <div className="mt-8 rounded-2xl border border-white/10 bg-white/10 p-5">
                            <p className="text-sm font-semibold uppercase tracking-[0.3em] text-neutral-400">
                                Besoin d’aide ?
                            </p>
                            <p className="mt-2 text-lg font-medium text-white">
                                support@lagrandecinema.com
                            </p>
                        </div>

                        <Link
                            href="/"
                            className="mt-8 inline-flex text-sm font-medium text-neutral-300 transition hover:text-white"
                        >
                            ← Retour à l’accueil
                        </Link>
                    </aside>
                </div>
            </div>
        </>
    );
}
