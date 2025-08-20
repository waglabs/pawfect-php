<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
        ->withPaths([
                __DIR__ . '/examples',
                __DIR__ . '/src',
                __DIR__ . '/tests',
        ])
        // uncomment to reach your current PHP version
        ->withPhpSets(
                php84: true,
        )
        ->withPhpVersion(\Rector\ValueObject\PhpVersion::PHP_82)
        ->withTypeCoverageLevel(10)
        ->withDeadCodeLevel(10)
        ->withCodeQualityLevel(10);
