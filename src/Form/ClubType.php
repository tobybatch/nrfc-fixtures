<?php

namespace App\Form;

use App\Entity\Club;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Club>
 */
class ClubType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('address', TextareaType::class, [
                'required' => false,
            ])
            ->add('latitude', NumberType::class, [
                'required' => false,
                'scale' => 8, // optional: number of decimal places
            ])
            ->add('longitude', NumberType::class, [
                'required' => false,
                'scale' => 8,
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
            ])
            ->add('aliases', TextareaType::class, [
                'required' => false,
                'label' => 'Aliases (one per line)',
            ])
        ;

        $builder->get('aliases')->addModelTransformer(new CallbackTransformer(
            function (?array $aliases): string {
                return $aliases ? implode("\n", $aliases) : '';
            },
            // reverse transform string -> array (after submitting form)
            function (string $value): array {
                $lines = preg_split('/\r\n|\r|\n/', $value);
                return array_values(array_filter(array_map('trim', $lines)));
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Club::class,
        ]);
    }
}
