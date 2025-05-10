<?php

namespace App\Tests\E2e;

use Symfony\Component\Panther\PantherTestCase;

class LoginFlowTest extends PantherTestCase
{
    private const LOGIN_URL = '/login';
    private const DEFAULT_TIMEOUT = 3000; // 3 seconds in ms
    
    public function testSuccessfulLoginRedirectsToTargetPath()
    {$uniqueTempDir = sys_get_temp_dir() . '/browser_' . uniqid();
        $client = static::createPantherClient(['browser' => PantherTestCase::CHROME], [
                '--headless',
                '--disable-gpu',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--user-data-dir=' . $uniqueTempDir,
            ]
        );
        
        // Navigate to login page
        $crawler = $client->request('GET', self::LOGIN_URL);
        
        // Fill in the form
        $form = $crawler->filter('form[name=login_form]')->form([
            'email' => 'valid_user@example.com',
            'password' => 'correct_password',
        ]);
        
        $client->submit($form);
        
        // Wait for redirect after successful login
        $client->waitFor('.dashboard', self::DEFAULT_TIMEOUT); // Adjust selector to your app
        
        // Assert we were redirected to the correct page
        $this->assertStringContainsString('/dashboard', $client->getCurrentURL());
    }
    
    public function testFailedLoginShowsErrorMessage()
    {
        $client = static::createPantherClient();
        
        $crawler = $client->request('GET', self::LOGIN_URL);
        
        $form = $crawler->filter('form[name=login_form]')->form([
            'email' => 'invalid_user@example.com',
            'password' => 'wrong_password',
        ]);
        
        $client->submit($form);
        
        // Wait for error message to appear
        $client->waitFor('.alert-danger', self::DEFAULT_TIMEOUT);
        
        // Assert error message is displayed
        $this->assertSelectorTextContains('.alert-danger', 'Invalid credentials');
    }
    
    public function testEmptySubmissionShowsValidationErrors()
    {
        $client = static::createPantherClient();
        
        $crawler = $client->request('GET', self::LOGIN_URL);
        
        $form = $crawler->filter('form[name=login_form]')->form([
            'email' => '',
            'password' => '',
        ]);
        
        $client->submit($form);
        
        // Wait for validation errors
        $client->waitFor('.invalid-feedback', self::DEFAULT_TIMEOUT);
        
        // Assert validation messages
        $this->assertSelectorTextContains('.invalid-feedback', 'This value should not be blank');
    }
    
    public function testLoginPageHasAllRequiredElements()
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', self::LOGIN_URL);
        
        // Check page title
        $this->assertPageTitleContains('Login');
        
        // Check form elements exist
        $this->assertSelectorExists('form[name=login_form]');
        $this->assertSelectorExists('input[name="email"]');
        $this->assertSelectorExists('input[name="password"]');
        $this->assertSelectorExists('button[type="submit"]');
        
        // Check remember me if you have it
        // $this->assertSelectorExists('input[name="_remember_me"]');
        
        // Check forgot password link if you have it
        // $this->assertSelectorExists('a[href="/reset-password"]');
    }
    
    public function testCsrfProtectionIsWorking()
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', self::LOGIN_URL);
        
        // Get the CSRF token
        $csrfToken = $crawler->filter('input[name="_csrf_token"]')->attr('value');
        
        // Try to submit with invalid CSRF
        $client->executeScript('document.querySelector("input[name=\'_csrf_token\']").value = "invalid_token"');
        
        $form = $crawler->filter('form[name=login_form]')->form([
            'email' => 'valid_user@example.com',
            'password' => 'correct_password',
        ]);
        
        $client->submit($form);
        
        // Should show an error
        $client->waitFor('.alert-danger', self::DEFAULT_TIMEOUT);
        $this->assertSelectorTextContains('.alert-danger', 'Invalid CSRF token');
    }
    
    public function testLoginThenLogoutFlow()
    {
        $client = static::createPantherClient();
        
        // Login first
        $crawler = $client->request('GET', self::LOGIN_URL);
        $form = $crawler->filter('form[name=login_form]')->form([
            'email' => 'valid_user@example.com',
            'password' => 'correct_password',
        ]);
        $client->submit($form);
        $client->waitFor('.dashboard', self::DEFAULT_TIMEOUT);
        
        // Click logout
        $client->clickLink('Logout'); // Adjust to your logout link text/selector
        
        // Should redirect to login or home page
        $client->waitFor('.login-form', self::DEFAULT_TIMEOUT);
        $this->assertStringContainsString('/login', $client->getCurrentURL());
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
