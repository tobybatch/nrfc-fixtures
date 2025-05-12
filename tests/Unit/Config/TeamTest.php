<?php

namespace App\Tests\Config;

use App\Config\Team;
use PHPUnit\Framework\TestCase;

class TeamTest extends TestCase
{
    public function testGetBy(): void
    {
        $this->assertEquals(Team::Minis, Team::getBy('Minis'));
        $this->assertEquals(Team::U13B, Team::getBy('U13B'));
        $this->assertEquals(Team::U14B, Team::getBy('U14B'));
        $this->assertEquals(Team::U15B, Team::getBy('U15B'));
        $this->assertEquals(Team::U16B, Team::getBy('U16B'));
        $this->assertEquals(Team::U18B, Team::getBy('U18B'));
        $this->assertEquals(Team::U12G, Team::getBy('U12G'));
        $this->assertEquals(Team::U14G, Team::getBy('U14G'));
        $this->assertEquals(Team::U16G, Team::getBy('U16G'));
        $this->assertEquals(Team::U18G, Team::getBy('U18G'));
    }

    public function testValues(): void
    {
        $this->assertEquals('Minis', Team::Minis->value);
        $this->assertEquals('U13B', Team::U13B->value);
        $this->assertEquals('U14B', Team::U14B->value);
        $this->assertEquals('U15B', Team::U15B->value);
        $this->assertEquals('U16B', Team::U16B->value);
        $this->assertEquals('U18B', Team::U18B->value);
        $this->assertEquals('U12G', Team::U12G->value);
        $this->assertEquals('U14G', Team::U14G->value);
        $this->assertEquals('U16G', Team::U16G->value);
        $this->assertEquals('U18G', Team::U18G->value);
    }
} 