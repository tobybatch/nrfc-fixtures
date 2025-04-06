<?php

namespace App\Tests\Unit\Form;

use App\Form\ResetPasswordRequestForm;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordRequestFormTest extends TestCase
{
    public function testBuildForm(): void
    {
        $formBuilder = $this->createMock(FormBuilderInterface::class);

        // Expect the builder to add the 'email' field with EmailType
        $formBuilder
            ->expects($this->once())
            ->method('add')
            ->with(
                'email',
                EmailType::class,
                $this->callback(function (array $options) {
                    // Verify the EmailType options
                    $this->assertEquals(['autocomplete' => 'email'], $options['attr']);

                    // Verify constraints
                    $constraints = $options['constraints'];
                    $this->assertCount(1, $constraints);

                    // Check NotBlank constraint
                    $notBlank = $constraints[0];
                    $this->assertInstanceOf(NotBlank::class, $notBlank);
                    $this->assertEquals('Please enter your email', $notBlank->message);

                    return true;
                })
            );

        $formType = new ResetPasswordRequestForm();
        $formType->buildForm($formBuilder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);

        // Expect setDefaults to be called with an empty array
        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with([]);

        $formType = new ResetPasswordRequestForm();
        $formType->configureOptions($resolver);
    }
}