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
    case FIRST_XV = "1st Team (Mens)";
    case LIONS = "2nd Team (Mens)";
    case AXV = "3rd Team (Mens)";
    case SENIOR_WOMEN = "1st Team (Women)";

    public static function getYouth(): array {
        return array(
            Team::Minis,
            Team::U13B,
            Team::U14B,
            Team::U15B,
            Team::U16B,
            TEAM::U18B,
            TEAM::U12G,
            TEAM::U14G,
            TEAM::U16G,
            Team::U18G,
        );
    }
    public static function getBoys(): array {
        return array(
            Team::Minis,
            Team::U13B,
            Team::U14B,
            Team::U15B,
            Team::U16B,
            TEAM::U18B,
        );
    }
    public static function getGirls(): array {
        return array(
            Team::Minis,
            TEAM::U12G,
            TEAM::U14G,
            TEAM::U16G,
            Team::U18G,
        );
    }
    public static function getSenior(): array {
        return array(
            Team::FIRST_XV,
            Team::LIONS,
            Team::AXV,
            Team::SENIOR_WOMEN,
        );
    }

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
            '1st Team (Mens)', 'FIRST_XV', '1st XV Fixture' => self::FIRST_XV,
            '2nd Team (Mens)', 'LIONS', 'Lions Fixture' => self::LIONS,
            '3rd Team (Mens)', 'AXV' => self::AXV,
            '1st Team (Women)', 'SENIOR_WOMEN' => self::SENIOR_WOMEN,
            default => null,
        };
    }
}
