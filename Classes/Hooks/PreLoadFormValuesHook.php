<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Hooks;

use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;

/**
 * Preload Form
 * Class PreLoadFormValuesHook
 */
class PreLoadFormValuesHook
{
    public function initializeFormElement(RenderableInterface $renderable): void
    {
        if ($renderable->getType() === 'LiaSiteTitle' && $renderable instanceof FormElementInterface) {
            $currentTitle = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.page.information')->getPageRecord()['title'];
            $renderable->setDefaultValue($currentTitle);
        }
    }
}
