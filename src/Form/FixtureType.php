<?php

namespace App\Form;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Club;
use App\Entity\Fixture;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Fixture>
 */
class FixtureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Display Name',
                'required' => false,
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5' => false, // allows custom format
                'format' => 'dd/MM/yyyy',
                'placeholder' => 'dd/mm/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('homeAway', EnumType::class, [
                'class' => HomeAway::class,
                'choice_label' => fn (HomeAway $choice) => $choice->name,
                'choice_value' => fn (?HomeAway $choice) => $choice?->value,
            ])
            ->add('competition', EnumType::class, [
                'label' => 'Competition/Training',
                'class' => Competition::class,
                'choice_label' => fn (Competition $choice) => $choice->name,
                'choice_value' => fn (?Competition $choice) => $choice?->value,
            ])
            ->add('team', EnumType::class, [
                'class' => Team::class,
                'placeholder' => '-- choose team --',
                'label' => 'Team/Age group',
                'choice_label' => fn (Team $team) => $team->value,
            ])
            ->add('club', EntityType::class, [
                'label' => 'Opponent/Training partner',
                'class' => Club::class,
                'required' => false,
                'choice_label' => 'name',
                'placeholder' => 'N/A',
            ])
            ->add('opponent', EnumType::class, [
                'class' => Team::class,
                'placeholder' => '-- choose team --',
                'label' => 'Opposing team',
                'choice_label' => fn (Team $team) => $team->value,
                'required' => false,
                'help' => 'For youth fixtures you can leave this blank.',
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fixture::class,
        ]);
    }
}
