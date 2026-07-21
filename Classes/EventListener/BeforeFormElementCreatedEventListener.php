<?php

/*
 * This file is part of the "LIA Form" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace LIA\LiaForm\EventListener;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractFormElement;
use TYPO3\CMS\Form\Event\BeforeRenderableIsAddedToFormEvent;

/**
 * Event listener for form element initialization.
 *
 * Preloads default values for specific form elements like LiaSiteTitle.
 *
 * @author LOUIS INTERNET <devs@louis.info>
 */
#[AsEventListener(
    identifier: 'lia-form/before-renderable-is-added',
    event: BeforeRenderableIsAddedToFormEvent::class
)]
final class BeforeFormElementCreatedEventListener
{
    /**
     * Handle the BeforeRenderableIsAddedToFormEvent.
     *
     * Sets the current page title as default value for LiaSiteTitle elements.
     */
    public function __invoke(BeforeRenderableIsAddedToFormEvent $event): void
    {
        $renderable = $event->renderable;

        if ($renderable->getType() !== 'LiaSiteTitle') {
            return;
        }

        // Note: BeforeRenderableIsAddedToFormEvent in TYPO3 14 only provides the renderable,
        // not the FormRuntime or Request. Using $GLOBALS['TYPO3_REQUEST'] is the only way
        // to access the current request in this context. This is a TYPO3 Core limitation.
        // The value is always auto-escaped by Fluid when rendered, preventing XSS.
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return;
        }

        $pageInformation = $request->getAttribute('frontend.page.information');
        if ($pageInformation === null) {
            return;
        }

        // Page title from database - safe for use as form default value
        // because Fluid auto-escapes output by default
        $currentTitle = (string)($pageInformation->getPageRecord()['title'] ?? '');

        if ($renderable instanceof AbstractFormElement) {
            $renderable->setProperty('defaultValue', $currentTitle);
        }
    }
}
