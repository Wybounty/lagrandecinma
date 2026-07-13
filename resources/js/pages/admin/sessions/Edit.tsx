import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Option = {
    id: number;
    label: string;
};

type SessionForm = {
    id: number;
    movie_id: string;
    room_id: string;
    starts_at: string;
    price: string;
    is_active: boolean;
};

type Props = {
    session: SessionForm;
    movies: Option[];
    rooms: Option[];
};

export default function Edit({ session, movies, rooms }: Props) {
    const { data, setData, put, processing, errors } = useForm<SessionForm>(session);

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        put(`/admin/sessions/${session.id}`);
    }

    return (
        <>
            <Head title="Modifier la séance" />

            <div className="space-y-6 p-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Modifier la séance</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <form onSubmit={submit} className="space-y-5">
                            <Field label="Film" error={errors.movie_id}>
                                <select
                                    value={data.movie_id}
                                    onChange={(event) => setData('movie_id', event.target.value)}
                                    className="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    <option value="">Choisir un film</option>
                                    {movies.map((movie) => (
                                        <option key={movie.id} value={movie.id}>
                                            {movie.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            <Field label="Salle" error={errors.room_id}>
                                <select
                                    value={data.room_id}
                                    onChange={(event) => setData('room_id', event.target.value)}
                                    className="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    <option value="">Choisir une salle</option>
                                    {rooms.map((room) => (
                                        <option key={room.id} value={room.id}>
                                            {room.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            <Field label="Date et heure" error={errors.starts_at}>
                                <Input
                                    type="datetime-local"
                                    value={data.starts_at}
                                    onChange={(event) => setData('starts_at', event.target.value)}
                                />
                            </Field>

                            <Field label="Prix" error={errors.price}>
                                <Input
                                    type="number"
                                    step="0.01"
                                    min={0}
                                    value={data.price}
                                    onChange={(event) => setData('price', event.target.value)}
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
                                {processing ? 'Mise à jour...' : 'Mettre à jour'}
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
