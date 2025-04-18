<?php

namespace App\Tests\Controller;

use App\Entity\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FixtureControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $fixtureRepository;
    private string $path = '/fixture/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->fixtureRepository = $this->manager->getRepository(Fixture::class);

        foreach ($this->fixtureRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Fixture index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'fixture[date]' => 'Testing',
            'fixture[homeAway]' => 'Testing',
            'fixture[competition]' => 'Testing',
            'fixture[team]' => 'Testing',
            'fixture[name]' => 'Testing',
            'fixture[club]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->fixtureRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Fixture();
        $fixture->setDate('My Title');
        $fixture->setHomeAway('My Title');
        $fixture->setCompetition('My Title');
        $fixture->setTeam('My Title');
        $fixture->setName('My Title');
        $fixture->setClub('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Fixture');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Fixture();
        $fixture->setDate('Value');
        $fixture->setHomeAway('Value');
        $fixture->setCompetition('Value');
        $fixture->setTeam('Value');
        $fixture->setName('Value');
        $fixture->setClub('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'fixture[date]' => 'Something New',
            'fixture[homeAway]' => 'Something New',
            'fixture[competition]' => 'Something New',
            'fixture[team]' => 'Something New',
            'fixture[name]' => 'Something New',
            'fixture[club]' => 'Something New',
        ]);

        self::assertResponseRedirects('/fixture/');

        $fixture = $this->fixtureRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getDate());
        self::assertSame('Something New', $fixture[0]->getHomeAway());
        self::assertSame('Something New', $fixture[0]->getCompetition());
        self::assertSame('Something New', $fixture[0]->getTeam());
        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getClub());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Fixture();
        $fixture->setDate('Value');
        $fixture->setHomeAway('Value');
        $fixture->setCompetition('Value');
        $fixture->setTeam('Value');
        $fixture->setName('Value');
        $fixture->setClub('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/fixture/');
        self::assertSame(0, $this->fixtureRepository->count([]));
    }
}
