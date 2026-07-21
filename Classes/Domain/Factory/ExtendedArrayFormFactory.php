<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

declare(strict_types=1);

namespace LIA\LiaForm\Domain\Factory;

use LIA\LiaForm\Event\BeforeFormDefinitionCreatesEvent;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Form\Domain\Exception\RenderingException;
use TYPO3\CMS\Form\Domain\Factory\ArrayFormFactory;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;

/**
 * This Factory build the FormDefinition object and returns it.
 * It also dispatch an event to manipulate the form configuration.
 *
 * Note: EventDispatcher is injected via parent class AbstractFormFactory::injectEventDispatcher().
 * This class uses the inherited $this->eventDispatcher property.
 */
class ExtendedArrayFormFactory extends ArrayFormFactory
{
    /**
     * Count the rendered forms on a request.
     */
    private static int $renderedForms = 0;

    /**
     * Build a form definition by given configuration array.
     *
     * @param array<string, mixed> $configuration The form configuration
     * @throws RenderingException
     * @throws \RuntimeException If no request is available
     */
    public function build(
        array $configuration,
        ?string $prototypeName = null,
        ?ServerRequestInterface $request = null
    ): FormDefinition {
        self::$renderedForms++;

        // Use provided request or fall back to GLOBALS (only in edge cases)
        $serverRequest = $request ?? $this->getServerRequestFromGlobals();

        if ($this->eventDispatcher === null) {
            throw new \RuntimeException(
                'EventDispatcher not injected. Ensure DI is configured correctly.',
                1709391236
            );
        }

        /** @var BeforeFormDefinitionCreatesEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new BeforeFormDefinitionCreatesEvent(
                $configuration,
                $serverRequest,
                self::$renderedForms
            )
        );

        $configuration = $event->getFormDefinitionConfigArray();

        return parent::build($configuration, $prototypeName);
    }

    /**
     * Get server request from GLOBALS as fallback.
     *
     * This is only used when no request is passed to build().
     * In normal TYPO3 frontend rendering, the request is always available.
     *
     * @throws \RuntimeException If no valid server request is available
     */
    private function getServerRequestFromGlobals(): ServerRequestInterface
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;

        if (!$request instanceof ServerRequestInterface) {
            throw new \RuntimeException(
                'No valid server request available. ExtendedArrayFormFactory requires a ServerRequestInterface.',
                1709391235
            );
        }

        return $request;
    }
}
