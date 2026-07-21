<?php

/*
 * This file is part of the "LIA Form" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace LIA\LiaForm\EventListener;

use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use LIA\LiaForm\Services\TypoScriptReaderService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Event\AfterCurrentPageIsResolvedEvent;

/**
 * Event listener for form runtime page initialization.
 *
 * Handles AJAX form submission by modifying the current page flow.
 *
 * @author Johannes Delesky, LOUIS INTERNET <delesky@louis.info>
 */
#[AsEventListener(
    identifier: 'lia-form/after-current-page-is-resolved',
    event: AfterCurrentPageIsResolvedEvent::class
)]
final readonly class AfterInitializeCurrentPageEventListener
{
    /**
     * Constructor.
     *
     * @param TypoScriptReaderService $typoScriptReaderService Service for reading TypoScript configuration
     */
    public function __construct(
        private TypoScriptReaderService $typoScriptReaderService
    ) {}

    /**
     * Handle the AfterCurrentPageIsResolvedEvent.
     *
     * Modifies current page based on AJAX submission state and sets default values.
     */
    public function __invoke(AfterCurrentPageIsResolvedEvent $event): void
    {
        $formRuntime = $event->formRuntime;
        $currentPage = $event->currentPage;
        $lastDisplayedPage = $event->lastDisplayedPage;
        $request = $event->request;

        // Set default values for LiaSiteTitle elements
        $this->setLiaSiteTitleDefaultValue($formRuntime, $request);

        // If AJAX is disabled for this extension, do nothing.
        if (!$this->typoScriptReaderService->isAjaxActive()) {
            return;
        }

        // Check if the form is submitted and if it was submitted by AJAX.
        $formState = $formRuntime->getFormState();
        if ($formState !== null && $formState->isFormSubmitted() && $this->comparePageType($request)) {
            // Return null to invoke the finisher on AJAX.
            $event->currentPage = null;
            return;
        }

        // Check if currentPage is null and return lastDisplayedPage.
        $formState = $formRuntime->getFormState();
        if (!$currentPage instanceof Page && ($formState === null || !$formState->isFormSubmitted())) {
            $event->currentPage = $lastDisplayedPage;
            return;
        }

        // Return currentPage otherwise form will not work.
    }

    /**
     * Compare pageType integer from request and configuration.
     *
     * Accepts both PSR-7 ServerRequestInterface and Extbase RequestInterface.
     * Both support getAttribute() which is used for routing and site extraction.
     * Using object type hint because both interfaces share getAttribute() method
     * but have no common interface in TYPO3 14.
     *
     * @param ServerRequestInterface|RequestInterface $request The request object
     * @return bool True if page types match
     */
    private function comparePageType(object $request): bool
    {
        $routing = $request->getAttribute('routing');
        $site = $request->getAttribute('site');

        // Validate types to prevent fatal errors from unexpected attribute values
        if (!$routing instanceof PageArguments || !$site instanceof Site) {
            return false;
        }

        return (int)$routing->getPageType() === $this->getPageTypeFromConfiguration($site);
    }

    /**
     * Extract the page type for get-content/ from site configuration.
     */
    private function getPageTypeFromConfiguration(Site $site): int
    {
        $fallback = $this->typoScriptReaderService->getContentPageTypeFallback();
        $pageType = $site->getConfiguration()['routeEnhancers']['PageTypeSuffix']['map']['get-content/'] ?? null;

        if (empty($pageType)) {
            return $fallback ?? 0;
        }

        return (int)$pageType;
    }

    /**
     * Set default value for LiaSiteTitle elements in FormState.
     *
     * @param FormRuntime $formRuntime The form runtime instance
     * @param object $request The request object
     */
    private function setLiaSiteTitleDefaultValue(FormRuntime $formRuntime, object $request): void
    {
        $formState = $formRuntime->getFormState();
        if ($formState === null) {
            return;
        }

        // Get page title from request
        if (!$request instanceof ServerRequestInterface) {
            return;
        }

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
