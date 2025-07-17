<?php

namespace App\Tests\Form;

use App\Entity\Club;
use App\Form\ClubType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @covers \App\Form\ClubType
 */
class ClubTypeTest extends TestCase
{
    private ClubType $clubType;

    protected function setUp(): void
    {
        $this->clubType = new ClubType();
    }

    public function testBuildForm(): void
    {
        $formBuilder = $this->createMock(FormBuilderInterface::class);

        $formBuilder
            ->expects($this->exactly(3))
            ->method('add')
            ->withConsecutive(
                ['name'],
                ['address', TextareaType::class]
            )
            ->willReturnSelf();

        $this->clubType->buildForm($formBuilder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);

        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with(['data_class' => Club::class]);

        $this->clubType->configureOptions($resolver);
    }
}
