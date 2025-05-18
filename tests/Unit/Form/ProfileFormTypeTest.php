<?php

namespace App\Tests\Unit\Form;

use App\Entity\User;
use App\Form\ProfileFormType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ProfileFormTypeTest extends TestCase
{
    private ProfileFormType $formType;

    protected function setUp(): void
    {
        $this->formType = new ProfileFormType();
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
                    EmailType::class,
                    $this->callback(function (array $options) {
                        return $options['required'] === true
                            && $options['attr']['class'] === 'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
                            && $options['attr']['placeholder'] === 'Your email address';
                    })
                ],
                [
                    'currentPassword',
                    PasswordType::class,
                    $this->callback(function (array $options) {
                        return $options['mapped'] === false
                            && $options['required'] === true
                            && $options['attr']['class'] === 'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
                            && $options['attr']['placeholder'] === 'Current password';
                    })
                ],
                [
                    'newPassword',
                    RepeatedType::class,
                    $this->callback(function (array $options) {
                        $lengthConstraint = new Length([
                            'min' => 6,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            'max' => 4096,
                        ]);
                        return $options['type'] === PasswordType::class
                            && $options['mapped'] === false
                            && $options['required'] === false
                            && $options['invalid_message'] === 'The password fields must match.'
                            && $options['first_options']['attr']['class'] === 'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
                            && $options['first_options']['attr']['placeholder'] === 'New password (leave blank to keep current)'
                            && $options['first_options']['constraints'][0]->min === $lengthConstraint->min
                            && $options['first_options']['constraints'][0]->minMessage === $lengthConstraint->minclubs
                            && $options['first_options']['constraints'][0]->max === $lengthConstraint->max
                            && $options['second_options']['attr']['class'] === 'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mt-2'
                            && $options['second_options']['attr']['placeholder'] === 'Repeat new password';
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