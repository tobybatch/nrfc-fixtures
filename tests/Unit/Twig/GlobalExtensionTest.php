<?php

namespace App\Tests\Unit\Twig;

use App\Entity\User;
use App\Twig\GlobalExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class GlobalExtensionTest extends TestCase
{
    private GlobalExtension $extension;
    private RequestStack $requestStack;

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();
        $this->extension = new GlobalExtension($this->requestStack);
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();

        $this->assertCount(2, $functions);
        $this->assertEquals('pageHasHelp', $functions[0]->getName());
        $this->assertEquals('pageHelpIsVisible', $functions[1]->getName());
    }

    public function testPageHasHelpWithNoRequest()
    {
        // No request pushed to the stack
        $this->assertTrue($this->extension->pageHasHelp());
    }

    public function testPageHasHelpWithNonMatchingRoute()
    {
        $request = Request::create('/some/route');
        $request->attributes->set('_route', 'some_route');
        $this->requestStack->push($request);

        $this->assertFalse($this->extension->pageHasHelp());
    }

    public function testPageHasHelpWithMatchingRoute()
    {
        $request = Request::create('/fixture');
        $request->attributes->set('_route', 'app_fixture_index');
        $this->requestStack->push($request);

        $this->assertTrue($this->extension->pageHasHelp());
    }

    public function testPageHelpIsVisibleWithNoRequest()
    {
        // No request pushed to the stack
        $this->assertTrue($this->extension->pageHelpIsVisible());
    }

    public function testPageHelpIsVisibleWithNoPreferences()
    {
        $request = Request::create('/some/route');
        $request->attributes->set('_route', 'some_route');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->requestStack->push($request);

        $this->assertTrue($this->extension->pageHelpIsVisible());
    }

    public function testPageHelpIsVisibleWithPreferencesButNoRoute()
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('preferences', [
            'showHelp' => [
                'other_route' => true
            ]
        ]);

        $request = Request::create('/some/route');
        $request->attributes->set('_route', 'some_route');
        $request->setSession($session);
        $this->requestStack->push($request);

        $this->assertTrue($this->extension->pageHelpIsVisible());
    }

    public function testPageHelpIsVisibleWithUserPreferences()
    {
        $user = $this->createMock(User::class);
        $user->method('getPreferences')->willReturn([
            'showHelp' => [
                'some_route' => true
            ]
        ]);

        $session = new Session(new MockArraySessionStorage());
        $session->set('user', $user);

        $request = Request::create('/some/route');
        $request->attributes->set('_route', 'some_route');
        $request->setSession($session);
        $this->requestStack->push($request);

        $this->assertTrue($this->extension->pageHelpIsVisible());
    }

    public function testPageHelpIsNotVisibleWithMergedPreferences()
    {
        $user = $this->createMock(User::class);
        $user->method('getPreferences')->willReturn([
            'showHelp' => [
                'some_route' => false
            ]
        ]);

        $session = new Session(new MockArraySessionStorage());
        $session->set('user', $user);
        $session->set('preferences', [
            'showHelp' => [
                'some_route' => true
            ]
        ]);

        $request = Request::create('/some/route');
        $request->attributes->set('_route', 'some_route');
        $request->setSession($session);
        $this->requestStack->push($request);

        $this->assertFalse($this->extension->pageHelpIsVisible());
    }

    public function testPageHelpIsVisibleWithDisabledHelp()
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('preferences', [
            'showHelp' => [
                'some_route' => false
            ]
        ]);

        $request = Request::create('/some/route');
        $request->attributes->set('_route', 'some_route');
        $request->setSession($session);
        $this->requestStack->push($request);

        $this->assertFalse($this->extension->pageHelpIsVisible());
    }
}