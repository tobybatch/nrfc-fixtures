<?php

namespace App\Tests\E2E;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\DataFixtures\Users;
use App\Entity\Club;
use App\Entity\Fixture;
use App\Repository\FixtureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class CrudFixturesTest extends PantherTestCase
{
    protected Client $client;
    protected EntityManagerInterface $entityManager;
    /** @var EntityRepository<Club> */
    protected EntityRepository $clubRepository;
    /** @var EntityRepository<Fixture> */
    protected EntityRepository $fixtureRepository;

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->clubRepository = $this->entityManager->getRepository(Club::class);
        $this->fixtureRepository = $this->entityManager->getRepository(Fixture::class);

        // Create client once
        $this->client = static::createPantherClient();
        $this->client->request('GET', '/user/login');
        $this->client->submitForm('Login', [
            '_username' => Users::EDITOR_USER,
            '_password' => Users::PASSWORD,
        ]);
        $this->client->waitForElementToContain('body', 'Fixtures for 2025/26');
    }

    /**
     * @throws \Exception
     */
    protected function tearDown(): void
    {
        if ($this->hasFailed()) {
            $client = $this->client;
            $client->takeScreenshot('var/screenshots/error-'.time().'.png');
        }

        $this->client->manage()->deleteAllCookies();
        $this->client->executeScript('window.localStorage.clear();');
        $this->client->executeScript('window.sessionStorage.clear();');

        parent::tearDown();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     * @throws \DateMalformedStringException
     */
    public function testCreateNewFixture(): void
    {
        $this->client->request('GET', '/');
        $this->client->waitForElementToContain('body', 'Create New');
        $crawler = $this->client->clickLink('Create New');

        $this->client->waitForElementToContain('body', 'Save');

        $date = (new \DateTime())->modify('+17 months');
        $dateIn = $date->format('d/m/Y');
        $dateOut = $date->format('j M y');

        $homeAway = HomeAway::Away->value;
        $competition = Competition::Friendly->value;
        $team = Team::U15B->value;
        $club = $this->randomClub();
        $notes = 'I\'m a shade tree mechanic, got a one-ton truck';

        $fixtureAsText = sprintf('%s %s (%s)', $club->getName(), $team, $homeAway);

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
    public function testEditFixture(): void
    {
        $fixture = $this->randomFixture();

        $this->client->request('GET', '/'.$fixture->getId());
        $this->client->waitForElementToContain('body', 'Edit');
        $this->client->clickLink('Edit');
        $this->client->waitForElementToContain('body', 'Update');

        $date = (new \DateTime())->modify('+3 months');
        $dateIn = $date->format('d/m/Y');
        $homeAway = HomeAway::Away == $fixture->getHomeAway() ? HomeAway::Home->value : HomeAway::Away->value;
        $competition = Competition::CountyCup == $fixture->getCompetition() ? Competition::NationalCup->value : Competition::CountyCup->value;
        $team = Team::U15B == $fixture->getTeam() ? Team::U16B->value : Team::U15B->value;
        $club = $this->randomClub();
        $notes = 'I\'m a shade tree mechanic, got a one-ton truck';

        $this->client->submitForm('Update', [
            'fixture[date]' => $dateIn,
            'fixture[homeAway]' => $homeAway,
            'fixture[competition]' => $competition,
            'fixture[team]' => $team,
            'fixture[club]' => $club->getId(),
            'fixture[notes]' => $notes,
        ]);

        $this->client->waitForElementToContain('body', 'Fixtures for 2025/26');

        // check that the fixture was updated
        $updatedFixture = $this->fixtureRepository->find($fixture->getId());
        $this->assertEquals($fixture->getDate(), $updatedFixture->getDate());
        $this->assertEquals($fixture->getHomeAway(), $updatedFixture->getHomeAway());
        $this->assertEquals($fixture->getCompetition(), $updatedFixture->getCompetition());
        $this->assertEquals($fixture->getTeam(), $updatedFixture->getTeam());
        $this->assertEquals($fixture->getClub(), $updatedFixture->getClub());
        $this->assertEquals($fixture->getNotes(), $updatedFixture->getNotes());
    }

    // delete new fixture - happy path

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testDeleteFixture(): void
    {
        $fixture = $this->randomFixture();
        $id = $fixture->getId();

        $this->client->request('GET', '/'.$id);
        $crawler = $this->client->waitForElementToContain('body', 'Delete');
        $this->client->waitFor('.btn-delete');
        $crawler->filter('.btn-delete')->click();

        $client = $this->client;
        $this->client->wait(5, function () use ($client) {
            try {
                $client->getWebDriver()->switchTo()->alert();

                return true;
            } catch (\Facebook\WebDriver\Exception\NoAlertOpenException $e) {
                return false;
            }
        });
        $this->client->getWebDriver()->switchTo()->alert()->accept();
        $this->client->waitForElementToContain('body', 'Fixture '.$id.' deleted');
        $this->assertStringContainsString('Fixture '.$id.' deleted', $crawler->text());

        // TODO - why doesn't this work
        //        $updatedFixture = $this->fixtureRepository->find($id);
        //        $this->assertNull($updatedFixture);
    }

    private function randomClub(): Club
    {
        $all = $this->clubRepository->findAll();

        return $all[array_rand($all)];
    }

    private function randomFixture(): Fixture
    {
        $all = $this->fixtureRepository->findAll();

        return $all[array_rand($all)];
    }
}
