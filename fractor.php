<?php

/**
 * Fractor configuration for TYPO3 v13 extension upgrade
 *
 * Fractor handles non-PHP file migrations:
 * - FlexForms (XML)
 * - TypoScript
 * - YAML (e.g., Services.yaml)
 * - Fluid templates
 * - .htaccess files
 *
 * Usage:
 *   ./vendor/bin/fractor process --dry-run  # Preview changes
 *   ./vendor/bin/fractor process            # Apply changes
 *
 * NOTE: Fractor does NOT support --config flag.
 *       It auto-discovers fractor.php in the current working directory.
 *       Place this file in the extension root and run from there.
 *
 * Requires: composer require --dev a9f/typo3-fractor:^0.5
 *
 * @see https://github.com/andreaswolf/fractor
 */

declare(strict_types=1);

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\FractorTypoScript\Configuration\TypoScriptProcessorOption;
use a9f\Typo3Fractor\Set\Typo3LevelSetList;

return FractorConfiguration::configure()
    ->withPaths([
        __DIR__ . '/Configuration',
        __DIR__ . '/Resources',
        __DIR__ . '/ext_typoscript_setup.typoscript',
    ])
    ->withSkip([
        __DIR__ . '/.Build',
        __DIR__ . '/vendor',
    ])
    ->withSets([
        // Extension targets TYPO3 v13 only (composer: typo3/cms-core ^13.4)
        Typo3LevelSetList::UP_TO_TYPO3_13,
    ])
    ->withOptions([
        // Preserve indentation inside TypoScript conditions
        TypoScriptProcessorOption::INDENT_CONDITIONS => true,
    ]);
