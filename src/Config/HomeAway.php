<?php
// src/Config/Competition.php
namespace App\Config;

enum HomeAway: string
{
    case Home = 'Home';
    case Away = 'Away';
    case TBA = 'TBA';

}