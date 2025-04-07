<?php
// src/Config/Competition.php
namespace App\Config;

enum Competition: string
{
    case None = 'None';
    case Friendly = 'Friendly';
    case CountyCup = 'CountyCup';
    case Pathway = 'Pathway';
    case Festival = 'Festival';
    case NationalCup = 'NationalCup';
    case Norfolk10s = 'Norfolk10s';
    case Conference = 'Conference';

}