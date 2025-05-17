<?php

namespace App\Tests\Entity;

use App\Entity\Fixture;
use App\Entity\Club;
use App\Config\HomeAway;
use App\Config\Competition;
use App\Config\Team;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class FixtureTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $fixture = new Fixture();
        $date = new DateTimeImmutable('2025-05-17');
        $club = $this->createMock(Club::class);

        $fixture->setDate($date);
        $this->assertSame($date, $fixture->getDate());

        $fixture->setClub($club);
        $this->assertSame($club, $fixture->getClub());

        $fixture->setHomeAway(HomeAway::Home);
        $this->assertSame(HomeAway::Home, $fixture->getHomeAway());

        $fixture->setCompetition(Competition::Friendly);
        $this->assertSame(Competition::Friendly, $fixture->getCompetition());

        $fixture->setTeam(Team::U13B);
        $this->assertSame(Team::U13B, $fixture->getTeam());

        $fixture->setName('Norwich v Cambridge');
        $this->assertSame('Norwich v Cambridge', $fixture->getName());

        $fixture->setNotes('Postponed due to weather');
        $this->assertSame('Postponed due to weather', $fixture->getNotes());
    }

    public function testFormatWithClub(): void
    {
        $club = $this->createMock(Club::class);
        $club->method('getName')->willReturn('Norwich RFC');

        $fixture = new Fixture();
        $fixture->setClub($club);
        $fixture->setCompetition(Competition::Friendly);
        $fixture->setHomeAway(HomeAway::Home);

        $this->assertSame('Norwich RFC (H)', $fixture->format());
        $this->assertStringContainsString('Norwich RFC', (string) $fixture);
    }

    public function testFormatWithNameFallback(): void
    {
        $fixture = new Fixture();
        $fixture->setName('Cambridge RFC');
        $fixture->setCompetition(Competition::Friendly);
        $fixture->setHomeAway(HomeAway::Away);

        $this->assertSame('Cambridge RFC (A)', $fixture->format());
    }

    public function testFormatWithCompetitionIncluded(): void
    {
        $fixture = new Fixture();
        $fixture->setName('Cambridge RFC');
        $fixture->setCompetition(Competition::NationalCup);
        $fixture->setHomeAway(HomeAway::Away);

        $expected = 'Cambridge RFC (A) [' . Competition::NationalCup->shortValue() . ']';
        $this->assertSame($expected, $fixture->format(true, true));
    }

    public function testFormatReturnsFallback(): void
    {
        $fixture = new Fixture();
        $fixture->setCompetition(Competition::None);
        $fixture->setHomeAway(HomeAway::Away);
        $this->assertSame('???', $fixture->format());
    }
}
