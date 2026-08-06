<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Hooks;

use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * This class contains Hooks of the FormRuntime class.
 */
class FormRuntimeHooks
{
    /**
     * This hook is used to modify form values.
     */
    public function afterSubmit(FormRuntime $formRuntime, RenderableInterface $renderable, $elementValue, array $requestArguments = [])
    {
        if ($renderable->getType() !== 'PhoneAndAreaCode') {
            return $elementValue;
        }

        $parsedBody = $formRuntime->getRequest()->getParsedBody();
        $areaCodeIdentifier = $renderable->getIdentifier() . '-areaCode';

        // Validate and sanitize area code input to prevent injection attacks
        $rawAreaCode = '';
        if (is_array($parsedBody) && isset($parsedBody['tx_form_formframework'][$areaCodeIdentifier])) {
            $rawAreaCode = $parsedBody['tx_form_formframework'][$areaCodeIdentifier];
        }

        // Limit length to prevent abuse
        $areaCode = substr(preg_replace('/[^0-9+\-() ]/', '', (string)$rawAreaCode), 0, 20);

        if ($areaCode === '') {
            return $elementValue;
        }

        return $areaCode . ' ' . $elementValue;
    }
}
