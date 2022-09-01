<?php

$finder = Symfony\Component\Finder\Finder::create()
    ->notPath('vendor')
    ->name(['*.php']);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12'                     => true,
    '@PHP80Migration'            => true,
    'array_syntax'               => ['syntax' => 'short'],
    'ordered_imports'            => ['sort_algorithm' => 'alpha'],
    'no_unused_imports'          => true,
    'no_useless_else'            => true,
    'no_useless_return'          => true,
    'blank_line_after_namespace' => true,
    'elseif'                     => true,
    'encoding'                   => true,
    'no_empty_statement'         => true,
    'binary_operator_spaces'     => [
        'operators' => [
            '=>' => 'align',
            '='  => 'align'
        ]
    ]
])->setFinder($finder);
