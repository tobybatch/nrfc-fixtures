<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\ProfileFormType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;

class ProfileFormTypeTest extends TestCase
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
            'email' => 'test@example.com',
            'currentPassword' => 'current_password',
            'plainPassword' => [
                'first' => 'new_password',
                'second' => 'new_password',
            ],
        ];

        $form = $this->factory->create(ProfileFormType::class);

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
        $form = $this->factory->create(ProfileFormType::class);
        $view = $form->createView();
        $children = $view->children;

        $this->assertArrayHasKey('email', $children);
        $this->assertArrayHasKey('currentPassword', $children);
        $this->assertArrayHasKey('plainPassword', $children);

        $this->assertEquals(EmailType::class, $form->get('email')->getConfig()->getType()->getInnerType()::class);
        $this->assertEquals(PasswordType::class, $form->get('currentPassword')->getConfig()->getType()->getInnerType()::class);
        $this->assertEquals(RepeatedType::class, $form->get('plainPassword')->getConfig()->getType()->getInnerType()::class);
    }

    public function testFormOptions(): void
    {
        $form = $this->factory->create(ProfileFormType::class);

        $this->assertEquals(User::class, $form->getConfig()->getDataClass());
    }
} 