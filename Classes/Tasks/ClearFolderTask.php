<?php

/*
 * This file is part of the "LIA Form" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace LIA\LiaForm\Tasks;

use LIA\LiaForm\Services\ClearFolderService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Scheduler task for cleaning deprecated files from a folder.
 *
 * Uses the ClearFolderService for actual deletion. The deprecation
 * time can be set in the Scheduler module in the backend.
 *
 * TYPO3 14: Uses TCA-based task registration via tx_scheduler_task TCA override.
 * The AdditionalFieldProvider pattern is deprecated - fields are now defined in TCA.
 *
 * @author Johannes Delesky <delesky@louis.info>
 */
class ClearFolderTask extends AbstractTask
{
    /**
     * Service for folder clearing operations.
     *
     * @var ClearFolderService|null
     */
    private ?ClearFolderService $clearFolderService = null;

    /**
     * Hours after which files are considered deprecated.
     *
     * @var int
     */
    protected int $hoursToLive = 1;

    /**
     * Execute the scheduler task.
     *
     * @return bool True if task execution was successful
     */
    public function execute(): bool
    {
        $this->initializeClassAttributes();

        if ($this->clearFolderService === null) {
            return false;
        }

        return $this->clearFolderService->recursiveDelete();
    }

    /**
     * Initialize the class attributes.
     *
     * Note: Uses GeneralUtility::makeInstance() because Scheduler tasks are
     * serialized and unserialized without DI container support.
     */
    private function initializeClassAttributes(): void
    {
        $this->clearFolderService = GeneralUtility::makeInstance(
            ClearFolderService::class,
            $this->hoursToLive
        );
    }

    /**
     * Return current field values as an associative array.
     *
     * Used during migration from old serialized tasks and for task information display.
     *
     * @return array<string, mixed> Task parameters
     */
    public function getTaskParameters(): array
    {
        return [
            'lia_form_hours_to_live' => $this->hoursToLive,
        ];
    }

    /**
     * Set field values from an associative array.
     *
     * Handles both old parameter names (lia_hoursToLive) and new TCA field names
     * for migration compatibility.
     *
     * @param array<string, mixed> $parameters Values from TCA fields or legacy serialized data
     */
    public function setTaskParameters(array $parameters): void
    {
        // Migration: Support both old property name and new TCA field name
        $this->hoursToLive = (int)(
            $parameters['lia_hoursToLive']
            ?? $parameters['lia_form_hours_to_live']
            ?? 1
        );
    }

    /**
     * Validate task parameters.
     *
     * Basic 'required' validation is handled by TCA configuration.
     * This method performs additional business logic validation.
     *
     * @param array<string, mixed> $parameters Parameters to validate
     * @return bool True if validation passed
     */
    public function validateTaskParameters(array $parameters): bool
    {
        $hoursToLive = (int)($parameters['lia_form_hours_to_live'] ?? 0);

        // Hours must be at least 1
        return $hoursToLive >= 1;
    }

    /**
     * Get additional information about this task for display in the scheduler module.
     *
     * @return string Human-readable task information
     */
    public function getAdditionalInformation(): string
    {
        return sprintf('Delete files older than %d hour(s)', $this->hoursToLive);
    }
}
