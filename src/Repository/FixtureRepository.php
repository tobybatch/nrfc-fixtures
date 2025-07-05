<?php

namespace App\Repository;

use App\Config\Team;
use App\Entity\Fixture;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fixture>
 */
class FixtureRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Fixture::class);
        $this->entityManager = $entityManager;
    }

    /**
     * @return DateTimeImmutable[]
     */
    public function getDates(): array
    {
        $results = $this->entityManager->createQueryBuilder()
            ->select('d.date')
            ->from('App\Entity\Fixture', 'd')
            ->getQuery()
            ->getResult();

        $dates = array_values(array_unique(array_map(
            function ($row) {
                return $row['date'];
            },
            $results
        )));

        sort($dates);

        return $dates;
    }

    /**
     * @param array<Team> $teams
     * @param DateTimeImmutable|null $date
     * @return array<string, array{Fixture}>
     */
    public function getFixturesForTeams(array $teams, DateTimeImmutable $date = null): array {
        $fixtures = [];

        foreach ($teams as $team) {
            $fixtures[$team->value] = $this->$this->getFixturesForTeam($team, $date);
        }

        return $fixtures;
    }


    /**
     * @param Team $team
     * @param DateTimeImmutable|null $date
     * @return array{Fixture}
     */
    public function getFixturesForTeam(Team $team, DateTimeImmutable $date = null): array
    {
        $statement = $this->createQueryBuilder('f')
            ->leftJoin('f.club', 'c')
            ->addSelect('c')
            ->where('f.team = :team')
            ->setParameter('team', $team);

        if ($date) {
            $statement->andWhere('f.date BETWEEN :start AND :end')
                ->setParameter('start', $date)
                ->setParameter('end', $date);
        }

        return $statement->orderBy('f.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Fixture[] Returns an array of Fixture objects
     */
    public function findByDateRange(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
    ): array {
        return $this->createQueryBuilder('f')
            ->where('f.date BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getResult();
    }
}
