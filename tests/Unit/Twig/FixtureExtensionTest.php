<?php

namespace App\Tests\Twig;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Club;
use App\Entity\Fixture;
use App\Twig\FixtureExtension;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class FixtureExtensionTest extends TestCase
{
    private FixtureExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new FixtureExtension();
    }

    public function testGetFilters(): void
    {
        $filters = $this->extension->getFilters();
        $this->assertCount(1, $filters);
        $this->assertEquals('fixtureSummary', $filters[0]->getName());
    }

    public function testGetFunctions(): void
    {
        $filters = $this->extension->getFunctions();
        $this->assertCount(3, $filters);
        $this->assertEquals('dateIsNew', $filters[0]->getName());
        $this->assertEquals('dateIsNotSet', $filters[1]->getName());
        $this->assertEquals('dateIsPast', $filters[2]->getName());
    }


    public function testDateIsNew(): void
    {
        $date1 = new DateTimeImmutable('2023-01-01');
        $date2 = new DateTimeImmutable('2023-01-01');
        $date3 = new DateTimeImmutable('2023-01-02');

        $this->assertTrue($this->extension->dateIsNew($date1, $date2));
        $this->assertFalse($this->extension->dateIsNew($date1, $date3));
    }

    public function testDateIsNotSet(): void
    {
        $this->assertTrue(
            $this->extension->dateIsNotSet(new \DateTimeImmutable('2023-01-01 00:00:00'))
        );
        $this->assertFalse(
            $this->extension->dateIsNotSet(new \DateTimeImmutable('2023-01-01 10:00:00'))
        );
    }

    public function testFixtureSummaryTrainingWithClub(): void
    {
        $fixture = new Fixture();
        $fixture->setName('Test Fixture');
        $fixture->setTeam(Team::U13B);
        $fixture->setHomeAway(HomeAway::Home);
        $fixture->setCompetition(Competition::Training);

        $club = new Club();
        $club->setName('Test Club');
        $fixture->setClub($club);

        $expected = 'U13B Training with Test Club (H)';
        $this->assertEquals($expected, $this->extension->fixtureSummary($fixture));
    }

    public function testFixtureSummaryTrainingWithNoClub(): void
    {
        $fixture = new Fixture();
        $fixture->setName('Test Fixture');
        $fixture->setTeam(Team::U13B);
        $fixture->setHomeAway(HomeAway::Home);
        $fixture->setCompetition(Competition::Training);

        $expected = 'U13B Training';
        $this->assertEquals($expected, $this->extension->fixtureSummary($fixture));
    }

    public function testFixtureSummaryWithClubComp(): void
    {
        $fixture = new Fixture();
        $fixture->setName('Test Fixture');
        $fixture->setTeam(Team::U13B);
        $fixture->setHomeAway(HomeAway::Home);
        $fixture->setCompetition(Competition::Friendly);

        $club = new Club();
        $club->setName('Test Club');
        $fixture->setClub($club);

        $expected = 'U13B Test Club vs Friendly (H)';
        $this->assertEquals($expected, $this->extension->fixtureSummary($fixture));
    }

    public function testFixtureSummaryWithClubNoComp(): void
    {
        $fixture = new Fixture();
        $fixture->setName('Test Fixture');
        $fixture->setTeam(Team::U13B);
        $fixture->setHomeAway(HomeAway::Home);
        $fixture->setCompetition(Competition::None);

        $club = new Club();
        $club->setName('Test Club');
        $fixture->setClub($club);

        $expected = 'U13B vs Test Club (H)';
        $this->assertEquals($expected, $this->extension->fixtureSummary($fixture));
    }

    public function testFixtureSummaryWithNoClubComp(): void
    {
        $fixture = new Fixture();
        $fixture->setName('Test Fixture');
        $fixture->setTeam(Team::U13B);
        $fixture->setHomeAway(HomeAway::Home);
        $fixture->setCompetition(Competition::Friendly);

        $expected = 'U13B Test Fixture Friendly (H)';
        $this->assertEquals($expected, $this->extension->fixtureSummary($fixture));
    }

    public function testFixtureSummaryWithNoClubNoComp(): void
    {
        $fixture = new Fixture();
        $fixture->setName('Test Fixture');
        $fixture->setTeam(Team::U13B);
        $fixture->setHomeAway(HomeAway::Home);
        $fixture->setCompetition(Competition::None);

        $expected = 'U13B Test Fixture (H)';
        $this->assertEquals($expected, $this->extension->fixtureSummary($fixture));
    }

    public function testFixtureSummaryFailOver(): void
    {
        $fixture = new Fixture();
        $fixture->setName('Test Fixture');
        $fixture->setTeam(Team::U13B);
        $fixture->setHomeAway(HomeAway::Home);
        $fixture->setCompetition(Competition::Training);

        $club = new Club();
        $club->setName('Test Club');
        $fixture->setClub($club);

        $expected = 'U13B Training with Test Club (H)';
        $this->assertEquals($expected, $this->extension->fixtureSummary($fixture));
    }
}