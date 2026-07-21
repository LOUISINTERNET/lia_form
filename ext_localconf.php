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
use LIA\LiaForm\XClass\FormRuntime;
use TYPO3\CMS\Form\Domain\Model\FormDefinition as CoreFormDefinition;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime as CoreFormRuntime;

defined('TYPO3') || die();

// Note: Scheduler task registration moved to TCA (Configuration/TCA/Overrides/tx_scheduler_task.php)
// per TYPO3 14 Deprecation #98453. The old SC_OPTIONS approach is deprecated.

// Default implementation doesn't provide API to remove processing rules.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][CoreFormDefinition::class] = [
    'className' => FormDefinition::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][CoreFormRuntime::class] = [
    'className' => FormRuntime::class,
];

// DataHandler hook to modify form flexform.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = FlexFormHook::class;

// Note: Form Framework hooks removed in TYPO3 v14.
// Event listeners registered via #[AsEventListener] PHP attributes in Classes/EventListener/:
// - AfterInitializeCurrentPageEventListener (replaces afterInitializeCurrentPage hook)
// - AfterFormSubmitEventListener (replaces afterSubmit hook)
// - BeforeFormElementCreatedEventListener (replaces initializeFormElement hook)
