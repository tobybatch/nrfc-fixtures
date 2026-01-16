<?php

namespace App\Repository;

use App\Config\Team;
use App\Entity\Fixture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fixture>
 *
 * @method findByTeam(Team $team)
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
     * @param array<Team> $teams
     *
     * @return \DateTimeImmutable[]
     * @throws \DateMalformedStringException
     */
    public function getDates(array $teams): array
    {
        $results = $this->entityManager->createQueryBuilder()
            ->select('DISTINCT d.date AS date')
            ->from('App\Entity\Fixture', 'd')
            ->where('d.team IN (:teams)')
            ->orderBy('d.date', 'ASC')
            ->setParameter('teams', $teams)
            ->getQuery()
            ->getScalarResult()
        ;

        $dates = [];
        foreach ($results as $row) {
            try {
                if (!in_array($row['date'], $dates)) {
                    $dates[] = $row['date'];
                }
            } catch (\Exception $e) {
                // Optionally log the error or skip silently
                // error_log('Invalid date format: ' . $row['date']);
                continue;
            }
        }

        return array_map(fn ($date) => new \DateTimeImmutable($date), $dates);
    }

    /**
     * @param array<Team> $teams
     *
     * @return array<string, array{Fixture}>
     */
    public function getFixturesForTeams(array $teams, ?\DateTimeImmutable $date = null): array
    {
        $fixtures = [];

        foreach ($teams as $team) {
            $fixtures[$team->value] = $this->getFixturesForTeam($team, $date);
        }

        return $fixtures;
    }

    /**
     * @return array{Fixture}
     */
    public function getFixturesForTeam(Team $team, ?\DateTimeImmutable $date = null): array
    {
        $statement = $this->createQueryBuilder('f')
            ->leftJoin('f.club', 'c')
            ->addSelect('c')
            ->where('f.team = :team')
            ->setParameter('team', $team);

        if ($date) {
            $start = $date->setTime(0, 0);
            $end = $date->setTime(23, 59, 59);
            $statement->andWhere('f.date BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        return $statement->orderBy('f.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Fixture[] Returns an array of Fixture objects
     */
    public function findByDateRange(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
    ): array {
        return $this->createQueryBuilder('f')
            ->where('f.date BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getResult();
    }
}
