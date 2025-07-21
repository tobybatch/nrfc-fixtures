<?php

// src/Form/CsvUploadType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class CsvUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('csv', FileType::class, [
            'label' => 'Upload CSV File',
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => [
                        'text/plain',
                        'text/csv',
                        'application/vnd.ms-excel',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid CSV file',
                ])
            ],
        ]);
    }
}
