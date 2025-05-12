<?php

namespace App\Tests\E2E;

use App\DataFixtures\Users;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Panther\PantherTestCase;

class LoginFlowTest extends PantherTestCase
{
    protected function setUp(): void
    {
        // Create client once
        $this->client = static::createPantherClient();
    }
    
    protected function tearDown(): void
    {        
        $this->client->manage()->deleteAllCookies();
    
        // Clear local and session storage using JavaScript
        $this->client->executeScript('window.localStorage.clear();');
        $this->client->executeScript('window.sessionStorage.clear();');
    }
    
    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testLoginLink()
    {
        $this->client->request('GET', '/');
        $this->client->waitForElementToContain('body', 'Login');
        $this->client->clickLink('Login');
        $crawler = $this->client->waitForElementToContain('body', 'Sign in to your account');
        $this->assertEquals(
            '/user/login',
            parse_url($this->client->getCurrentURL(), PHP_URL_PATH),
            'Navigation to /user/login failed'
        );
        // top login button is hidden
        $loginButton = $crawler->filterXPath('/html/body/header/div/div[2]/a');
        // Assert that no elements match the XPath
        $this->assertCount(
            0,
            $loginButton,
            'Login button XPath should not be present on the login page'
        );
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testBadUser() {
        $this->client->request('GET', '/user/login');
        $this->client->submitForm('Login', [
            '_username' => 'baduser',
            '_password' => 'bad password',
        ]);
        $crawler = $this->client->waitForElementToContain('body', 'Invalid credentials');
        $this->assertStringContainsString('Invalid credentials', $crawler->text());
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testGoodUser() {
        $this->client->request('GET', '/user/login');
        $this->client->submitForm('Login', [
            '_username' => Users::BASIC_USER,
            '_password' => Users::PASSWORD,
        ]);
        $crawler = $this->client->waitForElementToContain('body', Users::BASIC_USER);
        $element = $crawler->filterXPath("/html/body/header/div/div[2]/a[1]")->first();
        $this->assertEquals(Users::BASIC_USER, $element->text());

        // Sneaky check logout here
        $this->client->clickLink("Logout");
        $this->client->waitForElementToContain('body', 'Login');

        $loginButton = $crawler->filterXPath('/html/body/header/div/div[2]/a');
        // Assert that no elements match the XPath
        $this->assertCount(
            1,
            $loginButton,
            'Login button XPath should not be present on the login page'
        );
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testRegistration() {
        $username = "new_user@example.com";
        $password = "password123";

        $this->client->request('GET', '/user/register');
        // submit form
        $crawler = $this->client->submitForm('Create an account', [
            'registration_form[email]' => $username,
            'registration_form[plainPassword]' => $password,
        ]);
        // check we get Account created, you can log in now!
        $crawler = $this->client->waitForElementToContain('body', 'Account created, you can log in now');
        $this->assertStringContainsString('Account created, you can log in now', $crawler->text());

        $this->client->submitForm('Login', [
            '_username' => $username,
            '_password' => $password,
        ]);
        $crawler = $this->client->waitForElementToContain('body', $username);
        $this->client->clickLink($username);
        $crawler = $this->client->waitForElementToContain('body', 'Your Profile');
        $this->assertStringContainsString('Your Profile', $crawler->text());
    }

    public function testProfilePage() {
        $this->client->request('GET', '/user/login');
        $this->client->submitForm('Login', [
            '_username' => Users::BASIC_USER,
            '_password' => Users::PASSWORD,
        ]);
        $this->client->waitForElementToContain('body', Users::BASIC_USER);

        $this->client->request('GET', '/user/profile');
        $this->client->waitForElementToContain('body', "Your Profile");

        $this->client->submitForm('Update Profile', [
            'profile_form[email]' => Users::BASIC_USER,
            'profile_form[currentPassword]' => Users::PASSWORD,
            'profile_form[newPassword][first]' => 'new_password',
            'profile_form[newPassword][second]' => 'new_password',
        ]);
        $crawler = $this->client->waitForElementToContain('body', 'Your profile has been updated successfully');
        $this->assertStringContainsString('Your profile has been updated successfully', $crawler->text());
    }

    public function testProfilePageBadCurrentPassword() {
        $this->client->request('GET', '/user/login');
        $this->client->submitForm('Login', [
            '_username' => Users::BASIC_USER,
            '_password' => Users::PASSWORD,
        ]);
        $this->client->waitForElementToContain('body', Users::BASIC_USER);

        $this->client->request('GET', '/user/profile');
        $this->client->waitForElementToContain('body', "Your Profile");

        $this->client->submitForm('Update Profile', [
            'profile_form[email]' => Users::BASIC_USER,
            'profile_form[currentPassword]' => "junk",
            'profile_form[newPassword][first]' => 'new_password',
            'profile_form[newPassword][second]' => 'new_password',
        ]);
        $crawler = $this->client->waitForElementToContain('body', 'Your current password is incorrect');
        $this->assertStringContainsString('Your current password is incorrect', $crawler->text());
    }

    // TODO - this doesn't work in the app. We need to report an error on the page itself
//    public function testProfilePageMismatchedNewPassword() {
//        $this->client->request('GET', '/user/login');
//        $this->client->submitForm('Login', [
//            '_username' => Users::BASIC_USER,
//            '_password' => Users::PASSWORD,
//        ]);
//        $this->client->waitForElementToContain('body', Users::BASIC_USER);
//
//        $this->client->request('GET', '/user/profile');
//        $this->client->waitForElementToContain('body', "Your Profile");
//
//        $this->client->submitForm('Update Profile', [
//            'profile_form[email]' => Users::BASIC_USER,
//            'profile_form[currentPassword]' => "junk",
//            'profile_form[newPassword][first]' => 'new_password',
//            'profile_form[newPassword][second]' => 'new_password',
//        ]);
//        $crawler = $this->client->waitForElementToContain('body', 'Your current password is incorrect');
//        $this->assertStringContainsString('Your current password is incorrect', $crawler->text());
//    }

//        public function testMagicLink() {}
}

//Your Profile
//Email
//toby@nfn.org.uk
//Current password
//Current password
//New Password
//New password (leave blank to keep current)
//Repeat New Password
//Repeat new password
//Update Profile