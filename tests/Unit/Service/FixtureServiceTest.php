<?php

namespace App\Tests\Unit\Service;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Club;
use App\Entity\Fixture;
use App\Service\FixtureService;
use App\Service\TeamService;
use PHPUnit\Framework\TestCase;

class FixtureServiceTest extends TestCase
{
    private TeamService $teamService;
    private FixtureService $fixtureService;

    protected function setUp(): void
    {
        $this->teamService = $this->createMock(TeamService::class);
        $this->fixtureService = new FixtureService($this->teamService);
    }

    public function testFormatWithClubAndSeniorTeam()
    {
        $club = $this->createMock(Club::class);
        $club->method('getName')->willReturn('Tigers');

        $team = Team::FIRST_XV_MEN;

        $fixture = $this->createMock(Fixture::class);
        $fixture->method('getClub')->willReturn($club);
        $fixture->method('getTeam')->willReturn($team);
        $fixture->method('getCompetition')->willReturn(Competition::League);
        $fixture->method('getHomeAway')->willReturn(HomeAway::Home);

        $this->teamService
            ->method('isSenior')
            ->with($team)
            ->willReturn(true);

        $expected = 'Tigers 1 (H)';
        $this->assertSame($expected, $this->fixtureService->format($fixture));
    }

    public function testFormatWithClubAndYouthTeamAndCompetitionShown()
    {
        $club = $this->createMock(Club::class);
        $club->method('getName')->willReturn('Falcons');

        $team = Team::FIRST_XV_MEN;

        $fixture = $this->createMock(Fixture::class);
        $fixture->method('getClub')->willReturn($club);
        $fixture->method('getTeam')->willReturn($team);
        $fixture->method('getCompetition')->willReturn(Competition::CountyCup);
        $fixture->method('getHomeAway')->willReturn(HomeAway::Away);

        $this->teamService
            ->method('isSenior')
            ->with($team)
            ->willReturn(true);

        $expected = 'Falcons 1 (A)';
        $this->assertSame($expected, $this->fixtureService->format($fixture));
    }

    public function testFormatWithNameFallback()
    {
        $fixture = $this->createMock(Fixture::class);
        $fixture->method('getClub')->willReturn(null);
        $fixture->method('getName')->willReturn('Friendly Match');
        $fixture->method('getCompetition')->willReturn(Competition::None);
        $fixture->method('getHomeAway')->willReturn(HomeAway::Home);

        $expected = 'Friendly Match';
        $this->assertSame($expected, $this->fixtureService->format($fixture));
    }

    public function testFormatWithCompetitionFallback()
    {
        $fixture = $this->createMock(Fixture::class);
        $fixture->method('getClub')->willReturn(null);
        $fixture->method('getName')->willReturn(null);
        $fixture->method('getCompetition')->willReturn(Competition::League);
        $fixture->method('getHomeAway')->willReturn(HomeAway::Away);

        $expected = 'League (A)';
        $this->assertSame($expected, $this->fixtureService->format($fixture));
    }

    public function testFormatWithNothingReturnsTraining()
    {
        $fixture = $this->createMock(Fixture::class);
        $fixture->method('getClub')->willReturn(null);
        $fixture->method('getName')->willReturn(null);
        $fixture->method('getCompetition')->willReturn(Competition::None);
        $fixture->method('getHomeAway')->willReturn(HomeAway::Home);

        $expected = 'Training?';
        $this->assertSame($expected, $this->fixtureService->format($fixture));
    }

    public function testFormatWithCompetitionAndCompDetails()
    {
        $competition = Competition::League;

        $fixture = $this->createMock(Fixture::class);
        $fixture->method('getClub')->willReturn(null);
        $fixture->method('getName')->willReturn(null);
        $fixture->method('getCompetition')->willReturn($competition);
        $fixture->method('getHomeAway')->willReturn(HomeAway::Away);

        $expected = 'League (A) ['.$competition->shortValue().']';
        $this->assertSame($expected, $this->fixtureService->format($fixture, true, true));
    }
}
