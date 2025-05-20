<?php

namespace App\Tests\Unit\Config;

use App\Config\Competition;
use PHPUnit\Framework\TestCase;

class CompetitionTest extends TestCase
{
    public function testShortValue(): void
    {
        $this->assertEquals('CC', Competition::CountyCup->shortValue());
        $this->assertEquals('NC', Competition::NationalCup->shortValue());
        $this->assertEquals('Conf', Competition::Conference->shortValue());
        $this->assertEquals('', Competition::Training->shortValue());
        $this->assertEquals('', Competition::Friendly->shortValue());
        $this->assertEquals('', Competition::Pathway->shortValue());
        $this->assertEquals('', Competition::Festival->shortValue());
        $this->assertEquals('', Competition::Norfolk10s->shortValue());
        $this->assertEquals('', Competition::None->shortValue());
    }

    public function testValues(): void
    {
        $this->assertEquals('None', Competition::None->value);
        $this->assertEquals('Training', Competition::Training->value);
        $this->assertEquals('Friendly', Competition::Friendly->value);
        $this->assertEquals('CountyCup', Competition::CountyCup->value);
        $this->assertEquals('Pathway', Competition::Pathway->value);
        $this->assertEquals('Festival', Competition::Festival->value);
        $this->assertEquals('NationalCup', Competition::NationalCup->value);
        $this->assertEquals('Norfolk10s', Competition::Norfolk10s->value);
        $this->assertEquals('Conference', Competition::Conference->value);
    }
} 