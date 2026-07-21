<?php

/*
 * This file is part of the "LIA Form" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

use LIA\LiaForm\Tasks\ClearFolderTask;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

if (isset($GLOBALS['TCA']['tx_scheduler_task'])) {
    // Add custom field for hours to live
    ExtensionManagementUtility::addTCAcolumns(
        'tx_scheduler_task',
        [
            'lia_form_hours_to_live' => [
                'label' => 'LLL:EXT:lia_form/Resources/Private/Language/locallang_db.xlf:scheduler.clearFolderTask.hoursToLive',
                'description' => 'LLL:EXT:lia_form/Resources/Private/Language/locallang_db.xlf:scheduler.clearFolderTask.hoursToLive.description',
                'config' => [
                    'type' => 'number',
                    'size' => 10,
                    'required' => true,
                    'default' => 1,
                    'range' => [
                        'lower' => 1,
                    ],
                ],
            ],
        ]
    );

    // Register the ClearFolderTask type
    ExtensionManagementUtility::addRecordType(
        [
            'label' => 'LLL:EXT:lia_form/Resources/Private/Language/locallang_db.xlf:scheduler.clearFolderTask.title',
            'description' => 'LLL:EXT:lia_form/Resources/Private/Language/locallang_db.xlf:scheduler.clearFolderTask.description',
            'value' => ClearFolderTask::class,
            'icon' => 'mimetypes-x-tx_scheduler_task_group',
            'group' => 'lia_form',
        ],
        '
        --div--;core.tabs:general,
            tasktype,
            task_group,
            description,
            lia_form_hours_to_live,
        --div--;scheduler.messages:scheduler.form.palettes.timing,
            execution_details,
            nextexecution,
            --palette--;;lastexecution,
        --div--;core.tabs:access,
            disable,
        --div--;core.tabs:extended,',
        [],
        '',
        'tx_scheduler_task'
    );
}
