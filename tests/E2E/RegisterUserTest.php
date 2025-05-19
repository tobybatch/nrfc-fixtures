<?php

namespace App\Tests\E2E;

use App\Entity\User;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Panther\PantherTestCase;

// TODO bad email
class RegisterUserTest extends PantherTestCase
{

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testRegisterLink(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/user/login');

        $registerLink = $crawler->filter('a[href="/user/register"]');
        $this->assertTrue($registerLink->isDisplayed(), 'Register link should be visible.');

        $crawler = $client->clickLink('Register');
        $client->waitForElementToContain('body', 'Register a new account');
        $this->assertSelectorTextContains('body', 'Register a new account', 'Register page should be displayed.');
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testRegister(): void
    {
        $email = "foo@eaxmple.com";

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entities = $entityManager->getRepository(User::class)->findBy([
            'email' => $email
        ]);
        $this->assertCount(0, $entities);

        $client = static::createPantherClient();
        $client->request('GET', '/user/register');

        $client->submitForm('Create an account', [
            'registration_form[email]' => $email,
            'registration_form[plainPassword]' => 'password',
        ]);
        $crawler = $client->waitForElementToContain('body', 'Account created, you can log in now!');
        $this->assertStringContainsString('Account created, you can log in now!', $crawler->text());

        $entities = $entityManager->getRepository(User::class)->findBy([
            'email' => $email
        ]);
        $this->assertCount(1, $entities);
    }
}