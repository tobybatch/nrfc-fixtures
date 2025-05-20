<?php

namespace App\Tests\E2E;

use AllowDynamicProperties;
use App\Entity\User;
use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserTest extends PantherTestCase
{
    protected Client $client;
    protected string $email = 'bar@example.com';
    protected string $password = 'bad_password';

    public function setUp(): void
    {
        parent::setUp();

        // Create client once
        $this->client = static::createPantherClient();

    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        $this->client->manage()->deleteAllCookies();

        // Clear local and session storage using JavaScript
        $this->client->executeScript('window.localStorage.clear();');
        $this->client->executeScript('window.sessionStorage.clear();');
    }

    public function testLoginPage(): void
    {
        $this->client->request('GET', '/user/login');

        $this->assertSelectorTextContains('h2', 'Sign in to your account');
        $this->assertSelectorTextContains('a[href="/user/magic_login"]', 'Send me a magic link...');
        $this->assertSelectorTextContains('/html/body/main/div/div/div[2]/form/div[4]/button', 'Login');
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testLogin(): void {
        $this->createUser();

        $this->client->request('GET', '/user/login');

        $this->client->submitForm('Login', [
            '_username' => $this->email,
            '_password' => $this->password,
        ]);
        $this->client->waitForElementToContain('body', 'Fixtures for 2025/26');

        $this->client->clickLink($this->email);
        $crawler = $this->client->waitForElementToContain('h2', 'Your Profile');
        $this->assertStringContainsString('Your Profile', $crawler->text());
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testLogout(): void
    {
        $this->createUser();
        $this->client->request('GET', '/user/login');
        $this->client->submitForm('Login', [
            '_username' => $this->email,
            '_password' => $this->password,
        ]);
        $this->client->waitForElementToContain('body', 'Fixtures for 2025/26');
        $crawler = $this->client->clickLink('Logout');
        $this->assertEquals('/', parse_url($this->client->getCurrentURL(), PHP_URL_PATH));
        $this->client->waitForElementToContain('body', 'Login');
        $this->assertStringContainsString('Login', $crawler->text());
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testMagicLink(): void
    {
        $this->createUser();

        $this->client->request('GET', '/user/magic_login');
        $this->client->submitForm('Send Login Link', [
            'email' => 'not@a.real.email.address',
        ]);
        $crawler = $this->client->waitForElementToContain('body', 'Unknown email address');
        $this->assertStringContainsString('Unknown email address', $crawler->text());

        $this->client->request('GET', '/user/magic_login');
        $this->client->submitForm('Send Login Link', [
            'email' => $this->email,
        ]);
        $crawler = $this->client->waitForElementToContain('body', 'Check your email for a magic login link');
        $this->assertStringContainsString('Check your email for a magic login link', $crawler->text());
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testProfilePage(): void
    {
        $this->createUser();
        $this->client->request('GET', '/user/login');
        $this->client->submitForm('Login', [
            '_username' => $this->email,
            '_password' => $this->password,
        ]);
        $this->client->waitForElementToContain('body', 'Fixtures for 2025/26');
        $this->client->clickLink($this->email);
        $this->client->waitForElementToContain('body', 'Your Profile');
        $this->client->clickLink('Update Profile');

    }
    public function testChangePassword(): void {}

    private function createUser(): void
    {
        $this->client->request('GET', '/user/register');
        $this->client->submitForm('Create an account', [
            'registration_form[email]' => $this->email,
            'registration_form[plainPassword]' => $this->password,
        ]);
    }
}