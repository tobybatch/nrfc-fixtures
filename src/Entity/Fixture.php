<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Repository\FixtureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[ORM\Entity(repositoryClass: FixtureRepository::class)]
class Fixture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
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
    private Team $team;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(nullable: true, enumType: Team::class)]
    private ?Team $opponent = null;

    #[ORM\Column(nullable: true)]
    private ?string $matchReportExternalId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(['fixture:read'])]
    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    #[Groups(['fixture:read'])]
    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        $this->club = $club;

        return $this;
    }

    #[Groups(['fixture:read'])]
    public function getHomeAway(): HomeAway
    {
        return $this->homeAway;
    }

    public function setHomeAway(HomeAway $homeAway): static
    {
        $this->homeAway = $homeAway;

        return $this;
    }

    #[Groups(['fixture:read'])]
    public function getCompetition(): Competition
    {
        return $this->competition;
    }

    public function setCompetition(Competition $competition): static
    {
        $this->competition = $competition;

        return $this;
    }

    #[Groups(['fixture:read'])]
    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    #[Groups(['fixture:read'])]
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    #[Groups(['fixture:read'])]
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    #[Groups(['fixture:read'])]
    public function getOpponent(): ?Team
    {
        return $this->opponent;
    }

    public function setOpponent(?Team $opponent): static
    {
        $this->opponent = $opponent;

        return $this;
    }

    #[Groups(['fixture:read'])]
    public function getMatchReportExternalId(): ?string
    {
        return $this->matchReportExternalId;
    }

    public function setMatchReportExternalId(?string $matchReportExternalId): static
    {
        $this->matchReportExternalId = $matchReportExternalId;

        return $this;
    }

    #[Groups(['fixture:read'])]
    public function getClubName(): ?string
    {
        return $this->club?->getName();
    }

    public function __toString(): string
    {
        return sprintf(
            'Fixture #%d: %s vs %s on %s',
            $this->id ?? 0,
            $this->team->name ?? 'Unknown',
            $this->opponent?->name ?? 'Unknown',
            $this->date?->format('Y-m-d H:i') ?? 'Unknown Date'
        );
    }
}
