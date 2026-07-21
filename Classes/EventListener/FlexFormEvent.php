<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\EventListener;

use TYPO3\CMS\Core\Configuration\Event\AfterFlexFormDataStructureParsedEvent;

/**
 * This Event modify the form FlexForm by adding a field for a custom css class.
 */
class FlexFormEvent
{
    public function __invoke(AfterFlexFormDataStructureParsedEvent $event): void
    {
        $dataStructure = $event->getDataStructure();
        $identifier = $event->getIdentifier();

        if ($identifier['tableName'] === 'tt_content' && $identifier['fieldName'] === 'pi_flexform' && $identifier['dataStructureKey'] === '*,form_formframework') {
            $sheetElements = &$dataStructure['sheets']['sDEF']['ROOT']['el'];
            $sheetElements['settings.cssClass'] = [
                'label' => 'LLL:EXT:lia_form/Resources/Private/Language/locallang_db.xlf:tt_content.pi_flexform.formframework.cssClass',
                'config' => [
                    'type' => 'input',
                ],
            ];
        }

        $event->setDataStructure($dataStructure);
    }
}
