<?php

namespace App\Tests\UNit\Repository;

use App\Entity\Club;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClubRepositoryTest extends TestCase
{
    private ClubRepository $clubRepository;
    private MockObject $managerRegistry;
    private MockObject $entityManager;
    private MockObject $queryBuilder;
    private MockObject $query;

    protected function setUp(): void
    {
        // Mock the ManagerRegistry
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);

        // Mock the EntityManager
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Mock the QueryBuilder
        $this->queryBuilder = $this->createMock(QueryBuilder::class);

        // Mock the Query (use Doctrine\ORM\Query instead of AbstractQuery)
        $this->query = $this->createMock(Query::class);

        // Set up the ManagerRegistry to return the EntityManager
        $this->managerRegistry->method('getManagerForClass')
            ->with(Club::class)
            ->willReturn($this->entityManager);

        // Create a mock ClassMetadata
        $classMetadata = new ClassMetadata(Club::class);

        // Set up the EntityManager to return the ClassMetadata
        $this->entityManager->method('getClassMetadata')
            ->with(Club::class)
            ->willReturn($classMetadata);

        // Set up the EntityManager to return the QueryBuilder
        $this->entityManager->method('createQueryBuilder')
            ->willReturn($this->queryBuilder);

        // Create the ClubRepository instance
        $this->clubRepository = new ClubRepository($this->managerRegistry);
    }

    public function testFindAll(): void
    {
        // Mock a sample result
        $clubs = [
            new Club(),
            new Club(),
        ];

        // Set up QueryBuilder expectations
        $this->queryBuilder->method('select')
            ->with('c')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->method('from')
            ->with(Club::class, 'c')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->method('orderBy')
            ->with('c.name', 'ASC')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->method('getQuery')
            ->willReturn($this->query);

        // Set up Query to return the result
        $this->query->method('getResult')
            ->willReturn($clubs);

        // Call the method and assert the result
        $result = $this->clubRepository->findAll();

        $this->assertSame($clubs, $result);
        $this->assertCount(2, $result);
    }
}
