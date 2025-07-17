<?php

namespace App\Tests\Unit\Controller;

use App\Entity\Club;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ClubControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->container = static::getContainer();

        // You can fetch a user from DB, or create one dynamically
        $this->user = $this->container->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'editor_user@example.com']);
    }

    public function testIndexPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/club');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1'); // assuming there's a header in the template
    }

    public function testNewClubFormDisplays(): void
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/club/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testCreateNewClub(): void
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/club/new');

        $form = $crawler->selectButton('Save')->form([
            'club[name]' => 'Test FC',
            // add other form fields as necessary
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Test FC'); // confirm it appears somewhere
    }

    public function testShowClub(): void
    {
        $container = static::getContainer();

        $club = new Club();
        $club->setName('Visible Club');
        $em = $container->get('doctrine')->getManager();
        $em->persist($club);
        $em->flush();

        $this->client->request('GET', '/club/' . $club->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Visible Club');
    }

    public function testEditClub(): void
    {
        $container = static::getContainer();
        $this->client->loginUser($this->user);

        $club = new Club();
        $club->setName('Old Name');
        $em = $container->get('doctrine')->getManager();
        $em->persist($club);
        $em->flush();

        $crawler = $this->client->request('GET', '/club/' . $club->getId() . '/edit');

        $form = $crawler->selectButton('Update')->form([
            'club[name]' => 'New Name',
        ]);
        $this->client->submit($form);
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'New Name');
    }
}
