<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\XClass;

use TYPO3\CMS\Form\Domain\Runtime\FormRuntime as CoreFormRuntime;

/**
 * Extended FormRuntime with additional value setter.
 *
 * Provides public access to set form values programmatically.
 *
 * @author LOUIS INTERNET <devs@louis.info>
 */
class FormRuntime extends CoreFormRuntime
{
    /**
     * Set a form value by identifier.
     *
     * Note: The $value parameter uses mixed type because form values can be
     * any scalar type (string, int, bool), arrays, or null. This matches
     * the TYPO3 Core FormState::setFormValue() signature.
     *
     * @param string $identifier The form element identifier
     * @param string|int|bool|array<mixed>|null $value The value to set
     */
    public function setValue(string $identifier, mixed $value): void
    {
        $this->formState->setFormValue($identifier, $value);
    }
}
