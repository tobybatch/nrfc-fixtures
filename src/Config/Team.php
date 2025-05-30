<?php

// src/Config/Competition.php

namespace App\Config;

enum Team: string
{
    case Minis = 'Minis';
    case U13B = 'U13B';
    case U14B = 'U14B';
    case U15B = 'U15B';
    case U16B = 'U16B';
    case U18B = 'U18B';
    case U12G = 'U12G';
    case U14G = 'U14G';
    case U16G = 'U16G';
    case U18G = 'U18G';

    public static function getBy(string $value): ?Team
    {
        return match ($value) {
            'Minis' => self::Minis,
            'U13B' => self::U13B,
            'U14B' => self::U14B,
            'U15B' => self::U15B,
            'U16B' => self::U16B,
            'U18B' => self::U18B,
            'U12G' => self::U12G,
            'U14G' => self::U14G,
            'U16G' => self::U16G,
            'U18G' => self::U18G,
            default => null,
        };
    }
}
