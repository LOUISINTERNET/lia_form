<?php

declare(strict_types=1);

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Event;

use Psr\Http\Message\ServerRequestInterface;

/**
 * This event is dispatched in the ExtendedArrayFormFactory and provide the possibility
 * to manipulate the form configuration before FormDefinition class is created.
 */
final class BeforeFormDefinitionCreatesEvent
{
    /**
     * Event constructor.
     */
    public function __construct(
        private array $formDefintionConfigArray,
        private readonly ServerRequestInterface $request,
        private readonly int $renderedForms
    ) {}

    /**
     * Returns the formDefintionConfigArray
     */
    public function getFormDefinitionConfigArray(): array
    {
        return $this->formDefintionConfigArray;
    }

    /**
     * Set the formDefinitionConfigArray
     */
    public function setFormDefinitionConfigArray(array $newFormDefinitionConfigArray): void
    {
        $this->formDefintionConfigArray = $newFormDefinitionConfigArray;
    }

    /**
     * Return the ServerRequestInterface
     */
    public function getServerRequestInterface(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Returns the count of the rendered forms.
     */
    public function getRenderedForms(): int
    {
        return $this->renderedForms;
    }
}
