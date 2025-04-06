<?php

namespace App\Form;

use App\Config\Team;
use App\Form\Model\FixturesDisplayOptionsDTO;
use App\Service\PreferencesService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class FixturesDisplayOptionsForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = array_reduce(Team::cases(), function ($carry, $enum) {
            $carry[$enum->value] = $enum->name;
            return $carry;
        }, []);

        $builder
            ->add('teams', ChoiceType::class, [
                'choices' => $choices,
                'expanded' => true,
                'multiple' => true,
                'label' => 'Show which teams?',
                'attr' => ['class' => 'space-y-2 w-1'],
                'label_attr' => [
                    'class' => ''
//                    'class' => 'font-bold block mb-0 text-gray-900 ml-10'
                ],
                'row_attr' => ['class' => 'flex items-start'],
            ])
            ->add('showPastDates', CheckboxType::class, [
                'label' => 'Show?',
                'required' => false,
                'attr' => ['class' => 'form-checkbox h-4 w-4'],
                'label_attr' => ['class' => ''],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // TODO Enable CRSF - The apache -> nginx -> fpm seems to mess it up
        $resolver->setDefaults([
            'csrf_protection' => false,
//            'csrf_field_name' => '_token',
            'data_class' => FixturesDisplayOptionsDTO::class,
        ]);
    }
}