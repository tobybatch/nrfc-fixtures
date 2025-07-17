<?php

namespace App\Tests\Unit\Form;

use App\Form\ChangePasswordForm;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class ChangePasswordFormTest extends TestCase
{
    public function testBuildForm(): void
    {
        $formBuilder = $this->createMock(FormBuilderInterface::class);

        // Expect the builder to add the 'plainPassword' field with RepeatedType
        $formBuilder
            ->expects($this->once())
            ->method('add')
            ->with(
                'plainPassword',
                RepeatedType::class,
                $this->callback(function (array $options) {
                    // Verify the RepeatedType options
                    $this->assertEquals(PasswordType::class, $options['type']);
                    $this->assertEquals(['attr' => ['autocomplete' => 'new-password']], $options['options']);
                    $this->assertEquals('New password', $options['first_options']['label']);
                    $this->assertEquals('Repeat Password', $options['second_options']['label']);
                    $this->assertEquals('The password fields must match.', $options['invalid_message']);
                    $this->assertFalse($options['mapped']);

                    // Verify constraints
                    $constraints = $options['first_options']['constraints'];
                    $this->assertCount(4, $constraints);

                    // Check NotBlank constraint
                    $notBlank = $constraints[0];
                    $this->assertInstanceOf(NotBlank::class, $notBlank);
                    $this->assertEquals('Please enter a password', $notBlank->message);

                    // Check Length constraint
                    $length = $constraints[1];
                    $this->assertInstanceOf(Length::class, $length);
                    $this->assertEquals(12, $length->min);
                    $this->assertEquals('Your password should be at least {{ limit }} characters', $length->minMessage);
                    $this->assertEquals(4096, $length->max);

                    // Check PasswordStrength constraint
                    $this->assertInstanceOf(PasswordStrength::class, $constraints[2]);

                    // Check NotCompromisedPassword constraint
                    $this->assertInstanceOf(NotCompromisedPassword::class, $constraints[3]);

                    return true;
                })
            );

        $formType = new ChangePasswordForm();
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

        $formType = new ChangePasswordForm();
        $formType->configureOptions($resolver);
    }
}
