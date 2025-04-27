<?php

namespace App\Form;

use App\Entity\Club;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamVisibilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $teams = $options['teams'];
        $data = $options['data'];

        $builder
            ->add('teams', ChoiceType::class, [
                'label' => 'Select teams to display ≡',
                'choices' => $teams,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'data' => $data,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'teams' => [], // Default empty array
        ]);
    }
}
