<?php

namespace App\Enums;

enum MovieGenre: string
{
    case Action = 'Action';
    case Aventure = 'Aventure';
    case Animation = 'Animation';
    case Comedie = 'Comedie';
    case Drame = 'Drame';
    case Fantastique = 'Fantastique';
    case Horreur = 'Horreur';
    case Romance = 'Romance';
    case ScienceFiction = 'Science-fiction';
    case Thriller = 'Thriller';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $genre): string => $genre->value,
            self::cases(),
        );
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $genre): array => [
                'value' => $genre->value,
                'label' => $genre->label(),
            ],
            self::cases(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::ScienceFiction => 'Science-fiction',
            default => $this->value,
        };
    }
}
