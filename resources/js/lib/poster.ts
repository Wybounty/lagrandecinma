export function posterUrl(poster: string): string {
    if (poster.startsWith('http://') || poster.startsWith('https://') || poster.startsWith('/')) {
        return poster;
    }

    return `/storage/${poster}`;
}
