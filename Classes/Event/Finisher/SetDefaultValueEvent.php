<?php

declare(strict_types=1);

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Event\Finisher;

use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * This event is dispatched in the mail finisher and allows fields to be filled
 * with default values.
 */
final class SetDefaultValueEvent
{
    /**
     * @var FormRuntime $formRuntime
     */
    protected FormRuntime $formRuntime;

    /**
     * @var string $emailType can be EmailToReceiver or EmailToSender
     */
    protected string $shortFinisherIdentifier;

    /**
     * Event constructor
     *
     * @param FormRuntime $formRuntime
     * @param string $shortFinisherIdentifier can be EmailToReceiver or EmailToSender
     */
    public function __construct(FormRuntime $formRuntime, string $shortFinisherIdentifier)
    {
        $this->formRuntime = $formRuntime;
        $this->shortFinisherIdentifier = $shortFinisherIdentifier;
    }

    /**
     * Get the value of formRuntime
     */
    public function getFormRuntime(): FormRuntime
    {
        return $this->formRuntime;
    }

    /**
     * Set the value of formRuntime
     */
    public function setFormRuntime(FormRuntime $formRuntime): void
    {
        $this->formRuntime = $formRuntime;
    }

    /**
     * Get $shortFinisherIdentifier can be EmailToReceiver or EmailToSender
     *
     * @return string
     */
    public function getShortFinisherIdentifier(): string
    {
        return $this->shortFinisherIdentifier;
    }

    /**
     * Set $shortFinisherIdentifier can be EmailToReceiver or EmailToSender
     *
     * @param string $shortFinisherIdentifier can be EmailToReceiver or EmailToSender
     */
    public function setShortFinisherIdentifier(string $shortFinisherIdentifier): void
    {
        $this->shortFinisherIdentifier = $shortFinisherIdentifier;
    }
}
