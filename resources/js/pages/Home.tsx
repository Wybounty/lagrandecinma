import { Head, Link } from '@inertiajs/react';
import { SiteFooter } from '@/components/site-footer';
import { SiteHeader } from '@/components/site-header';
import { posterUrl } from '@/lib/poster';

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
    return (
        <>
            <Head title="La Grande Cinema" />

            <div className="min-h-screen bg-white">
                <SiteHeader cinemaName="La Grande Cinema" />

                <main className="min-h-screen w-full">
                    <section className="relative h-[500px] w-full overflow-hidden">
                        <img
                            src="https://images.pexels.com/photos/34757792/pexels-photo-34757792.jpeg"
                            alt="Affiche du cinéma"
                            className="absolute inset-0 h-full w-full object-cover"
                        />

                        <div className="absolute inset-0 z-10 bg-black/93"></div>

                        <div className="relative z-20 flex h-full flex-col items-center justify-center gap-4">
                            <h1 className="text-3xl font-bold text-white lg:text-6xl">
                                Bienvenue chez
                            </h1>
                            <h1 className="text-3xl font-bold text-[#ffb703] lg:text-6xl">
                                La Grande Cinema
                            </h1>
                            <div className="flex h-[100px] w-full flex-row items-center justify-center gap-4">
                                <Link
                                    href="/#films"
                                    className="rounded-xl bg-red-600 px-8 py-4 font-semibold text-white transition hover:bg-red-700"
                                >
                                    Voir les films
                                </Link>
                                <Link
                                    href="/#contact"
                                    className="rounded-xl bg-white px-8 py-4 font-semibold text-black transition hover:bg-white/90"
                                >
                                    Nous contacter
                                </Link>
                            </div>
                        </div>
                    </section>

                    <section
                        id="films"
                        className="mx-auto min-h-screen scroll-mt-24 px-4 pt-8 pb-12 md:px-8 xl:px-10"
                    >
                        <div className="grid grid-cols-2 gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-4">
                            {movies.map((movie) => (
                                <Link
                                    key={movie.id}
                                    href={`/movies/${movie.slug}`}
                                    className="group"
                                >
                                    <div className="overflow-hidden rounded-2xl shadow-lg transition-all duration-300 group-hover:-translate-y-2 group-hover:shadow-2xl">
                                        <div className="aspect-[2/3] w-full overflow-hidden">
                                            <img
                                                src={posterUrl(movie.poster)}
                                                alt={movie.title}
                                                className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                            />
                                        </div>
                                    </div>

                                    <h2 className="mt-3 text-center text-base font-bold text-neutral-900 md:mt-5 md:text-xl xl:text-2xl">
                                        {movie.title}
                                    </h2>
                                </Link>
                            ))}
                        </div>
                    </section>

                    <section className="bg-black px-4 py-16 text-white md:px-8 xl:px-10" id="contact">
                        <div className="mx-auto grid gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-start">
                            <div className="space-y-5">
                                <p className="text-sm tracking-[0.3em] text-white/70 uppercase">
                                    Contact
                                </p>
                                <h2 className="text-3xl max-w-5xl font-bold tracking-tight md:text-5xl">
                                    Ecrivez-nous pour une
                                    question ou une demande speciale.
                                </h2>
                                <p className="max-w-xl text-base leading-7 text-white/80 md:text-lg">
                                    Ce formulaire est uniquement visuel pour le
                                    moment. Il sert a poser la structure du
                                    futur contact client avec un design propre
                                    et lisible.
                                </p>
                                <a
                                    href="mailto:contact@lagrandecinma.fr"
                                    className="inline-block rounded-xl bg-red-600 px-8 py-4 font-semibold text-white transition hover:bg-red-700"
                                >
                                    Nous contacter
                                </a>
                            </div>
                        </div>
                    </section>
                </main>

                <SiteFooter cinemaName="La Grande Cinema" />
            </div>
        </>
    );
}
