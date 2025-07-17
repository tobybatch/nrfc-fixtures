<?php

namespace App\Form\Model;

class FixturesDisplayOptionsDTO
{
    /**
     * @var array<string>
     */
    public array $teams = [];

    public bool $showPastDates = false;
}
