<?php

// src/Repository/ClubRepository.php

namespace App\Repository;

use App\Config\Team;
use App\Entity\Club;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Club>
 */
class ClubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Club::class);
    }

    /**
     * @return Club[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('LOWER(c.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByNameInsensitive(string $name): ?Club
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.name) = :name')
            ->setParameter('name', mb_strtolower($name))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByNameStartingWith(string $searchTerm): ?Club
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.name) LIKE LOWER(:searchTerm)')
            ->setParameter('searchTerm', $searchTerm.'%')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
