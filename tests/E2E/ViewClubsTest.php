<?php

namespace App\Tests\E2E;

use App\Entity\Club;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Panther\PantherTestCase;

class ViewClubsTest extends PantherTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?ClubRepository $clubRepository;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->clubRepository = self::getContainer()->get(ClubRepository::class);
        $this->logger = self::getContainer()->get(LoggerInterface::class);
    }

    /**
     * @throws \Exception
     */
    public function testShowAllClubs()
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/club');

        // Title
        $this->assertSelectorTextContains('h1', 'Clubs');

        // Table headers
        $headers = $crawler->filter('table thead tr th');
        $expectedHeaders = ['Name', 'Address'];
        $this->assertCount(count($expectedHeaders), $headers, 'Incorrect number of table headers.');
        $headers->each(function ($node, $index) use ($expectedHeaders) {
            $headerText = trim($node->text());
            $this->assertEquals(
                $expectedHeaders[$index],
                $headerText,
                "Header at position $index does not match. Expected '{$expectedHeaders[$index]}', got '$headerText'."
            );
        });

        // Table rows
        $rows = $crawler->filter('table tbody tr');
        $rawClubs = file_get_contents(__DIR__ . '/../../assets/clubs.csv');
        if (!$rawClubs) {
            throw new \Exception('Unable to read clubs.json');
        }
        $clubs = explode("\n", $rawClubs);
        $clubCount = count($clubs) - 1; // header row
        $this->assertCount($clubCount, $rows, 'Incorrect number of clubs.');

        $text = $rows->first()->filter('td')->first()->text();
        $rows->first()->filter('a')->click();
        $client->waitForElementToContain('body', $text);
        $this->assertStringStartsWith('/club', parse_url($client->getCurrentURL(), PHP_URL_PATH));
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testShowClub(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/club');

        $client->ClickLink("Beccles");

        $crawler = $client->waitForElementToContain('h2', "Beccles");

        $this->assertStringStartsWith(
            "Beef Meadow",
            $crawler->filter('.nrfc-address')->text(),
            'Club name should be displayed on the club page.'
        );
        // TODO - test notes, this is tested by the CRUD tests
//        $this->assertEquals(
//            "Some notes",
//            $crawler->filter('.nrfc-club-notes')->text(),
//            'Club notes should displayed on the club page.'
//        );
        $this->assertNotNull(
            $crawler->filter('.leaflet-container')->text(),
            'Club map should displayed on the club page.'
        );

        $client->clickLink("Back to list");
        $client->waitForElementToContain('h1', 'Clubs');
        $this->assertSelectorTextContains('h1', 'Clubs');
        $this->assertEquals('/club', parse_url($client->getCurrentURL(), PHP_URL_PATH));

    }
}