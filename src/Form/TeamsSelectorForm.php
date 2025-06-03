<?php

namespace App\Form;

use App\Config\Team;
use App\Service\PreferencesService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamsSelectorForm extends AbstractType
{
    private PreferencesService $preferencesService;

    public function __construct(PreferencesService $preferencesService)
    {
        $this->preferencesService = $preferencesService;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = array_reduce(Team::cases(), function ($carry, $enum) {
            $carry[$enum->name] = $enum->value;
            return $carry;
        }, []);

        $teams = json_decode(
            $this->preferencesService->getPreferences()['teamsSelected'] ?? '[]',
            true
        );

        $builder
            ->add('teams', ChoiceType::class, [
                'choices' => $choices,
                'expanded' => true,
                'multiple' => true,
                'label' => 'Show which teams?',
                'data' => $teams,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
