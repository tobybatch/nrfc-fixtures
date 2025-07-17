<?php

namespace App\Tests\Unit\Service;

use App\Config\Team;
use App\Service\TeamService;
use PHPUnit\Framework\TestCase;

class TeamServiceTest extends TestCase
{
    private TeamService $service;

    protected function setUp(): void
    {
        $this->service = new TeamService();
    }

    public function testGetBoys()
    {
        $expected = [
            Team::Minis,
            Team::U13B,
            Team::U14B,
            Team::U15B,
            Team::U16B,
            Team::U18B,
        ];

        $this->assertSame($expected, $this->service->getBoys());
    }

    public function testGetGirls()
    {
        $expected = [
            Team::Minis,
            Team::U12G,
            Team::U14G,
            Team::U16G,
            Team::U18G,
        ];

        $this->assertSame($expected, $this->service->getGirls());
    }

    public function testGetSeniors()
    {
        $expected = [
            Team::FIRST_XV_MEN,
            Team::SECOND_XV_MEN,
            Team::FIRST_XV_WOMEN,
        ];

        $this->assertSame($expected, $this->service->getSeniors());
    }

    public function testGetYouth()
    {
        $expected = [
            Team::Minis,
            Team::U13B,
            Team::U14B,
            Team::U15B,
            Team::U16B,
            Team::U18B,
            Team::U12G,
            Team::U14G,
            Team::U16G,
            Team::U18G,
        ];

        $this->assertSame($expected, $this->service->getYouth());
    }

    public function testGetByReturnsCorrectEnum()
    {
        $this->assertSame(Team::Minis, $this->service->getBy('Minis'));
        $this->assertSame(Team::U13B, $this->service->getBy('U13B'));
        $this->assertSame(Team::FIRST_XV_MEN, $this->service->getBy('1st Team (Mens)'));
        $this->assertSame(Team::FIRST_XV_MEN, $this->service->getBy('FIRST_XV_MEN'));
        $this->assertSame(Team::FIRST_XV_MEN, $this->service->getBy('1st XV Fixture'));
        $this->assertSame(Team::SECOND_XV_MEN, $this->service->getBy('2nd Team (Mens)'));
        $this->assertSame(Team::SECOND_XV_MEN, $this->service->getBy('SECOND_XV_MEN'));
        $this->assertSame(Team::SECOND_XV_MEN, $this->service->getBy('Lions Fixture'));
        $this->assertSame(Team::FIRST_XV_WOMEN, $this->service->getBy('1st Team (Women)'));
        $this->assertSame(Team::FIRST_XV_WOMEN, $this->service->getBy('FIRST_XV_WOMEN'));
    }

    public function testGetByReturnsNullForUnknownValue()
    {
        $this->assertNull($this->service->getBy('UnknownTeam'));
        $this->assertNull($this->service->getBy(''));
    }

    public function testIsSeniorReturnsTrueForSeniorTeams()
    {
        $this->assertTrue($this->service->isSenior(Team::FIRST_XV_MEN));
        $this->assertTrue($this->service->isSenior(Team::SECOND_XV_MEN));
        $this->assertTrue($this->service->isSenior(Team::FIRST_XV_WOMEN));
    }

    public function testIsSeniorReturnsFalseForNonSeniorTeams()
    {
        $this->assertFalse($this->service->isSenior(Team::U13B));
        $this->assertFalse($this->service->isSenior(Team::U18G));
        $this->assertFalse($this->service->isSenior(Team::Minis));
    }
}
