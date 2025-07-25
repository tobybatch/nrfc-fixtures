<?php

namespace App\Tests\Unit\Controller;

use App\Entity\Club;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClubControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->container = static::getContainer();
        $this->hasher = $this->container->get('security.password_hasher');
        $this->em = $this->container->get('doctrine')->getManager();

        $this->user = new User();
        $this->user->setEmail('editor_user@example.com');
        $this->user->setRoles(['ROLE_EDITOR']);
        $this->user->setPassword($this->hasher->hashPassword($this->user, 'testpass'));

        $this->em->persist($this->user);
        $this->em->flush();
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

        $this->client->request('GET', '/club/'.$club->getId());

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

        $crawler = $this->client->request('GET', '/club/'.$club->getId().'/edit');

        $form = $crawler->selectButton('Update')->form([
            'club[name]' => 'New Name',
        ]);
        $this->client->submit($form);
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'New Name');
    }
}
