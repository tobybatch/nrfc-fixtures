<?php

namespace App\Tests\Config;

use App\Config\HomeAway;
use PHPUnit\Framework\TestCase;

class HomeAwayTest extends TestCase
{
    public function testValues(): void
    {
        $this->assertEquals('H', HomeAway::Home->value);
        $this->assertEquals('A', HomeAway::Away->value);
        $this->assertEquals('TBA', HomeAway::TBA->value);
    }
}
