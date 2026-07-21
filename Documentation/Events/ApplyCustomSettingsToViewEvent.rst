.. _applyCustomSettingsToViewEvent:

==============================
ApplyCustomSettingsToViewEvent
==============================

Allows you to add your own settings to the view.

Attributes
==========

.. confval:: emailView
    :name: emailView
    :required: true
    :type: TYPO3\CMS\Core\Mail\FluidEmail

    This is the view which can be extended with your custom settings.

Subscribe this event
====================

First create an EventListener class in your Extension. It may look like this.

.. code-block:: php
    :caption: EXT:my_extension/Classes/EventListeners/ApplyCustomSettingsToViewEventListener.php

    <?php
    declare(strict_types=1);

    namespace MY\MyExtension\EventListener;

    use LIA\LiaForm\Event\ApplyCustomSettingsToViewEvent;

    final class ApplyCustomSettingsToViewEventListener {

        /**
        * Manipulate the form configuration by a custom logic.
        *
        * @param ApplyCustomSettingsToViewEvent $event
        * @return void
        */
        public function __invoke(ApplyCustomSettingsToViewEvent $event): void
        {
            // do some crazy stuff ...

            $event->setEmailView($emailView);
        }
    }

Now register this EventListener in your `Services.yaml`.

.. code-block:: yaml
    :caption: EXT:my_extension/Configuration/Services.yaml

    MY\MyExtension\EventListener\ApplyCustomSettingsToViewEventListener:
      tags:
        - name: event.listener
          identifier: 'my-extension/apply-custom-settings-to-view-event'
          event: LIA\LiaForm\Event\ApplyCustomSettingsToViewEvent
