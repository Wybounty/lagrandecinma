import { Link } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { cn } from '@/lib/utils';

type NavItem = {
    label: string;
    href: string;
};

type Props = {
    cinemaName?: string;
    items?: NavItem[];
    className?: string;
};

const defaultItems: NavItem[] = [
    { label: 'Accueil', href: '/' },
    { label: 'Films', href: '/#films' },
    { label: 'Contact', href: '/#contact' },
];

export function SiteHeader({
    cinemaName = 'La Grande Cinema',
    items = defaultItems,
    className,
}: Props) {
    return (
        <header
            className={cn(
                'sticky top-0 z-40 w-full bg-[#780000] text-white backdrop-blur-xl',
                className,
            )}
        >
            <div className="mx-auto flex h-20 w-full items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <Link
                    href="/"
                    prefetch
                    className="text-lg font-semibold tracking-tight text-white transition hover:opacity-80"
                >
                    {cinemaName}
                </Link>

                <Sheet>
                    <SheetTrigger asChild>
                        <Button
                            variant="ghost"
                            className="h-12 rounded-xl bg-white px-6 text-base font-semibold text-[#780000] transition hover:bg-red-50 hover:text-red-700 sm:px-8"
                            aria-label="Ouvrir le menu"
                        >
                            <Menu className="size-5" />
                            <span>Menu</span>
                        </Button>
                    </SheetTrigger>

                    <SheetContent
                        side="right"
                        className="w-[85vw] border-neutral-200 bg-white px-0 text-neutral-950 sm:max-w-sm"
                    >
                        <SheetTitle className="sr-only">
                            Menu principal
                        </SheetTitle>
                        <SheetHeader className="border-b border-neutral-200 px-6 pt-6 pb-4">
                            <p className="text-sm tracking-[0.25em] text-neutral-500 uppercase">
                                Navigation
                            </p>
                            <h2 className="text-xl font-semibold text-neutral-950">
                                {cinemaName}
                            </h2>
                        </SheetHeader>

                        <nav className="flex flex-col gap-2 px-4 py-4">
                            {items.map((item) => (
                                <Link
                                    key={item.label}
                                    href={item.href}
                                    className="group flex items-center justify-between rounded-2xl px-4 py-3 text-base font-medium text-neutral-900 transition hover:bg-red-50 hover:text-red-600"
                                >
                                    <span>{item.label}</span>
                                    <span className="translate-x-0 text-neutral-400 transition group-hover:translate-x-1 group-hover:text-red-600">
                                        &gt;
                                    </span>
                                </Link>
                            ))}
                        </nav>
                    </SheetContent>
                </Sheet>
            </div>
        </header>
    );
}
