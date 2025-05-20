<?php

namespace App\Tests\Unit\Service;

use App\Service\PreferencesService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;


class PreferencesServiceTest extends KernelTestCase
{
    protected array $preferences = array(
        "path" => [
            "one" => [
                "sub level" => "sub value",
            ],
            "a" => true,
            "b" => true,
            "c" => false,
        ]);

    private PreferencesService $preferencesService;
    private $sessionMock;
    private $requestMock;
    private $requestStackMock;

    public function setUp(): void
    {
        self::bootKernel();
        // Create a mock for the Session class
        $this->sessionMock = $this->createMock(Session::class);
        $this->sessionMock
            ->method('get')
            ->willReturn($this->preferences);

        // Create a mock for the Request class
        $this->requestMock = $this->createMock(Request::class);
        $this->requestMock
            ->method('hasSession')
            ->willReturn(true);
        $this->requestMock
            ->method('getSession')
            ->willReturn($this->sessionMock);

        // Create a mock for the RequestStack class
        $this->requestStackMock = $this->createMock(RequestStack::class);
        $this->requestStackMock
            ->method('getCurrentRequest')
            ->willReturn($this->requestMock);

        // Instantiate PreferencesService with the mocked RequestStack
        $this->preferencesService = new PreferencesService($this->requestStackMock);
    }

    public function testGet(): void
    {
        $expected = $this->preferences;
        $preferences = $this->preferencesService->getPreferences();
        $this->assertEquals($expected, $preferences);
    }

    public function testSet(): void
    {
        $expected = array(
            "path" => [
                "one" => [
                    "sub level" => "sub value",
                    "two" => [
                        "three" => "value",
                    ]
                ],
                "a" => true,
                "b" => true,
                "c" => false,
            ]);
        $preferences = $this->preferencesService->setPreferences('path.one.two.three', 'value');

        $this->assertEquals('value', $preferences['path']['one']['two']['three']);
        $this->assertEquals($expected, $preferences);
    }
}
