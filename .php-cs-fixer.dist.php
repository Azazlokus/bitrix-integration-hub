<?php

declare(strict_types=1);

// PER-CS 3.0 — актуальный преемник PSR-12 от PHP-FIG (ТЗ требует PSR-12; PER-CS его включает).
$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/local/modules',
        __DIR__ . '/local/php_interface',
        __DIR__ . '/tools',
        __DIR__ . '/tests',
        __DIR__ . '/mock-services',
    ])
    ->append([__FILE__])
    ->exclude(['vendor']);

return new PhpCsFixer\Config()
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')
    ->setRules([
        '@PER-CS3x0' => true,
        '@PER-CS3x0:risky' => true,
        '@PHP8x4Migration' => true,
        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'global_namespace_import' => ['import_classes' => true, 'import_functions' => false, 'import_constants' => false],
        'final_class' => true,
        'void_return' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
    ])
    ->setFinder($finder);
