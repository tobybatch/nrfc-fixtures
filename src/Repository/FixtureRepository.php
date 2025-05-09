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

    public function getDates(): array
    {
        $results = $this->entityManager->createQueryBuilder()
            ->select('d.date')
            ->from('App\Entity\Fixture', 'd')
            ->getQuery()
            ->getResult();

        $uniqueDates = array_values(array_unique(array_map(
            function ($row) {
                return $row['date']->format('Y-m-d');
            },
            $results
        )));

        sort($uniqueDates); // Optional sorting
        return $uniqueDates;
    }

    public function getFixturesForTeam(Team $team, $date = null): array
    {
        $statement = $this->createQueryBuilder('f')
            ->leftJoin('f.club', 'c')
            ->addSelect('c')
            ->where('f.team = :team')
            ->setParameter('team', $team);

        if ($date) {
            $statement->andWhere('f.date BETWEEN :start AND :end')
                ->setParameter('start', $date . " 00:00:00")
                ->setParameter('end', $date . " 23:59:59");
        }

        return $statement->orderBy('f.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param DateTimeImmutable $startDate
     * @param DateTimeImmutable $endDate
     * @return Fixture[] Returns an array of Fixture objects
     */
    public function findByDateRange(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array
{
    return $this->createQueryBuilder('f')
        ->where('f.date BETWEEN :start AND :end')
        ->setParameter('start', $startDate)
        ->setParameter('end', $endDate)
        ->getQuery()
        ->getResult();
}
}
