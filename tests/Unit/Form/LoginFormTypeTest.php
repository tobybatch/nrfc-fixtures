<?php

namespace App\Tests\Unit\Form;

use App\Entity\User;
use App\Form\LoginFormType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Test\FormBuilderInterface as TestFormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginFormTypeTest extends TestCase
{
    private LoginFormType $formType;

    protected function setUp(): void
    {
        $this->formType = new LoginFormType();
    }

    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder
            ->expects($this->exactly(1))
            ->method('add')
            ->withConsecutive(
                [
                    '_username',
                    TextType::class,
                    $this->callback(function (array $options) {
                        return $options['label'] === 'Email'
                            && $options['attr']['autocomplete'] === 'username email';
                    })
                ],
                [
                    '_password',
                    PasswordType::class,
                    $this->callback(function (array $options) {
                        $notBlankConstraint = new NotBlank(['message' => 'Please enter a password']);
                        return $options['mapped'] === false
                            && $options['attr']['autocomplete'] === 'current-password'
                            && $options['constraints'][0]->message === $notBlankConstraint->message;
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
                'csrf_field_name' => '_csrf_token',
                'csrf_token_id' => 'authenticate',
            ]);

        $this->formType->configureOptions($resolver);
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame('', $this->formType->getBlockPrefix());
    }
}