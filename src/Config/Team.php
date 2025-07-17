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
    case FIRST_XV_MEN = '1st Team (Mens)';
    case SECOND_XV_MEN = '2nd Team (Mens)';
    case THIRD_XV_MEN = '3rd Team (Mens)';
    case FOURTH_XV_MEN = '4th Team (Mens)';
    case FIRST_XV_WOMEN = '1st Team (Women)';
    case SECOND_XV_WOMEN = '2st Team (Women)';
}
