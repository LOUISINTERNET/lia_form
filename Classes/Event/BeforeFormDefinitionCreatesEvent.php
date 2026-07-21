<?php

/*
 * This file is part of the "LIA Form" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

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
     * Create event instance.
     *
     * @param array<string, mixed> $formDefinitionConfigArray The form configuration array
     * @param ServerRequestInterface $request The current server request
     * @param int $renderedForms Count of forms rendered in this request
     */
    public function __construct(
        private array $formDefinitionConfigArray,
        private readonly ServerRequestInterface $request,
        private readonly int $renderedForms
    ) {}

    /**
     * Get the form definition configuration array.
     *
     * @return array<string, mixed> The form configuration
     */
    public function getFormDefinitionConfigArray(): array
    {
        return $this->formDefinitionConfigArray;
    }

    /**
     * Set the form definition configuration array.
     *
     * @param array<string, mixed> $newFormDefinitionConfigArray The new form configuration
     */
    public function setFormDefinitionConfigArray(array $newFormDefinitionConfigArray): void
    {
        $this->formDefinitionConfigArray = $newFormDefinitionConfigArray;
    }

    /**
     * Get the server request interface.
     *
     * @return ServerRequestInterface The current request
     */
    public function getServerRequestInterface(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Get the count of rendered forms.
     *
     * @return int Number of forms rendered in this request
     */
    public function getRenderedForms(): int
    {
        return $this->renderedForms;
    }
}
