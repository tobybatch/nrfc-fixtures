<?php

namespace App\Tests\Twig;

use App\Entity\Club;
use App\Twig\MapExtension;
use PHPUnit\Framework\TestCase;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;
use Twig\Environment;
use Twig\Error\RuntimeError;

class MapExtensionTest extends TestCase
{
    private Environment $twig;
    private MapExtension $extension;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->extension = new MapExtension($this->twig);
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();

        $this->assertCount(1, $functions);
        $this->assertEquals('makeMap', $functions[0]->getName());
    }

    public function testMakeMapWithNullClub()
    {
        $result = $this->extension->makeMap(null);
        $this->assertEquals('', $result);
    }

    public function testMakeMapWithClubWithoutCoordinates()
    {
        $club = new Club();
        $club->setName('Test Club');
        $club->setAddress('123 Test Street');

        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                'components/map.html.twig',
                [
                    'address' => '123 Test Street',
                    'map' => false,
                ]
            )
            ->willReturn('<div>Rendered template</div>');

        $result = $this->extension->makeMap($club);
        $this->assertEquals('<div>Rendered template</div>', $result);
    }

    public function testMakeMapWithClubWithCoordinates()
    {
        $club = new Club();
        $club->setName('Test Club');
        $club->setAddress('123 Test Street');
        $club->setLatitude(51.5074);
        $club->setLongitude(-0.1278);

        $expectedPoint = new Point(51.5074, -0.1278);
        $expectedMap = (new Map())
            ->center($expectedPoint)
            ->zoom(10)
            ->addMarker(new Marker(
                position: $expectedPoint,
                title: 'Test Club',
            ));

        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('components/map.html.twig'),
                $this->callback(function ($parameters) {
                    return '123 Test Street' === $parameters['address']
                        && $parameters['map'] instanceof Map;
                })
            )
            ->willReturn('<div>Rendered template with map</div>');

        $result = $this->extension->makeMap($club);
        $this->assertEquals('<div>Rendered template with map</div>', $result);
    }

    public function testMakeMapHandlesTwigErrors()
    {
        $club = new Club();
        $club->setName('Test Club');
        $club->setAddress('123 Test Street');
        $club->setLatitude(51.5074);
        $club->setLongitude(-0.1278);

        $this->twig->expects($this->once())
            ->method('render')
            ->willThrowException(new RuntimeError('Template error'));

        $this->expectException(RuntimeError::class);
        $this->extension->makeMap($club);
    }
}
