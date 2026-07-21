<?php

use LIA\LiaForm\Hooks\FlexFormHook;
use LIA\LiaForm\Hooks\FormRuntimeHooks;
use LIA\LiaForm\Hooks\PreLoadFormValuesHook;
use LIA\LiaForm\Tasks\ClearFolderAdditionalFieldProvider;
use LIA\LiaForm\Tasks\ClearFolderTask;

defined('TYPO3') || die();

(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][ClearFolderTask::class] = [
        'extension' => 'lia_form',
        'title' => 'LiaForm: ClearFolderTask',
        'description' => 'Deletes file in given folder.',
        'additionalFields' => ClearFolderAdditionalFieldProvider::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Form\Domain\Finishers\EmailFinisher::class] = [
        'className' => \LIA\LiaForm\Finisher\EmailFinisher::class,
    ];

    // default implementation doesn't provide API to remove processing rules
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Form\Domain\Model\FormDefinition::class] = [
        'className' => \LIA\LiaForm\XClass\FormDefinition::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Form\Domain\Runtime\FormRuntime::class] = [
        'className' => \LIA\LiaForm\XClass\FormRuntime::class,
    ];

    //add hook to get predefined values from database to form
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['initializeFormElement']['preFillJobs'] = PreLoadFormValuesHook::class;

    // add hook to modify form flexform
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = FlexFormHook::class;

    // Register afterInitializeCurrentPage hook from FormRuntime
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterInitializeCurrentPage'][] = FormRuntimeHooks::class;

    // Register afterSubmit hook from FormRuntime
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'][1756115713] = FormRuntimeHooks::class;
})();
