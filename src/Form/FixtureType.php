<?php

namespace App\Form;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Club;
use App\Entity\Fixture;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FixtureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('homeAway', EnumType::class, [
                'class' => HomeAway::class,
                'placeholder' => HomeAway::TBA->value,
            ])
            ->add('competition', EnumType::class, ['class' => Competition::class])
            ->add('team', EnumType::class, [
                'class' => Team::class,
                'placeholder' => "-- choose team --",
                'label' => 'Age group'
            ])
            ->add('club', EntityType::class, [
                'label' => 'Opponent/Training',
                'class' => Club::class,
                'required' => false,
                'choice_label' => 'name',
                'placeholder' => "Training",
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
