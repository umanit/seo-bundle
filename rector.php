<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig
    ::configure()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        earlyReturn: true,
        strictBooleans: true,
    )
    ->withAttributesSets(symfony: true, doctrine: true, sensiolabs: true)
    ->withPhpSets()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()
    ->withImportNames(importShortClasses: false, removeUnusedImports: true)
;
