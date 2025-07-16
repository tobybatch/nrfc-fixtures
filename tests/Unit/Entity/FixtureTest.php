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
}
