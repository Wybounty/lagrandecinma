import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login } from '@/routes';

interface Movie {
    id: number;
    title: string;
    description: string;
    slug: string;
    genre: string;
    duration: number;
    release_date: string;
    poster: string;
}

interface Props {
    movies: Movie[];
}

export default function Home({ movies }: Props) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="La Grande Cinéma" />

            <div className="min-h-screen bg-white">
                {/* Header */}
                <header className="flex items-center justify-end px-4 py-6 md:px-8 xl:px-10">
                    {auth.user ? (
                        <Link
                            href={dashboard()}
                            className="rounded-lg border border-neutral-300 px-5 py-2 text-sm font-medium transition hover:bg-black hover:text-white"
                        >
                            Dashboard
                        </Link>
                    ) : (
                        <Link
                            href={login()}
                            className="text-sm font-medium hover:underline"
                        >
                            Log in
                        </Link>
                    )}
                </header>

                {/* Movies */}
                <main className="mx-auto max-w-[1800px] px-4 pb-12 md:px-8 xl:px-10">
                    <div className="grid grid-cols-2 gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-4">
                        {movies.map((movie) => (
                            <Link
                                key={movie.id}
                                href={`/movies/${movie.slug}`}
                                className="group"
                            >
                                <div className="overflow-hidden rounded-2xl shadow-lg transition-all duration-300 group-hover:-translate-y-2 group-hover:shadow-2xl">
                                    <img
                                        src={`/storage/${movie.poster}`}
                                        alt={movie.slug}
                                        className="w-full object-contain transition-transform duration-500 group-hover:scale-105"
                                    />
                                </div>

                                <h2 className="mt-3 text-center text-base font-bold text-neutral-900 md:mt-5 md:text-xl xl:text-2xl">
                                    {movie.title}
                                </h2>
                            </Link>
                        ))}
                    </div>
                </main>
            </div>
        </>
    );
}