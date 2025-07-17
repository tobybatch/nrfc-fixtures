<?php

namespace App\Tests\Unit\Service;

use App\Service\PreferencesService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PreferencesServiceTest extends TestCase
{
    private RequestStack $requestStack;
    private SessionInterface $session;
    private LoggerInterface $logger;
    private PreferencesService $preferencesService;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $request = $this->createMock(Request::class);
        $request->method('getSession')->willReturn($this->session);

        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->method('getCurrentRequest')->willReturn($request);

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->preferencesService = new PreferencesService($this->requestStack, $this->logger);
    }

    public function testGetPreferencesReturnsEmptyArrayByDefault()
    {
        $this->session->method('get')->with('preferences', [])->willReturn([]);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Getting preferences', []);

        $result = $this->preferencesService->getPreferences();
        $this->assertSame([], $result);
    }

    public function testGetPreferencesReturnsExistingData()
    {
        $existing = ['theme' => 'dark', 'language' => 'en'];
        $this->session->method('get')->with('preferences', [])->willReturn($existing);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Getting preferences', $existing);

        $result = $this->preferencesService->getPreferences();
        $this->assertSame($existing, $result);
    }

    public function testSetPreferencesUpdatesSession()
    {
        $existing = ['language' => 'en'];
        $expected = ['language' => 'en', 'theme' => 'dark'];

        $this->session
            ->method('get')
            ->with('preferences', [])
            ->willReturn($existing);

        $this->session
            ->expects($this->once())
            ->method('set')
            ->with('preferences', $expected);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Getting preferences', $existing);

        $this->preferencesService->setPreferences('theme', 'dark');
    }

    public function testGetDataReturnsPreferences()
    {
        $preferences = ['foo' => 'bar'];
        $this->session->method('get')->willReturn($preferences);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Getting preferences', $preferences);

        $result = $this->preferencesService->getData();
        $this->assertSame($preferences, $result);
    }
}
