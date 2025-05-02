<?php

namespace App\Tests\Repository;

use App\Config\Team;
use App\Entity\Fixture;
use App\Repository\FixtureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class FixtureRepositoryTest extends TestCase
{
    private FixtureRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $managerRegistry = $this->createMock(ManagerRegistry::class);

        $managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(Fixture::class)
            ->willReturn($this->entityManager);

        $this->entityManager->expects($this->once())
            ->method('getClassMetadata')
            ->with(Fixture::class)
            ->willReturn(new ClassMetadata(Fixture::class));

        $this->repository = new FixtureRepository($managerRegistry, $this->entityManager);
    }

    public function testGetDates(): void
    {
        $query = $this->createMock(\Doctrine\ORM\AbstractQuery::class);
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('DISTINCT f.date')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('from')
            ->with(Fixture::class, 'f')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('f.date', 'ASC')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn(['2023-01-01', '2023-01-02']);

        $dates = $this->repository->getDates();
        $this->assertEquals(['2023-01-01', '2023-01-02'], $dates);
    }

    public function testGetFixturesForTeam(): void
    {
        $query = $this->createMock(\Doctrine\ORM\AbstractQuery::class);
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('f')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('from')
            ->with(Fixture::class, 'f')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('f.team = :team')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('team', Team::U13B)
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('f.date', 'ASC')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([new Fixture()]);

        $fixtures = $this->repository->getFixturesForTeam(Team::U13B);
        $this->assertCount(1, $fixtures);
    }

    public function testFindByDateRange(): void
    {
        $query = $this->createMock(\Doctrine\ORM\AbstractQuery::class);
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('f')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('from')
            ->with(Fixture::class, 'f')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('f.date BETWEEN :start AND :end')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(
                ['start', '2023-01-01'],
                ['end', '2023-01-31']
            )
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('f.date', 'ASC')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([new Fixture()]);

        $fixtures = $this->repository->findByDateRange('2023-01-01', '2023-01-31');
        $this->assertCount(1, $fixtures);
    }
} 