import { Head, Link } from '@inertiajs/react';

export default function Confirmed() {
    return (
        <>
            <Head title="Réservation confirmée" />

            <div className="min-h-screen bg-neutral-100 px-4 py-12 sm:px-6 lg:px-8">
                <div className="mx-auto flex min-h-[70vh] max-w-2xl items-center">
                    <div className="w-full rounded-3xl bg-white p-10 text-center shadow-xl">
                        <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-3xl">
                            ✓
                        </div>

                        <h1 className="mt-6 text-3xl font-bold text-neutral-900">
                            Votre ticket a été réservé
                        </h1>

                        <p className="mt-4 text-lg text-neutral-600">
                            Votre code est correct. La réservation est maintenant confirmée.
                        </p>

                        <Link
                            href="/"
                            className="mt-8 inline-flex rounded-xl bg-red-600 px-6 py-3 font-semibold text-white transition hover:bg-red-700"
                        >
                            Retour à l’accueil
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
