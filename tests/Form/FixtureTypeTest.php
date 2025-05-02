<?php

namespace App\Tests\Form;

use App\Entity\Club;
use App\Entity\Fixture;
use App\Form\FixtureType;
use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;

class FixtureTypeTest extends TestCase
{
    private FormFactoryInterface $factory;

    protected function setUp(): void
    {
        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions([])
            ->getFormFactory();
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'date' => '2023-01-01 14:30:00',
            'homeAway' => HomeAway::Home->value,
            'competition' => Competition::Friendly->value,
            'team' => Team::U13B->value,
            'club' => 1,
            'notes' => 'Test notes',
        ];

        $club = new Club();
        $club->setId(1);

        $form = $this->factory->create(FixtureType::class);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    public function testFormFields(): void
    {
        $form = $this->factory->create(FixtureType::class);
        $view = $form->createView();
        $children = $view->children;

        $this->assertArrayHasKey('date', $children);
        $this->assertArrayHasKey('homeAway', $children);
        $this->assertArrayHasKey('competition', $children);
        $this->assertArrayHasKey('team', $children);
        $this->assertArrayHasKey('club', $children);
        $this->assertArrayHasKey('notes', $children);

        $this->assertEquals(DateTimeType::class, $form->get('date')->getConfig()->getType()->getInnerType()::class);
        $this->assertEquals(EnumType::class, $form->get('homeAway')->getConfig()->getType()->getInnerType()::class);
        $this->assertEquals(EnumType::class, $form->get('competition')->getConfig()->getType()->getInnerType()::class);
        $this->assertEquals(EnumType::class, $form->get('team')->getConfig()->getType()->getInnerType()::class);
        $this->assertEquals(EntityType::class, $form->get('club')->getConfig()->getType()->getInnerType()::class);
        $this->assertEquals(TextType::class, $form->get('notes')->getConfig()->getType()->getInnerType()::class);
    }

    public function testFormOptions(): void
    {
        $form = $this->factory->create(FixtureType::class);

        $this->assertEquals(Fixture::class, $form->getConfig()->getDataClass());
    }
} 