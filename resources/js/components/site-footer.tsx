import { Link } from '@inertiajs/react';

type FooterLink = {
    label: string;
    href: string;
};

type Props = {
    cinemaName?: string;
    links?: FooterLink[];
};

const defaultLinks: FooterLink[] = [
    { label: 'Accueil', href: '/' },
    { label: 'Films', href: '/movies' },
    { label: 'Contact', href: '/#contact' },
    { label: 'Mentions légales', href: '/mentions-legales' },
    { label: 'Politique de confidentialité', href: '/politique-de-confidentialite' },


];

export function SiteFooter({
    cinemaName = 'La Grande Cinema',
    links = defaultLinks,
}: Props) {
    return (
        <footer className="border-t border-red-900/30 bg-[#780000] text-white">
            <div className="mx-auto flex flex-col gap-6 px-4 py-8 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div>
                    <p className="text-lg font-semibold">{cinemaName}</p>
                    <p className="mt-1 text-sm text-white/80">
                        Le cinema, simple, fluide et agreable.
                    </p>
                </div>

                <nav className="flex flex-wrap gap-4 text-sm text-white/85">
                    {links.map((link) => (
                        <Link
                            key={link.label}
                            href={link.href}
                            className="transition hover:text-white"
                        >
                            {link.label}
                        </Link>
                    ))}
                </nav>
            </div>
        </footer>
    );
}
