<?php

namespace App\Tests\E2E;

use Symfony\Component\Panther\PantherTestCase;

class FixtureIndexTest extends PantherTestCase
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

        $fixturesLink = $crawler->filter('nav ul li a[href="/"]');
        $this->assertEquals('Fixtures', trim($fixturesLink->text()));

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
        $this->assertCount(36, $rows, 'Incorrect number of table rows.');
    }
}