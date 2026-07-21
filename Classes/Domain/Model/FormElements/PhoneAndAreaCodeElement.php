<?php

declare(strict_types=1);

namespace LIA\LiaForm\Domain\Model\FormElements;

use LIA\LiaForm\Event\BeforePhoneAreaCodeInitializeEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractFormElement;

class PhoneAndAreaCodeElement extends AbstractFormElement
{
    public function initializeFormElement()
    {
        $dispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        $event = new BeforePhoneAreaCodeInitializeEvent('EXT:lia_form/Configuration/Data/PhoneAreaCodeList.json');
        $dispatcher->dispatch($event);
        $dataSourcePath = $event->getDataSourcePath();

        if (str_starts_with($dataSourcePath, 'EXT')) {
            $dataSourcePath = GeneralUtility::getFileAbsFileName($dataSourcePath);
        }
        $data = json_decode(file_get_contents($dataSourcePath), true);

        $this->setProperty('options', $data);
    }
}
