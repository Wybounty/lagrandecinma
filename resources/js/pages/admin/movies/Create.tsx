import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type MovieForm = {
    title: string;
    description: string;
    genre: string;
    duration: number;
    release_date: string;
    poster: File | null;
    trailer_url: string;
    is_active: boolean;
};

type Props = {
    movie: MovieForm;
    genres: Array<{ value: string; label: string }>;
};

export default function Create({ movie, genres }: Props) {
    const { data, setData, post, processing, errors } = useForm<MovieForm>(movie);

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        post('/admin/movies', {
            forceFormData: true,
        });
    }

    return (
        <>
            <Head title="Créer un film" />

            <div className="space-y-6 p-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Créer un film</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <form onSubmit={submit} className="space-y-5">
                            <Field label="Titre" error={errors.title}>
                                <Input value={data.title} onChange={(event) => setData('title', event.target.value)} />
                            </Field>

                            <Field label="Genre" error={errors.genre}>
                                <select
                                    value={data.genre}
                                    onChange={(event) => setData('genre', event.target.value)}
                                    className="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    {genres.map((genre) => (
                                        <option key={genre.value} value={genre.value}>
                                            {genre.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            <Field label="Durée (minutes)" error={errors.duration}>
                                <Input
                                    type="number"
                                    min={1}
                                    value={data.duration}
                                    onChange={(event) => setData('duration', Number(event.target.value))}
                                />
                            </Field>

                            <Field label="Date de sortie" error={errors.release_date}>
                                <Input
                                    type="date"
                                    value={data.release_date}
                                    onChange={(event) => setData('release_date', event.target.value)}
                                />
                            </Field>

                            <Field label="Affiche" error={errors.poster}>
                                <div className="space-y-3">
                                    <Input
                                        type="file"
                                        accept="image/*"
                                        onChange={(event) =>
                                            setData('poster', event.target.files?.[0] ?? null)
                                        }
                                    />

                                    {data.poster && (
                                        <img
                                            src={URL.createObjectURL(data.poster)}
                                            alt="Aperçu de l'affiche"
                                            className="h-40 w-28 rounded-lg object-cover"
                                        />
                                    )}
                                </div>
                            </Field>

                            <Field label="Bande-annonce" error={errors.trailer_url}>
                                <Input
                                    type="url"
                                    value={data.trailer_url}
                                    onChange={(event) => setData('trailer_url', event.target.value)}
                                    placeholder="https://www.youtube.com/watch?v=..."
                                />
                            </Field>

                            <Field label="Description" error={errors.description}>
                                <textarea
                                    value={data.description}
                                    onChange={(event) => setData('description', event.target.value)}
                                    className="min-h-40 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                />
                            </Field>

                            <label className="flex items-center gap-3 text-sm font-medium">
                                <input
                                    type="checkbox"
                                    checked={data.is_active}
                                    onChange={(event) => setData('is_active', event.target.checked)}
                                    className="size-4 rounded border-input"
                                />
                                Actif
                            </label>

                            <Button type="submit" disabled={processing}>
                                {processing ? 'Enregistrement...' : 'Créer le film'}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <div className="space-y-2">
            <Label>{label}</Label>
            {children}
            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    );
}
