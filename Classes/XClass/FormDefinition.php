<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\XClass;

use TYPO3\CMS\Form\Domain\Model\FormDefinition as CoreFormDefinition;

/**
 * Extended FormDefinition with processing rule removal.
 *
 * Provides API to remove processing rules that the default implementation lacks.
 *
 * @author Onur Güngören <guengoeren@louis.info>, LOUIS INTERNET
 */
class FormDefinition extends CoreFormDefinition
{
    /**
     * Remove a processing rule by identifier.
     *
     * @param string $identifier The processing rule identifier to remove
     */
    public function removeProcessingRule(string $identifier): void
    {
        if (isset($this->processingRules[$identifier])) {
            unset($this->processingRules[$identifier]);
        }
    }
}
