<?php

$finder = Symfony\Component\Finder\Finder::create()
    ->notPath('vendor')
    ->notPath('cache')
    ->notPath('logs')
    ->name(['*.php', '*.twig']);

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR12'                     => true,
        'array_syntax'               => ['syntax' => 'short'],
        'ordered_imports'            => ['sortAlgorithm' => 'alpha'],
        'no_unused_imports'          => true,
        'no_useless_else'            => true,
        'no_useless_return'          => true,
        'blank_line_after_namespace' => true,
        'elseif'                     => true,
        'encoding'                   => true,
        'binary_operator_spaces'     => [
            'align_double_arrow' => true,
            'align_equals'       => true
        ],
    ])
    ->setFinder($finder);
