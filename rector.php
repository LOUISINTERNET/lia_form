<?php

/**
 * Rector configuration for TYPO3 v14 extension upgrade
 *
 * Usage:
 *   ./.Build/bin/rector process --dry-run  # Preview changes
 *   ./.Build/bin/rector process            # Apply changes
 *
 * Requires: composer require --dev ssch/typo3-rector:^3.11
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\PostRector\Rector\NameImportingPostRector;
use Rector\Set\ValueObject\LevelSetList;
use Ssch\TYPO3Rector\CodeQuality\General\GeneralUtilityMakeInstanceToConstructorPropertyRector;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Ssch\TYPO3Rector\Set\Typo3SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/Classes',
        __DIR__ . '/Configuration',
        __DIR__ . '/ext_localconf.php',
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
    $rectorConfig->removeUnusedImports();

    // Define what rule sets will be applied
    $rectorConfig->sets([
        // PHP level upgrades (TYPO3 v14 minimum is PHP 8.2)
        LevelSetList::UP_TO_PHP_82,

        // Extension targets TYPO3 v14 only (composer: typo3/cms-core ^14.3)
        Typo3LevelSetList::UP_TO_TYPO3_14,

        // TYPO3 code quality and general improvements
        Typo3SetList::CODE_QUALITY,
        Typo3SetList::GENERAL,
    ]);

    $rectorConfig->skip([
        __DIR__ . '/ext_emconf.php',
        __DIR__ . '/.Build',
        __DIR__ . '/vendor',
        __DIR__ . '/__EXAMPLE__',

        // Skip constructor promotion - keep explicit property declarations for clarity
        ClassPropertyAssignToConstructorPromotionRector::class,

        // Skip removing parent calls - may be needed for TYPO3 hooks
        RemoveParentCallWithoutParentRector::class,

        // Don't import names in ext_localconf.php — conditionally-loaded classes
        // must stay as FQCN inside isLoaded() guards
        NameImportingPostRector::class => [__DIR__ . '/ext_localconf.php'],

        // These classes are instantiated via GeneralUtility::makeInstance() from
        // non-DI contexts (DataHandler hook, finisher created in
        // FormDefinition::createFinisher()) — constructor injection would cause
        // ArgumentCountError at runtime
        GeneralUtilityMakeInstanceToConstructorPropertyRector::class => [
            __DIR__ . '/Classes/Hooks/FlexFormHook.php',
            __DIR__ . '/Classes/Finisher/EmailFinisher.php',
            // Adding a constructor here would redeclare the parent's promoted
            // readonly properties (PHP fatal) — parent ctor is autowired instead
            __DIR__ . '/Classes/Preview/FormPreviewRenderer.php',
        ],
    ]);
};
