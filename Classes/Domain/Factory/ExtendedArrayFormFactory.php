<?php

declare(strict_types=1);

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Domain\Factory;

use LIA\LiaForm\Event\BeforeFormDefinitionCreatesEvent;
use LIA\LiaForm\Services\ServerRequestService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Form\Domain\Exception\RenderingException;
use TYPO3\CMS\Form\Domain\Factory\ArrayFormFactory;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;

/**
 * This Factory build the FormDefinition object and returns it.
 * It also dispatch an event to manipulate the form configuration.
 */
class ExtendedArrayFormFactory extends ArrayFormFactory
{
    /**
     * Count the rendered forms on a request.
     */
    private static int $renderedForms = 0;

    public function __construct(private readonly EventDispatcher $eventDispatcher) {}

    /**
     * Build a form definition by given configuration array.
     *
     * @throws RenderingException
     */
    public function build(array $configuration, ?string $prototypeName = null, ?ServerRequestInterface $request = null): FormDefinition
    {
        self::$renderedForms++;
        $eventDispatcher = $this->eventDispatcher;
        $event = $eventDispatcher->dispatch(
            new BeforeFormDefinitionCreatesEvent(
                $configuration,
                ServerRequestService::getServerRequest(),
                self::$renderedForms
            )
        );

        $configuration = $event->getFormDefinitionConfigArray();

        return parent::build($configuration, $prototypeName);
    }
}
