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
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Event\AfterCurrentPageIsResolvedEvent;

/**
 * Seeds the default value of LiaSiteTitle elements with the current page title.
 *
 * The listener identifier is kept as-is: it names the event this listener
 * subscribes to, and consuming extensions may order against it via before/after.
 * It no longer implies anything about $event->currentPage though: this listener
 * only seeds FormState values.
 *
 * @author Johannes Delesky, LOUIS INTERNET <delesky@louis.info>
 */
#[AsEventListener(
    identifier: 'lia-form/after-current-page-is-resolved',
    event: AfterCurrentPageIsResolvedEvent::class
)]
final class LiaSiteTitleDefaultValueEventListener
{
    /**
     * Handle the AfterCurrentPageIsResolvedEvent.
     *
     * Sets default values, the resolved page is left untouched.
     */
    public function __invoke(AfterCurrentPageIsResolvedEvent $event): void
    {
        // Set default values for LiaSiteTitle elements
        $this->setLiaSiteTitleDefaultValue($event->formRuntime, $event->request);
    }

    /**
     * Set default value for LiaSiteTitle elements in FormState.
     *
     * @param FormRuntime $formRuntime The form runtime instance
     * @param ServerRequestInterface $request The request object
     */
    private function setLiaSiteTitleDefaultValue(FormRuntime $formRuntime, ServerRequestInterface $request): void
    {
        $formState = $formRuntime->getFormState();
        if ($formState === null) {
            return;
        }

        // Get page title from request
        $pageInformation = $request->getAttribute('frontend.page.information');
        if ($pageInformation === null) {
            return;
        }

        $currentTitle = (string)($pageInformation->getPageRecord()['title'] ?? '');
        if ($currentTitle === '') {
            return;
        }

        // Find all LiaSiteTitle elements and set their value in FormState
        foreach ($formRuntime->getFormDefinition()->getRenderablesRecursively() as $element) {
            if ($element->getType() === 'LiaSiteTitle') {
                $existingValue = $formRuntime->getElementValue($element->getIdentifier());
                if ($existingValue === null || $existingValue === '') {
                    $formState->setFormValue($element->getIdentifier(), $currentTitle);
                }
            }
        }
    }
}
