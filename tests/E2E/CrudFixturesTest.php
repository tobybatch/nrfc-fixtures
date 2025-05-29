<?php

namespace App\Tests\E2E;

use App\DataFixtures\Users;
use App\Entity\Club;
use DateTime;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class CrudFixturesTest extends PantherTestCase
{
    protected Client $client;
    public function setUp(): void
    {
        parent::setUp();
        // Create client once
        $this->client = static::createPantherClient();
        $this->client->request('GET', '/user/login');
        $this->client->submitForm('Login', [
            '_username' => Users::EDITOR_USER,
            '_password' => Users::PASSWORD,
        ]);
        $this->client->waitForElementToContain('body', 'Fixtures for 2025/26');
    }

    protected function tearDown(): void
    {
        $this->client->manage()->deleteAllCookies();
        $this->client->executeScript('window.localStorage.clear();');
        $this->client->executeScript('window.sessionStorage.clear();');
    }

    // create new fixture - happy path
    public function testCreateNewFixture(): void
    {
        $this->client->request('GET', '/');
        $this->client->waitForElementToContain('body', 'Create New');
        $this->client->clickLink('Create New');

        $date = (new DateTime())->modify('+3 months');
        $dateIn = $date->format('d-m-Y');
        $dateOut = $date->format('j M y');

        $homeAway = 'A';
        $competition = 'CountyCup';
        $team = 'U16B';
        $club = $this->randomClub();
        $notes = 'I\'m a shade tree mechanic, got a one-ton truck';
        $fixtureAsText = sprintf("%s (%s)", $club->getName(), $homeAway);

        $this->client->submitForm('Save', [
            'fixture[date]' => $dateIn,
            'fixture[homeAway]' => $homeAway,
            'fixture[competition]' => $competition,
            'fixture[team]' => $team,
            'fixture[club]' => $club->getId(),
            'fixture[notes]' => $notes,
        ]);

        $crawler = $this->client->waitForElementToContain('body', 'Fixtures for 2025/26');

        // check that the fixture is created
        $this->assertStringContainsString($dateOut, $crawler->text());
        $this->assertStringContainsString($fixtureAsText, $crawler->text());
    }

    // edit new fixture - happy path
    // delete new fixture - happy path

    private function randomClub(): Club
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $repository = $entityManager->getRepository(Club::class);
        $all = $repository->findAll();
        return $all[array_rand($all)];
    }
}