<?php

namespace App\Tests\E2E;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Panther\PantherTestCase;

class ViewFixturesTest extends PantherTestCase
{
    public function testHomepage()
    {
        $client = static::createPantherClient();
        $client->request('GET', '/');

        $this->assertSelectorTextContains('h1', 'Fixtures for 2025/26');
    }

    // Check menu
    public function testMenu()
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/');

        $fixturesLink = $crawler->selectLink("All Youth");
        $this->assertEquals('/?team=youth', $fixturesLink->attr('href'));

        $fixturesLink = $crawler->filter('nav ul li a[href="/club"]');
        $this->assertEquals('Clubs', trim($fixturesLink->text()));
    }

    // Check table headers
    public function testTableHeaders()
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/'); // Adjust URL as needed

        // Target the table headers
        $headers = $crawler->filter('table thead tr th');

        // Expected headers
        $expectedHeaders = ['Date', 'Minis', 'U13B', 'U14B', 'U15B', 'U16B', 'U18B', 'U12G', 'U14G', 'U16G', 'U18G'];

        // Assert the number of headers
        $this->assertCount(count($expectedHeaders), $headers, 'Incorrect number of table headers.');

        // Assert each header's text
        $headers->each(function ($node, $index) use ($expectedHeaders) {
            $headerText = trim($node->text());
            $this->assertEquals(
                $expectedHeaders[$index],
                $headerText,
                "Header at position $index does not match. Expected '{$expectedHeaders[$index]}', got '$headerText'."
            );
        });

        // Optional: Verify specific header links (e.g., for "Minis")
        $minisLink = $crawler->filter('table thead th a[href="/?team=Minis"]');
        $this->assertEquals('Minis', trim($minisLink->text()), 'Minis link text is incorrect.');
        $this->assertTrue($minisLink->isDisplayed(), 'Minis link is not visible.');
    }

    // Data table
    public function testDataTable()
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/'); // Adjust URL as needed

        // Target the table headers
        $rows = $crawler->filter('table tbody tr');

        // Assert the number of headers
        $this->assertCount(35, $rows, 'Incorrect number of table rows.');
    }

    public function testViewSingleTeam(){
        $client = static::createPantherClient();
        $client->request('GET', '/'); // Adjust URL as needed

        $client->ClickLink("Minis");
        $crawler = $client->waitForElementToContain('h1', "Minis Fixtures for 2025/26");
        $this->assertEquals('team=Minis', parse_url($client->getCurrentURL(), PHP_URL_QUERY));

        $headers = $crawler->filter('table thead tr th');
        $expectedHeaders = ['Date', 'Minis'];
        $this->assertCount(count($expectedHeaders), $headers, 'Incorrect number of table headers.');

        $client->clickLink("Fixtures");
        $this->assertSelectorTextContains('h1', 'Fixtures for 2025/26');
    }

    public function testViewSingleDate() {
        $client = static::createPantherClient();
        $links = $client->request('GET', '/')->filter('.nrfc-date-link');
        $lastLink = $links->last();
        $lastLink->click();

        $crawler = $client->waitForElementToContain('body', "Home Fixtures");
        $gridItems = $crawler->filter('.nrfc-by-date-items');
        $this->assertCount(4, $gridItems->children(), 'Incorrect number of grid items.');
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testViewSingleFixture() {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/');

        $crawler->selectLink('(A)')->click();
        $crawler = $client->waitForElementToContain('body', "Back to list");

        $headers = $crawler->filter('table thead tr th');
        $this->assertCount(1, $headers, 'Incorrect number of table headers.');

        $rows = $crawler->filter('table tbody tr');
        $this->assertCount(6, $rows, 'Incorrect number of table rows.');

        $headers = $crawler->filter('table tbody tr th');
        $headers->each(function ($node, $index) {
            $text = match ($index) {
                1 => 'Kick off',
                2 => 'Venue',
                3 => 'Team',
                4 => 'Competition',
                default => null,
            };
            if ($text) {
                $this->assertEquals($text, $node->text());
            }
        });

        $client->clickLink("Back to list");
        $this->assertSelectorTextContains('h1', 'Fixtures for');
        $this->assertEquals('/', parse_url($client->getCurrentURL(), PHP_URL_PATH));
    }
}