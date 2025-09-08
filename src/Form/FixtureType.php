<?php

namespace App\Form;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Club;
use App\Entity\Fixture;
use App\Repository\ClubRepository;
use App\Service\DirectusClient;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
    private DirectusClient $directus;

    public function __construct(DirectusClient $directus)
    {
        $this->directus = $directus;
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // fetch match reports from Directus
        $reports = $this->directus->fetchCollection('matchReports', [
            'fields' => 'id,slug,title,createdAt',
            'sort'   => '-createdAt',
        ]);

        $matchReportsOptions = [];
        foreach ($reports as $report) {
            $date = new DateTime($report['createdAt']);
            $optionLabel = sprintf(
                "%s (%s)",
                $report['title'],
                $date->format('d/m/Y')
            );
            $matchReportsOptions[$optionLabel] = $report['slug'];
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'Display Name',
                'help' => 'This is the text displayed in the fixture list. If you leave this blank, the name will be generated from the fixture, e.g. Beccles (A).',
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
                'label' => 'Team/Age group (Norwich)',
                'choice_label' => fn (Team $team) => $team->value,
            ])
            ->add('club', EntityType::class, [
                'label' => 'Opponent/Training partner',
                'class' => Club::class,
                'required' => false,
                'choice_label' => 'name',
                'placeholder' => 'N/A',
                'query_builder' => function (ClubRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('LOWER(c.name)', 'ASC');
                },
            ])
            ->add('opponent', EnumType::class, [
                'class' => Team::class,
                'placeholder' => '-- choose team --',
                'label' => 'Opposing team',
                'choice_label' => fn (Team $team) => $team->value,
                'required' => false,
                'help' => 'For youth fixtures you can leave this blank.',
            ])
            ->add('matchReportExternalId', ChoiceType::class, [
                'label' => 'Match Report',
                'choices' => $matchReportsOptions,
                'placeholder' => '-- choose report --',
                'required' => false,
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
