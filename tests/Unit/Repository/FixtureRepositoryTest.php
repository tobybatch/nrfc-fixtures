<?php

namespace App\Tests\Unit\Repository;

use App\Config\Team;
use App\Entity\Fixture;
use App\Repository\FixtureRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class FixtureRepositoryTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private ManagerRegistry $registry;
    private FixtureRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);

        $this->repository = new FixtureRepository($this->registry, $this->entityManager);
    }

    public function testGetDates(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class); // ✅ Use Query instead of AbstractQuery

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())->method('select')->with('d.date')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('from')->with('App\Entity\Fixture', 'd')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('getQuery')->willReturn($query);

        $query->expects($this->once())->method('getResult')->willReturn([
            ['date' => new DateTimeImmutable('2024-05-01')],
            ['date' => new DateTimeImmutable('2024-05-02')],
            ['date' => new DateTimeImmutable('2024-05-01')], // duplicate
        ]);

        $dates = $this->repository->getDates();

        $this->assertEquals(['2024-05-01', '2024-05-02'], $dates);
    }

    public function testGetFixturesForTeam(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        $team = Team::Minis;
        $fixture = $this->createMock(Fixture::class);

        $this->registry->method('getManagerForClass')->willReturn($this->entityManager);

        $fixtureRepo = $this->getMockBuilder(FixtureRepository::class)
            ->setConstructorArgs([$this->registry, $this->entityManager])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $fixtureRepo->method('createQueryBuilder')->willReturn($queryBuilder);

        $queryBuilder->method('leftJoin')->willReturnSelf();
        $queryBuilder->method('addSelect')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $query->method('getResult')->willReturn([$fixture]);

        $results = $fixtureRepo->getFixturesForTeam($team, '2024-05-01');

        $this->assertCount(1, $results);
        $this->assertSame($fixture, $results[0]);
    }

    public function testFindByDateRange(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        $fixture = $this->createMock(Fixture::class);

        $fixtureRepo = $this->getMockBuilder(FixtureRepository::class)
            ->setConstructorArgs([$this->registry, $this->entityManager])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $fixtureRepo->method('createQueryBuilder')->willReturn($queryBuilder);

        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $query->method('getResult')->willReturn([$fixture]);

        $start = new DateTimeImmutable('2024-05-01');
        $end = new DateTimeImmutable('2024-05-31');

        $results = $fixtureRepo->findByDateRange($start, $end);

        $this->assertCount(1, $results);
        $this->assertSame($fixture, $results[0]);
    }
}
