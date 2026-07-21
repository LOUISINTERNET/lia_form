<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Hooks;

use LIA\LiaForm\Services\TypoScriptReaderService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * This class contains Hooks of the FormRuntime class.
 */
class FormRuntimeHooks
{
    /**
     * Hook the initializeCurrentPageFromRequest of the FormRuntime.
     */
    public function afterInitializeCurrentPage(FormRuntime $formRuntime, ?Page $currentPage, ?Page $lastDisplayedPage = null, ?array $arguments = []): ?Page
    {
        // if ajax is disabled for this extension the hook returns the given value.
        if (!TypoScriptReaderService::isAjaxActive()) {
            return $currentPage;
        }

        // Check if the form is submitted and if it was submitted by ajax.
        if ($formRuntime->getFormState()->isFormSubmitted() && $this->comparePageType($formRuntime->getRequest())) {
            // return null to invoke the finisher on ajax.
            return null;
        }

        // check if currentPage is null and return lastDisplayedPage
        if (!$currentPage instanceof Page && !$formRuntime->getFormState()->isFormSubmitted()) {
            return $lastDisplayedPage;
        }

        // Return currentPage otherwise form will not work.
        return $currentPage;
    }

    /**
     * This hook is used to modify form values.
     */
    public function afterSubmit(FormRuntime $formRuntime, RenderableInterface $renderable, $elementValue, array $requestArguments = [])
    {
        if ($renderable->getType() === 'PhoneAndAreaCode') {
            $areaCode = $formRuntime->getRequest()->getParsedBody()['tx_form_formframework'][$renderable->getIdentifier() . '-areaCode'];
            $elementValue = $areaCode . ' ' . $elementValue;
        }

        return $elementValue;
    }

    /**
     * Compare pageType integer from request and configuration.
     */
    private function comparePageType(RequestInterface $request): bool
    {
        $routing = $request->getAttribute('routing');
        $site = $request->getAttribute('site');

        return (int)$routing->getPageType() === $this->getPageTypeFromConfiguration($site);
    }

    /**
     * Extract the page type for get-content/ from site configuration.
     */
    private function getPageTypeFromConfiguration(Site $site): int
    {
        $fallback = TypoScriptReaderService::getContentPageTypeFallback();
        $pageType = $site->getConfiguration()['routeEnhancers']['PageTypeSuffix']['map']['get-content/'];

        if (empty($pageType)) {
            return $fallback ?? 0;
        }

        return $pageType;
    }
}
