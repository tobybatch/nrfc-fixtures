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
        $functions = $this->extension->getFunctions();
        $this->assertCount(2, $functions);
        $this->assertEquals('dateIsNew', $functions[0]->getName());
        $this->assertEquals('dateIsNotSet', $functions[1]->getName());
    }

    public function testFixtureSummary(): void
    {
        $fixture = new Fixture();
        $fixture->setTeam(Team::U13B);
        $fixture->setHomeAway(HomeAway::Home);
        $fixture->setCompetition(Competition::Friendly);

        $club = new Club();
        $club->setName('Test Club');
        $fixture->setClub($club);

        $expected = 'U13B vs Test Club (H)';
        $this->assertEquals($expected, $this->extension->fixtureSummary($fixture));
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
        $this->assertFalse(
            $this->extension->dateIsNotSet(
                new DateTimeImmutable('2023-01-01')
            )
        );

        $this->assertTrue(
            $this->extension->dateIsNotSet(
                new DateTimeImmutable('2023-01-01 12:01:00')
            )
        );
    }

} 