<?php

namespace App\Tests\E2E;

use Symfony\Component\Panther\PantherTestCase;

class ShowHideHelpTest extends PantherTestCase
{
    public function testHelpTextHidesOnButtonClick(): void
    {
        // Start the Panther client
        $client = static::createPantherClient();

        // Clear the session cookie to start with a fresh session
        $client->manage()->deleteAllCookies();

        // Visit the page with the help text block
        $crawler = $client->request('GET', '/');
        $helpContainer = $crawler->filter('div[data-helptoggle-target="content"]');
        $this->assertTrue($helpContainer->isDisplayed(), 'Help text container should be visible initially.');

        // Hide
        $hideButton = $crawler->filter('button[data-helptoggle-target="hideButton"]');
        $hideButton->click();
        $client->waitForInvisibility('div[data-helptoggle-target="content"]', 5);
        $this->assertFalse($helpContainer->isDisplayed(), 'Help text container should be hidden after clicking the hide button.');

        // Now reload the page and see the help hidden
        $crawler = $client->request('GET', '/');
        $helpContainer = $crawler->filter('div[data-helptoggle-target="content"]');
        $this->assertFalse($helpContainer->isDisplayed(), 'Help text container should be hidden after reloading.');

        // click help and see it shown
        $client->waitForVisibility('button[data-helptoggle-target="showButton"]', 5);
        $button = $crawler->filter('button[data-helptoggle-target="showButton"]');
        $button->click();
        $client->waitForVisibility('div[data-helptoggle-target="content"]', 5);
        $this->assertTrue($helpContainer->isDisplayed(), 'Help text container should be visible after clicking the help link.');

        // reload the page and see the
        $crawler = $client->request('GET', '/');
        $helpContainer = $crawler->filter('div[data-helptoggle-target="content"]');
        $this->assertTrue($helpContainer->isDisplayed(), 'Help text container should be visible initially.');
    }

}