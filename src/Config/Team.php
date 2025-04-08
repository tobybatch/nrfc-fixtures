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

}