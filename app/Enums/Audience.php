<?php

namespace App\Enums;

use InvalidArgumentException;

enum Audience : string
{
    case KIDS = 'kids';
    case MATURE = 'mature';
    case ALL = 'all';

    public static function fromValue(string $value) : Audience
    {
        return match ($value) {
            'kids' => self::KIDS,
            'mature' => self::MATURE,
            'all' => self::ALL,
            default => throw new InvalidArgumentException('Invalid value for Audience'),
        };
    }

    public static function getAll(): array
    {
        return [
            self::KIDS,
            self::MATURE,
            self::ALL,
        ];
    }
}
