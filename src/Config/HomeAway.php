<?php
// src/Config/Competition.php
namespace App\Config;

enum HomeAway: string
{
    case Home = 'H';
    case Away = 'A';
    case TBA = 'TBA';

}