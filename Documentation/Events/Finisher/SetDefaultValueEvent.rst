.. _setDefaultValueEvent:

====================
SetDefaultValueEvent
====================

This Event allows you to set field with default values if needed.
This event is dispatched in the EmailFinisher in the `processView` function.

Attributes
==========

.. confval:: formRuntime
    :name: formRuntime
    :required: true
    :type: TYPO3\CMS\Form\Domain\Runtime\FormRuntime

    The send formRuntime.

.. confval:: shortFinisherIdentifier
    :name: shortFinisherIdentifier
    :required: true
    :type: string

    The identifier of the called EmailFinisher (EmailToReceiver or EmailToSender).

Subscribe this event
====================

First create an EventListener class in your Extension. It may look like this.

.. code-block:: php
    :caption: EXT:my_extension/Classes/EventListeners/SetDefaultValueEventListener.php

    <?php
    declare(strict_types=1);

    namespace MY\MyExtension\EventListener\Finisher;

    use LIA\LiaForm\Event\Finisher\SetDefaultValueEvent;
    use TYPO3\CMS\Core\Utility\StringUtility;

    final class SetDefaultValueEventListener {

        /**
        * Manipulate the form configuration by a custom logic.
        *
        * @param SetDefaultValueEvent $event
        * @return void
        */
        public function __invoke(SetDefaultValueEvent $event): void
        {
            // do some crazy stuff ...

            $event->setEmailView($emailView);
        }
    }

Now register this EventListener in your `Services.yaml`.

.. code-block:: yaml
    :caption: EXT:my_extension/Configuration/Services.yaml

    MY\MyExtension\EventListener\Finisher\SetDefaultValueEventListener:
      tags:
        - name: event.listener
          identifier: 'my-extension/finisher-set-default-values-event'
          event: LIA\LiaForm\Event\Finisher\SetDefaultValueEvent

