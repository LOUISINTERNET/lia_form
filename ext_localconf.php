<?php

/*
 * This file is part of the "LIA Form" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

use LIA\LiaForm\Hooks\FlexFormHook;
use LIA\LiaForm\XClass\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormDefinition as CoreFormDefinition;

defined('TYPO3') || die();

// Note: Scheduler task registration moved to TCA (Configuration/TCA/Overrides/tx_scheduler_task.php)
// per TYPO3 14 Deprecation #98453. The old SC_OPTIONS approach is deprecated.

// Default implementation doesn't provide API to remove processing rules.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][CoreFormDefinition::class] = [
    'className' => FormDefinition::class,
];

// DataHandler hook to modify form flexform.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = FlexFormHook::class;

// Note: Form Framework hooks were removed in TYPO3 v14. Their replacements live in
// Classes/EventListener/, registered via #[AsEventListener] attributes.
