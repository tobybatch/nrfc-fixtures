<?php

namespace App\Repository;

use App\Config\Team;
use App\Entity\Fixture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fixture>
 */
class FixtureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fixture::class);
    }

    public function getFixturesForTeam(Team $team): array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.club', 'c')
            ->addSelect('c')
            ->where('f.team = :team')
            ->setParameter('team', $team)
            ->orderBy('f.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Fixture[] Returns an array of Fixture objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Fixture
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
