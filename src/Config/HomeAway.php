<?php
// src/Config/Competition.php
namespace App\Config;

enum HomeAway: string
{
    case Home = 'H';
    case Away = 'A';
    case TBA = 'TBA';

    public static function isHomeOrAway($detail): HomeAway
    {
        if (str_contains($detail, '(H)')) {
            return HomeAway::Home;
        }
        if (str_contains($detail, '(A)')) {
            return HomeAway::Away;
        }
        return HomeAway::TBA;
    }
}