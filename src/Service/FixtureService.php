<?php

namespace App\Service;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Fixture as FixtureEntity;
use App\Repository\ClubRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class FixtureService
{
    private ObjectManager $em;
    private ClubRepository $clubRepository;

    public function __construct(
        EntityManagerInterface $em,
        ClubRepository $clubRepository
    )
    {
        $this->em = $em;
        $this->clubRepository = $clubRepository;
    }
    public function createFixture(Team $team, DateTimeImmutable $date, mixed $detail): void
    {
        $fixture = new FixtureEntity();
        list($sessionName, $comp, $home, $club) = self::makeFixtureFromString($detail);
        $fixture->setTeam($team);
        if ($club) {
            $fixture->setClub($club);
        } else {
            $fixture->setName($sessionName);
        }
        $fixture->setCompetition($comp);
        $fixture->setHomeAway($home);
        $fixture->setDate($date);
        $fixture->setTeam($team);

        $this->em->persist($fixture);
        $this->em->flush();
    }

    public function makeFixtureFromString(string $detail): array
    {
        if (in_array(strtolower(trim($detail)), ['training', 'skills session'])) {
            return ["Training", Competition::Training, HomeAway::Home, null];
        }

        // is CB or Pathway
        if (
            str_starts_with(trim($detail), "CB")
            || str_contains(strtolower(trim($detail)), "pathway")
            || str_contains(strtolower(trim($detail)), "academy")
        ) {
            return [ucwords($detail), Competition::Pathway, HomeAway::TBA, null];
        }
        // is county cup / colts cup
        if (
            str_starts_with(strtolower(trim($detail)), "county cup")
            || str_contains(strtolower(trim($detail)), "colts cup")
            || str_contains(strtolower(trim($detail)), "norfolk finals")
        ) {
            return [ucwords($detail), Competition::CountyCup, HomeAway::TBA, null];
        }
        // is festival
        if (str_contains(strtolower(trim($detail)), "festival")) {
            return [ucwords($detail), Competition::Festival, HomeAway::TBA, null];
        }
        // is nat cup
        if (str_contains(strtolower(trim($detail)), "nat cup")) {
            return [ucwords($detail), Competition::NationalCup, HomeAway::TBA, null];
        }
        // is norfolk 10s
        if (str_contains(trim($detail), "Norfolk10s")) {
            return [$detail, Competition::Norfolk10s, HomeAway::TBA, null];
        }
        // is Conference
        if (str_contains(strtolower(trim($detail)), "conference")) {
            return [ucwords($detail), Competition::Conference, HomeAway::TBA, null];
        }
        // is special day
        if (in_array(strtolower(trim($detail)), ["mothering sunday", "christmas", "easter", "out of season"])) {
            return [ucwords($detail), Competition::None, HomeAway::TBA, null];
        }

        // we've got this far, we think it's a club game
        $club = $this->clubRepository->fuzzyFind(
            preg_replace('/\s*\([^)]*\)/', '', $detail)
        );
        return [
            ucwords($detail),
            Competition::Friendly,
            HomeAway::isHomeOrAway($detail),
            $club
        ];
    }
}