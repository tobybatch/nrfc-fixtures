<?php
// src/Config/Competition.php
namespace App\Config;

enum Competition: string
{
    case None = 'None';
    case Training = 'Training';
    case Friendly = 'Friendly';
    case CountyCup = 'CountyCup';
    case Pathway = 'Pathway';
    case Festival = 'Festival';
    case NationalCup = 'NationalCup';
    case Norfolk10s = 'Norfolk10s';
    case Conference = 'Conference';

    public function shortValue(): string
    {
        return match ($this) {
            Competition::CountyCup => "CC",
            Competition::NationalCup => "NC",
            Competition::Conference => "Conf",
            // Competition::Pathway, Competition::Festival, Competition::Norfolk10s, Competition::None, Competition::Friendly => "",
            default => "",
        };
    }
}