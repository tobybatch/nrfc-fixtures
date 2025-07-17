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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
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
            ->expects($this->exactly(7))
            ->method('add')
            ->withConsecutive(
                [
                    'date',
                    DateType::class,
                    $this->callback(function (array $options) {
                        return 'single_text' === $options['widget'];
                    }),
                ],
                [
                    'homeAway',
                    EnumType::class,
                    $this->callback(function (array $options) {
                        return HomeAway::class === $options['class'];
                    }),
                ],
                [
                    'competition',
                    EnumType::class,
                    $this->callback(function (array $options) {
                        return Competition::class === $options['class'];
                    }),
                ],
                [
                    'team',
                    EnumType::class,
                    $this->callback(function (array $options) {
                        return
                            isset($options['class']) && Team::class === $options['class']
                            && isset($options['placeholder']) && '-- choose team --' === $options['placeholder']
                            && isset($options['label']) && 'Team/Age group' === $options['label']
                            && isset($options['choice_label']) && is_callable($options['choice_label']);
                    }),
                ],
                [
                    'club',
                    EntityType::class,
                    $this->callback(function (array $options) {
                        return 'Opponent/Training partner' === $options['label']
                            && Club::class === $options['class']
                            && false === $options['required']
                            && 'name' === $options['choice_label']
                            && 'N/A' === $options['placeholder'];
                    }),
                ],
                [
                    'opponent',
                    EnumType::class,
                    $this->callback(function ($options) {
                        return
                            isset($options['class']) && Team::class === $options['class']
                            && isset($options['placeholder']) && '-- choose team --' === $options['placeholder']
                            && isset($options['label']) && 'Opposing team' === $options['label']
                            && isset($options['required']) && false === $options['required']
                            && isset($options['help']) && 'For youth fixtures you can leave this blank.' === $options['help']
                            && isset($options['choice_label']) && is_callable($options['choice_label']);
                    }),
                ],
                [
                    'notes',
                    TextareaType::class,
                    $this->callback(function (array $options) {
                        return 'Notes' === $options['label']
                            && false === $options['required'];
                    }),
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
