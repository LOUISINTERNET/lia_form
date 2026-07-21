<?php

declare(strict_types=1);

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Tasks;

use LIA\LiaForm\Services\ClearFolderService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * This task cleans folder from deprecated files, this class uses the CleanFolderService for it.
 * Thee Deprecation can be set in the Scheduler Module in the BE.
 */
class ClearFolderTask extends AbstractTask
{
    /**
     * @var ClearFolderService
     */
    protected $clearFolderService;

    /**
     * @var int $liaHoursToLive this variable should be set by scheduler in BE
     */
    public int $liaHoursToLive;

    /**
     * @var string $liaFolderToClear the of the folder to clean up.
     */
    public string $liaFolderToClear;

    /**
     * Execute this task.
     */
    public function execute(): bool
    {
        $this->initializeClassAttributes();

        return $this->clearFolderService->recursiveDelete();
    }

    /**
     * Initialize the class attributes.
     */
    private function initializeClassAttributes(): void
    {
        $this->clearFolderService = GeneralUtility::makeInstance(
            ClearFolderService::class,
            $this->liaHoursToLive,
            $this->liaFolderToClear
        );
    }
}
