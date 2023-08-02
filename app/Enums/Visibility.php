<?php

namespace App\Enums;

use InvalidArgumentException;

enum Visibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case UNLISTED = 'unlisted';
    case SCHEDULED = 'scheduled';

    public static function fromValue(string $value) : Visibility
    {
        return match ($value) {
            'public' => self::PUBLIC,
            'private' => self::PRIVATE,
            'unlisted' => self::UNLISTED,
            'scheduled' => self::SCHEDULED,
            default => throw new InvalidArgumentException('Invalid value for Visibility'),
        };
    }

    public static function getAll(): array
    {
        return [
            self::PUBLIC,
            self::PRIVATE,
            self::UNLISTED,
            self::SCHEDULED,
        ];
    }
}
