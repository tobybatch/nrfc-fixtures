<?php

namespace App\Entity;

use App\Repository\FixtureRepository;
use Doctrine\ORM\Mapping as ORM;
use \App\Config\HomeAway;
use \App\Config\Competition;
use \App\Config\Team;

#[ORM\Entity(repositoryClass: FixtureRepository::class)]
class Fixture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[ORM\OrderBy(['date' => 'DESC'])]
    private ?\DateTimeImmutable $date = null;

    #[ORM\ManyToOne(inversedBy: 'fixtures')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Club $club = null;

    #[ORM\Column(enumType: HomeAway::class)]
    private HomeAway $homeAway;

    #[ORM\Column(enumType: Competition::class)]
    private Competition $competition;

    #[ORM\Column(enumType: Team::class)]
    private ?Team $team = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        $this->club = $club;

        return $this;
    }


    public function getHomeAway(): ?HomeAway
    {
        return $this->homeAway;
    }

    public function setHomeAway(HomeAway $homeAway): static
    {
        $this->homeAway = $homeAway;

        return $this;
    }

    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    public function setCompetition(?Competition $competition): static
    {
        $this->competition = $competition;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
