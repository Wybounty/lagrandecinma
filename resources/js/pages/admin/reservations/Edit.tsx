import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type SessionOption = {
    id: number;
    label: string;
    available_seats: number;
};

type ReservationForm = {
    id: number;
    cinema_session_id: string;
    first_name: string;
    last_name: string;
    email: string;
    quantity: number;
    status: string;
};

type Props = {
    reservation: ReservationForm;
    sessions: SessionOption[];
};

export default function Edit({ reservation, sessions }: Props) {
    const { data, setData, put, processing, errors } = useForm<ReservationForm>(reservation);

    const selectedSession = sessions.find(
        (session) => session.id.toString() === data.cinema_session_id,
    );

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        put(`/admin/reservations/${reservation.id}`);
    }

    return (
        <>
            <Head title={`Modifier la réservation #${reservation.id}`} />

            <div className="space-y-6 p-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Modifier la réservation #{reservation.id}</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <form onSubmit={submit} className="space-y-5">
                            <Field label="Séance" error={errors.cinema_session_id}>
                                <select
                                    value={data.cinema_session_id}
                                    onChange={(event) => setData('cinema_session_id', event.target.value)}
                                    className="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    <option value="">Choisir une séance</option>
                                    {sessions.map((session) => (
                                        <option key={session.id} value={session.id}>
                                            {session.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            <div className="grid gap-5 md:grid-cols-2">
                                <Field label="Prénom" error={errors.first_name}>
                                    <Input
                                        value={data.first_name}
                                        onChange={(event) => setData('first_name', event.target.value)}
                                    />
                                </Field>

                                <Field label="Nom" error={errors.last_name}>
                                    <Input
                                        value={data.last_name}
                                        onChange={(event) => setData('last_name', event.target.value)}
                                    />
                                </Field>
                            </div>

                            <div className="grid gap-5 md:grid-cols-2">
                                <Field label="Email" error={errors.email}>
                                    <Input
                                        type="email"
                                        value={data.email}
                                        onChange={(event) => setData('email', event.target.value)}
                                    />
                                </Field>

                                <Field label="Quantité" error={errors.quantity}>
                                    <Input
                                        type="number"
                                        min={1}
                                        value={data.quantity}
                                        onChange={(event) => setData('quantity', Number(event.target.value))}
                                    />
                                </Field>
                            </div>

                            <Field label="Statut" error={errors.status}>
                                <select
                                    value={data.status}
                                    onChange={(event) => setData('status', event.target.value)}
                                    className="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    <option value="confirmed">Confirmée</option>
                                    <option value="cancelled">Annulée</option>
                                </select>
                            </Field>

                            {selectedSession && (
                                <p className="rounded-md border bg-muted/50 px-4 py-3 text-sm text-muted-foreground">
                                    Places disponibles pour cette séance: {selectedSession.available_seats}
                                </p>
                            )}

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
