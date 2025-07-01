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
            ->add('date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('homeAway', EnumType::class, [
                'class' => HomeAway::class,
                'data' => HomeAway::TBA,
            ])
            ->add('competition', EnumType::class, [
                'label' => 'Competition/Training',
                'class' => Competition::class,
                'data' => Competition::Training,
            ])
            ->add('team', EnumType::class, [
                'class' => Team::class,
                'placeholder' => '-- choose team --',
                'label' => 'Team/Age group',
            ])
            ->add('club', EntityType::class, [
                'label' => 'Opponent/Training partner',
                'class' => Club::class,
                'required' => false,
                'choice_label' => 'name',
                'placeholder' => 'N/A',
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
