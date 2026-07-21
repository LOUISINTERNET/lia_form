<?php

/*
 * This file is part of the "LIA Form" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace LIA\LiaForm\Domain\Model\FormElements;

use LIA\LiaForm\Event\BeforePhoneAreaCodeInitializeEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractFormElement;

/**
 * Form element for phone number with area code selection.
 *
 * Loads area codes from JSON configuration and allows event-based
 * customization of the data source.
 *
 * Note: This class uses GeneralUtility::makeInstance() for EventDispatcher because
 * Form Framework form elements are instantiated via the form factory without DI support.
 * This is a documented TYPO3 Form Framework limitation.
 *
 * @author LOUIS INTERNET <devs@louis.info>
 */
class PhoneAndAreaCodeElement extends AbstractFormElement
{
    /**
     * Initialize the form element.
     *
     * Loads phone area codes from JSON file and sets them as options.
     */
    public function initializeFormElement(): void
    {
        $dispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        $event = new BeforePhoneAreaCodeInitializeEvent(
            'EXT:lia_form/Configuration/Data/PhoneAreaCodeList.json'
        );
        $dispatcher->dispatch($event);
        $dataSourcePath = $event->getDataSourcePath();

        // Security: Only EXT: paths are allowed to prevent path traversal attacks
        if (!str_starts_with($dataSourcePath, 'EXT:')) {
            throw new \InvalidArgumentException(
                'Only EXT: paths are allowed for dataSourcePath to prevent path traversal. Got: ' . $dataSourcePath,
                1709391234
            );
        }

        $dataSourcePath = GeneralUtility::getFileAbsFileName($dataSourcePath);

        $jsonContent = file_get_contents($dataSourcePath);
        if ($jsonContent === false) {
            return;
        }

        $data = json_decode($jsonContent, true);
        if (is_array($data)) {
            $this->setProperty('options', $data);
        }
    }
}
