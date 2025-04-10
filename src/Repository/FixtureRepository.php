<?php

namespace App\Repository;

use App\Config\Team;
use App\Entity\Fixture;
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
            function($row) {
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
            $statement = $statement->andWhere('f.date = :date')
            ->setParameter(':date', $date . " 12:00:00");
        }

        return $statement->orderBy('f.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
