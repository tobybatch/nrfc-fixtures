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
    case SENIOR_WOMEN = "1st Team (Women)";
}
