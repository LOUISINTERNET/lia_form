<?php

/*
 * This file is part of the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

namespace LIA\LiaForm\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This ViewHelper replace a substring of a give content by given string.
 * This ViewHelper is used in the DataProtection form field to set the link to the configured privacy page.
 *
 * Example
 * =======
 *
 * .. code-block:: html
 *
 *      {f:link.typolink(parameter: element.renderingOptions.pageUid) -> f:variable(name: 'link')} {formvh:translateElementProperty(element: element, property: 'label') -> f:variable(name: 'translation')}
 *      <f:format.raw>
 *        <liaform:format.replace substring="%s" content="{translation}" replacement="{link}" />
 *      </f:format.raw>
 */
class ReplaceViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('content', 'string', 'Content in which to perform replacement');
        $this->registerArgument('substring', 'string', 'Substring to replace', true);
        $this->registerArgument('replacement', 'string', 'Replacement to insert', false, '');
        $this->registerArgument('count', 'integer', 'Maximum number of times to perform replacement');
        $this->registerArgument('caseSensitive', 'boolean', 'If true, perform case-sensitive replacement', false, true);
    }

    /**
     * @return mixed
     */
    public function render()
    {
        $content = empty($this->arguments['content']) ? $this->renderChildren() : $this->arguments['content'];
        $substring = $this->arguments['substring'];
        $replacement = $this->arguments['replacement'];
        $count = (int)$this->arguments['count'];
        $caseSensitive = (bool)$this->arguments['caseSensitive'];
        $function = ($caseSensitive ? 'str_replace' : 'str_ireplace');
        return $function($substring, $replacement, $content, $count);
    }
}
