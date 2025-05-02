<?php

namespace App\Tests\Entity;

use App\Entity\Club;
use App\Entity\Fixture;
use PHPUnit\Framework\TestCase;

class ClubTest extends TestCase
{
    private Club $club;

    protected function setUp(): void
    {
        $this->club = new Club();
    }

    public function testGetId(): void
    {
        $this->assertNull($this->club->getId());
    }

    public function testGetName(): void
    {
        $this->assertNull($this->club->getName());
    }

    public function testSetName(): void
    {
        $name = 'Test Club';
        $this->club->setName($name);
        $this->assertEquals($name, $this->club->getName());
    }

    public function testGetAddress(): void
    {
        $this->assertNull($this->club->getAddress());
    }

    public function testSetAddress(): void
    {
        $address = '123 Test Street';
        $this->club->setAddress($address);
        $this->assertEquals($address, $this->club->getAddress());
    }

    public function testGetPostcode(): void
    {
        $this->assertNull($this->club->getPostcode());
    }

    public function testSetPostcode(): void
    {
        $postcode = 'AB12 3CD';
        $this->club->setPostcode($postcode);
        $this->assertEquals($postcode, $this->club->getPostcode());
    }

    public function testGetLatitude(): void
    {
        $this->assertNull($this->club->getLatitude());
    }

    public function testSetLatitude(): void
    {
        $latitude = 51.5074;
        $this->club->setLatitude($latitude);
        $this->assertEquals($latitude, $this->club->getLatitude());
    }

    public function testGetLongitude(): void
    {
        $this->assertNull($this->club->getLongitude());
    }

    public function testSetLongitude(): void
    {
        $longitude = -0.1278;
        $this->club->setLongitude($longitude);
        $this->assertEquals($longitude, $this->club->getLongitude());
    }

    public function testGetFixtures(): void
    {
        $this->assertCount(0, $this->club->getFixtures());
    }

    public function testAddFixture(): void
    {
        $fixture = new Fixture();
        $this->club->addFixture($fixture);
        $this->assertCount(1, $this->club->getFixtures());
        $this->assertSame($this->club, $fixture->getClub());
    }

    public function testRemoveFixture(): void
    {
        $fixture = new Fixture();
        $this->club->addFixture($fixture);
        $this->club->removeFixture($fixture);
        $this->assertCount(0, $this->club->getFixtures());
        $this->assertNull($fixture->getClub());
    }

    public function testToString(): void
    {
        $this->club->setName('Test Club');
        $this->assertEquals('Test Club', (string)$this->club);
    }
} 