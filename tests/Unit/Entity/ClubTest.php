<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Club;
use App\Entity\Fixture;
use PHPUnit\Framework\TestCase;

class ClubTest extends TestCase
{
    public function testNameProperty(): void
    {
        $club = new Club();
        $club->setName('Norwich RFC');
        $this->assertSame('Norwich RFC', $club->getName());
    }

    public function testAddressProperty(): void
    {
        $club = new Club();
        $club->setAddress('123 Rugby Lane');
        $this->assertSame('123 Rugby Lane', $club->getAddress());
    }

    public function testLatitudeProperty(): void
    {
        $club = new Club();
        $club->setLatitude(52.6309);
        $this->assertSame(52.6309, $club->getLatitude());
    }

    public function testLongitudeProperty(): void
    {
        $club = new Club();
        $club->setLongitude(1.2974);
        $this->assertSame(1.2974, $club->getLongitude());
    }

    public function testNotesProperty(): void
    {
        $club = new Club();
        $club->setNotes('Home ground near the river.');
        $this->assertSame('Home ground near the river.', $club->getNotes());
    }

    public function testToStringMethod(): void
    {
        $club = new Club();
        $this->assertSame('???', (string) $club);

        $club->setName('Norwich RFC');
        $this->assertSame('Norwich RFC', (string) $club);
    }

    public function testAddAndRemoveFixture(): void
    {
        $club = new Club();
        $fixture = new Fixture();

        $this->assertCount(0, $club->getFixtures());

        $club->addFixture($fixture);
        $this->assertCount(1, $club->getFixtures());
        $this->assertTrue($club->getFixtures()->contains($fixture));
        $this->assertSame($club, $fixture->getClub());

        $club->removeFixture($fixture);
        $this->assertCount(0, $club->getFixtures());
        $this->assertNull($fixture->getClub());
    }
}
