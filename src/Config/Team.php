<?php
// src/Config/Competition.php
namespace App\Config;

enum Team: string
{
    case Minis = "Minis";
    case U13B = "U13B";
    case U14B = "U14B";
    case U15B = "U15B";
    case U16B = "U16B";
    case U18B = "U18B";
    case U12G = "U12G";
    case U14G = "U14G";
    case U16G = "U16G";
    case U18G = "U18G";

    public static function getBy($value): ?Team
    {
        return match ($value) {
            "Minis" => self::Minis,
            "U13B" => self::U13B,
            "U14B" => self::U14B,
            "U15B" => self::U15B,
            "U16B" => self::U16B,
            "U18B" => self::U18B,
            "U12G" => self::U12G,
            "U14G" => self::U14G,
            "U16G" => self::U16G,
            "U18G" => self::U18G,
        };
    }
//    public static function toString(Team $team): string
//    {
//        return match($team) {
//            Team::Minis => "Minis",
//            Team::U13B => "U13B",
//            Team::U14B => "U14B",
//            Team::U15B => "U15B",
//            Team::U16B => "U16B",
//            Team::U18B => "U18B",
//            Team::U12G => "U12G",
//            Team::U14G => "U14G",
//            Team::U16G => "U16G",
//            Team::U18G => "U18G"
//        };
//    }

//    public static function fromInt(int $int): Team|null
//    {
//        return match ($int) {
//            0 => Team::Minis,
//            1 => Team::U13B,
//            2 => Team::U14B,
//            3 => Team::U15B,
//            4 => Team::U16B,
//            5 => Team::U18B,
//            6 => Team::U12G,
//            7 => Team::U14G,
//            8 => Team::U16G,
//            9 => Team::U18G,
//            default => null,
//        };
//    }
}