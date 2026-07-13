import { Head, Link } from '@inertiajs/react';

export default function Cancel() {
    return (
        <>
            <Head title="Paiement annulé" />

            <div className="min-h-screen bg-neutral-100 px-4 py-12 sm:px-6 lg:px-8">
                <div className="mx-auto flex min-h-[70vh] max-w-2xl items-center">
                    <div className="w-full rounded-3xl bg-white p-10 text-center shadow-xl">
                        <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-3xl text-red-600">
                            ✕
                        </div>

                        <h1 className="mt-6 text-3xl font-bold text-neutral-900">
                            Paiement annulé
                        </h1>

                        <p className="mt-4 text-lg text-neutral-600">
                            Votre paiement a été annulé. Aucun billet n’a été
                            réservé.
                        </p>

                        <p className="mt-2 text-neutral-500">
                            Vous pouvez retourner à la liste des films et
                            recommencer votre réservation.
                        </p>

                        <div className="mt-8 flex justify-center gap-4">
                            <Link
                                href="/#films"
                                className="inline-flex rounded-xl bg-red-600 px-8 py-4 font-semibold text-white transition hover:bg-red-700"
                            >
                                Voir les films
                            </Link>

                            <Link
                                href="/"
                                className="inline-flex rounded-xl border border-neutral-300 px-8 py-4 font-semibold text-neutral-700 transition hover:bg-neutral-100"
                            >
                                Retour à l’accueil
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}