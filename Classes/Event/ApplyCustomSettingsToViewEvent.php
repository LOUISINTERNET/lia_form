<?php

declare(strict_types=1);

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Event;

use TYPO3\CMS\Core\Mail\FluidEmail;

/**
 * Allows you to add your own settings to the view.
 */
final class ApplyCustomSettingsToViewEvent
{
    /**
     * @var FluidEmail $emailView
     */
    protected FluidEmail $emailView;

    /**
     * Event constructor
     */
    public function __construct(FluidEmail $emailView)
    {
        $this->emailView = $emailView;
    }

    /**
     * Get the value of emailView
     */
    public function getEmailView(): FluidEmail
    {
        return $this->emailView;
    }

    /**
     * Set the value of emailView
     *
     * @param FluidEmail $emailView
     */
    public function setEmailView(FluidEmail $emailView): void
    {
        $this->emailView = $emailView;
    }
}
