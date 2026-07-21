<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Tasks;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\SchedulerManagementAction;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * This class extends the Scheduler view by an addition file.
 * This field set the deprecation of the files for the CleanFolderTask in days.
 */
class ClearFolderAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    /**
     * Addition field configuration.
     *
     * @param array $additionalFieldsConfig
     */
    protected array $additionalFieldsConfig = [
        'liaHoursToLive' => [
            'label' => 'Delete file after X hours',
            'defaultVal' => 1,
            'cshKey' => '_MOD_system_txschedulerM1',
            'fieldType' => 'number',
        ],
        'liaFolderToClear' => [
            'label' => 'The folder to clear',
            'defaultVal' => 'formuploads',
            'cshKey' => '_MOD_system_txschedulerM2',
            'fieldType' => 'input',
        ],
    ];

    /**
     * This function set the addition field in view of the scheduler.
     *
     * @param $task
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $additionalFields               = [];
        $currentSchedulerModuleAction   = $schedulerModule->getCurrentAction();

        foreach ($this->additionalFieldsConfig as $fieldName => $fieldConfig) {
            // Initialize extra field value
            if (empty($taskInfo[$fieldName])) {
                if ($currentSchedulerModuleAction === SchedulerManagementAction::ADD) {
                    // In case of new task and if field is empty, set default email address
                    $taskInfo[$fieldName] = $fieldConfig['defaultVal'];
                } elseif ($currentSchedulerModuleAction === SchedulerManagementAction::EDIT) {
                    // In case of edit, and editing a test task, set to internal value if not data was submitted already
                    $taskInfo[$fieldName] = $task->$fieldName;
                } else {
                    // Otherwise set an empty value, as it will not be used anyway
                    $taskInfo[$fieldName] = '';
                }
            }

            // Write the code for the field
            // $fieldID = 'lia_hoursToLive';
            $fieldCode = '<input type="' . $fieldConfig['fieldType'] . '" class="form-control" name="tx_scheduler[' . $fieldName . ']" id="' . $fieldName . '" value="' . htmlspecialchars((string)$taskInfo[$fieldName]) . '">';

            $additionalFields[$fieldName] = [
                'code' => $fieldCode,
                'label' => $fieldConfig['label'],
                'cshKey' => $fieldConfig['cshKey'],
                'cshLabel' => $fieldName,
            ];
        }

        return $additionalFields;
    }

    /**
     * Validate the given value of the custom field.
     *
     * @param array $submittedData
     * @param SchedulerModuleController $parentObject
     *
     * @return bool
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $parentObject)
    {
        $liaDaysToLive = (int)(trim((string)$submittedData['liaHoursToLive']));
        $liaFolderToClear = (trim((string)$submittedData['liaFolderToClear']));

        if ($liaDaysToLive <= 0) {
            $this->ErrorMessage(
                'Please enter a count of hours.',
                'ERROR',
                ContextualFeedbackSeverity::ERROR
            );
            return false;
        }

        if (!is_dir(Environment::getPublicPath() . '/typo3temp/' . $liaFolderToClear)) {
            $this->ErrorMessage(
                'The given folder does not exist in typo3temp.',
                'ERROR',
                ContextualFeedbackSeverity::ERROR
            );
            return false;
        }

        return true;
    }

    /**
     * Save the given value in the current task.
     *
     * @param array $submittedData
     * @param AbstractTask $task
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task): void
    {
        foreach ($this->additionalFieldsConfig as $fieldName => $fieldConfig) {
            $task->$fieldName = $submittedData[$fieldName];
        }
    }

    /**
     * Adds an error flash message to queue.
     *
     * @param string $description
     * @param string $title
     * @param ContextualFeedbackSeverity $messageStatus
     */
    private function ErrorMessage(string $description = '', string $title = '', ContextualFeedbackSeverity $messageStatus = ContextualFeedbackSeverity::OK): void
    {
        /* @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            (string)$description,
            (string)$title,
            $messageStatus
        );

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->enqueue($flashMessage);
    }
}
