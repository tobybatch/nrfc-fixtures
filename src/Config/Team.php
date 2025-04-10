<?php
// src/Config/Competition.php
namespace App\Config;

enum Team: int
{
    case Minis = 0;
    case U13B = 1;
    case U14B = 2;
    case U15B = 3;
    case U16B = 4;
    case U18B = 5;
    case U12G = 6;
    case U14G = 7;
    case U16G = 8;
    case U18G = 9;

    public static function fromInt(int $int): Team
    {
        return match ($int) {
            0 => Team::Minis,
            1 => Team::U13B,
            2 => Team::U14B,
            3 => Team::U15B,
            4 => Team::U16B,
            5 => Team::U18B,
            6 => Team::U12G,
            7 => Team::U14G,
            8 => Team::U16G,
            9 => Team::U18G,
            default => null,
        };
    }
}