<?php

/*
 * This file is part of the "LIA Form" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
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
    /**
     * Escape output to prevent XSS vulnerabilities.
     *
     * @var bool
     */
    protected $escapeOutput = true;

    /**
     * Initialize arguments.
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('content', 'string', 'Content in which to perform replacement');
        $this->registerArgument('substring', 'string', 'Substring to replace', true);
        $this->registerArgument('replacement', 'string', 'Replacement to insert', false, '');
        $this->registerArgument('count', 'int', 'Maximum number of times to perform replacement');
        $this->registerArgument('caseSensitive', 'boolean', 'If true, perform case-sensitive replacement', false, true);
    }

    /**
     * Render the view helper.
     *
     * @return string The content with replacements applied
     */
    public function render(): string
    {
        $content = empty($this->arguments['content']) ? $this->renderChildren() : $this->arguments['content'];
        $substring = $this->arguments['substring'];
        $replacement = $this->arguments['replacement'];
        $count = (int)($this->arguments['count'] ?? 0);
        $caseSensitive = (bool)$this->arguments['caseSensitive'];

        if ($caseSensitive) {
            return str_replace($substring, $replacement, $content, $count);
        }

        return str_ireplace($substring, $replacement, $content, $count);
    }
}
