<?php

namespace App\Tests\Unit\Form;

use App\Entity\User;
use App\Form\RegistrationFormType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormTypeTest extends TestCase
{
    private RegistrationFormType $formType;

    protected function setUp(): void
    {
        $this->formType = new RegistrationFormType();
    }

    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder
            ->expects($this->exactly(1))
            ->method('add')
            ->withConsecutive(
                [
                    'email',
                    null,
                    $this->callback(function (array $options) {
                        return empty($options); // No specific options for email field
                    })
                ],
                [
                    'plainPassword',
                    PasswordType::class,
                    $this->callback(function (array $options) {
                        $notBlankConstraint = new NotBlank(['message' => 'Please enter a password']);
                        $lengthConstraint = new Length([
                            'min' => 6,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            'max' => 4096,
                        ]);
                        return $options['mapped'] === false
                            && $options['attr']['autocomplete'] === 'new-password'
                            && $options['constraints'][0]->message === $notBlankConstraint->message
                            && $options['constraints'][1]->min === $lengthConstraint->min
                            && $options['constraints'][1]->minMessage === $lengthConstraint->minMessage
                            && $options['constraints'][1]->max === $lengthConstraint->max;
                    })
                ]
            );

        $this->formType->buildForm($builder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);

        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with([
                'data_class' => User::class,
            ]);

        $this->formType->configureOptions($resolver);
    }
}