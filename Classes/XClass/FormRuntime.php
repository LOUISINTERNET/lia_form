<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\XClass;

class FormRuntime extends \TYPO3\CMS\Form\Domain\Runtime\FormRuntime
{
    public function setValue($identifier, $value): void
    {
        $this->formState->setFormValue($identifier, $value);
    }
}
