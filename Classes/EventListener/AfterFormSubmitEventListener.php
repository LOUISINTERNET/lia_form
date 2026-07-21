<?php

/*
 * This file is part of the "LIA Form" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace LIA\LiaForm\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Form\Event\BeforeRenderableIsValidatedEvent;

/**
 * Event listener for form submission handling.
 *
 * Modifies form element values after submission, e.g., combining phone area codes.
 *
 * @author Johannes Delesky, LOUIS INTERNET <delesky@louis.info>
 */
#[AsEventListener(
    identifier: 'lia-form/before-renderable-is-validated',
    event: BeforeRenderableIsValidatedEvent::class
)]
final class AfterFormSubmitEventListener
{
    /**
     * Handle the BeforeRenderableIsValidatedEvent.
     *
     * Combines area code with phone number for PhoneAndAreaCode elements.
     */
    public function __invoke(BeforeRenderableIsValidatedEvent $event): void
    {
        $renderable = $event->renderable;

        if ($renderable->getType() !== 'PhoneAndAreaCode') {
            return;
        }

        $elementValue = $event->value;
        $parsedBody = $event->request->getParsedBody();

        // Validate and sanitize area code input to prevent injection attacks
        $rawAreaCode = '';
        if (is_array($parsedBody) && isset($parsedBody['tx_form_formframework'][$renderable->getIdentifier() . '-areaCode'])) {
            $rawAreaCode = $parsedBody['tx_form_formframework'][$renderable->getIdentifier() . '-areaCode'];
        }
        $areaCode = preg_replace('/[^0-9+\-() ]/', '', (string)$rawAreaCode);

        // Limit length to prevent abuse
        if ($areaCode !== null && strlen($areaCode) > 20) {
            $areaCode = substr($areaCode, 0, 20);
        }

        if ($areaCode !== null && $areaCode !== '') {
            $event->value = $areaCode . ' ' . $elementValue;
        }
    }
}
