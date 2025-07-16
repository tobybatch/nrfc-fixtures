<?php

namespace App\Tests\Form;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Club;
use App\Entity\Fixture;
use App\Form\FixtureType;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @covers \App\Form\FixtureType
 */
class FixtureTypeTest extends TestCase
{
    private FixtureType $fixtureType;

    protected function setUp(): void
    {
        $this->fixtureType = new FixtureType();
    }

    public function testBuildForm(): void
    {
        $formBuilder = $this->createMock(FormBuilderInterface::class);

        $formBuilder
            ->expects($this->exactly(8))
            ->method('add')
            ->withConsecutive(
                [
                    'date',
                    DateType::class,
                    $this->callback(function (array $options) {
                        return $options['widget'] === 'single_text';
                    })
                ],
                [
                    'homeAway',
                    EnumType::class,
                    $this->callback(function (array $options) {
                        return $options['class'] === HomeAway::class;
                    })
                ],
                [
                    'competition',
                    EnumType::class,
                    $this->callback(function (array $options) {
                        return $options['class'] === Competition::class;
                    })
                ],
                [
                    'team',
                    EnumType::class,
                    $this->callback(function (array $options) {
                        return $options['class'] === Team::class
                            && $options['placeholder'] === '-- choose team --'
                            && $options['label'] === 'Team/Age group';
                    })
                ],
                [
                    'club',
                    EntityType::class,
                    $this->callback(function (array $options) {
                        return $options['label'] === 'Opponent/Training partner'
                            && $options['class'] === Club::class
                            && $options['required'] === false
                            && $options['choice_label'] === 'name'
                            && $options['placeholder'] === 'N/A';
                    })
                ],
                [
                    'opponent',
                    EntityType::class,
                    $this->callback(function (array $options) {
                        return $options['label'] === 'Opposing team'
                            && $options['class'] === Team::class
                            && $options['required'] === false
                            && $options['choice_label'] === 'name'
                            && $options['placeholder'] === 'N/A';
                    })
                ],
                [
                    'notes',
                    EntityType::class,
                    $this->callback(function (array $options) {
                        return $options['label'] === 'Notes'
                            && $options['required'] === false
                    })
                ]
            )
            ->willReturnSelf();

        $this->fixtureType->buildForm($formBuilder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);

        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with([
                'data_class' => Fixture::class,
            ]);

        $this->fixtureType->configureOptions($resolver);
    }
}