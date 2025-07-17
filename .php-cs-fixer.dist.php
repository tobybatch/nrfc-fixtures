<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

//return Config::create()
//    ->setRiskyAllowed(true)
//    ->setRules([
//        '@PSR12' => true,
//        'array_syntax' => ['syntax' => 'short'],
//        'binary_operator_spaces' => ['default' => 'single_space'],
//    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['default' => 'single_space'],

    ])
    ->setFinder($finder)
;
