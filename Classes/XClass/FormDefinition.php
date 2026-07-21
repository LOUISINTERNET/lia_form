<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\XClass;

class FormDefinition extends \TYPO3\CMS\Form\Domain\Model\FormDefinition
{
    public function removeProcessingRule($identifier): void
    {
        if (isset($this->processingRules[$identifier])) {
            unset($this->processingRules[$identifier]);
        }
    }
}
