<?php

namespace App\Tests\Entity;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Club;
use App\Entity\Fixture;
use PHPUnit\Framework\TestCase;

class FixtureTest extends TestCase
{
    private Fixture $fixture;

    protected function setUp(): void
    {
        $this->fixture = new Fixture();
    }

    public function testGetId(): void
    {
        $this->assertNull($this->fixture->getId());
    }

    public function testGetDate(): void
    {
        $this->assertNull($this->fixture->getDate());
    }

    public function testSetDate(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        $this->fixture->setDate($date);
        $this->assertEquals($date, $this->fixture->getDate());
    }

    public function testGetTeam(): void
    {
        $this->assertNull($this->fixture->getTeam());
    }

    public function testSetTeam(): void
    {
        $team = Team::U13B;
        $this->fixture->setTeam($team);
        $this->assertEquals($team, $this->fixture->getTeam());
    }

    public function testGetCompetition(): void
    {
        $this->assertNull($this->fixture->getCompetition());
    }

    public function testSetCompetition(): void
    {
        $competition = Competition::Friendly;
        $this->fixture->setCompetition($competition);
        $this->assertEquals($competition, $this->fixture->getCompetition());
    }

    public function testGetHomeAway(): void
    {
        $this->assertNull($this->fixture->getHomeAway());
    }

    public function testSetHomeAway(): void
    {
        $homeAway = HomeAway::Home;
        $this->fixture->setHomeAway($homeAway);
        $this->assertEquals($homeAway, $this->fixture->getHomeAway());
    }

    public function testGetClub(): void
    {
        $this->assertNull($this->fixture->getClub());
    }

    public function testSetClub(): void
    {
        $club = new Club();
        $this->fixture->setClub($club);
        $this->assertEquals($club, $this->fixture->getClub());
    }

    public function testGetNotes(): void
    {
        $this->assertNull($this->fixture->getNotes());
    }

    public function testSetNotes(): void
    {
        $notes = 'Test notes';
        $this->fixture->setNotes($notes);
        $this->assertEquals($notes, $this->fixture->getNotes());
    }

    public function testGettersAndSetters(): void
    {
        $date = new \DateTimeImmutable('2023-01-01');
        
        $this->fixture->setDate($date);
        $this->assertEquals($date, $this->fixture->getDate());

        $this->fixture->setClub($this->club);
        $this->assertEquals($this->club, $this->fixture->getClub());

        $this->fixture->setHomeAway(HomeAway::Home);
        $this->assertEquals(HomeAway::Home, $this->fixture->getHomeAway());

        $this->fixture->setCompetition(Competition::Friendly);
        $this->assertEquals(Competition::Friendly, $this->fixture->getCompetition());

        $this->fixture->setTeam(Team::U13B);
        $this->assertEquals(Team::U13B, $this->fixture->getTeam());

        $this->fixture->setName('Test Fixture');
        $this->assertEquals('Test Fixture', $this->fixture->getName());

        $this->fixture->setNotes('Test Notes');
        $this->assertEquals('Test Notes', $this->fixture->getNotes());
    }

    public function testFormat(): void
    {
        $this->fixture->setClub($this->club);
        $this->fixture->setHomeAway(HomeAway::Home);
        $this->fixture->setCompetition(Competition::Friendly);

        // Test with home/away included
        $this->assertEquals('Test Club (H)', $this->fixture->format());

        // Test with competition included
        $this->assertEquals('Test Club (H) []', $this->fixture->format(true, true));

        // Test with name instead of club
        $this->fixture->setClub(null);
        $this->fixture->setName('Test Name');
        $this->assertEquals('Test Name (H)', $this->fixture->format());

        // Test with no name or club
        $this->fixture->setName(null);
        $this->assertEquals('', $this->fixture->format());
    }

    public function testToString(): void
    {
        $this->fixture->setClub($this->club);
        $this->fixture->setHomeAway(HomeAway::Home);
        $this->assertEquals('Test Club (H)', (string)$this->fixture);
    }
} 