.. _allInOneEventListenerClass:

==============================
All in one EventListener Class
==============================

You can also listen to all events in one class. Here you have to use your own functions.
In this example you see a class containing all Events at once.

.. code-block:: php
    :caption: EXT:my_extension/Classes/EventListeners/LiaFormEventListeners.php

    <?php
    declare(strict_types=1);

    namespace MY\MyExtension\EventListener\Finisher;

    use LIA\LiaForm\Event\ApplyCustomSettingsToViewEvent;
    use LIA\LiaForm\Event\BeforeFormDefinitionCreatesEvent;
    use LIA\LiaForm\Event\Finisher\SetDefaultValueEvent;

    final class LiaFormEventListeners
    {
        /**
        * Manipulate the FormRuntime to set default field values.
        *
        * @param SetDefaultValueEvent $event
        * @return void
        */
        public function finisherSetDefaultValueEventListener(SetDefaultValueEvent $event): void
        {
            // do some crazy stuff ...

            $event->setFormRuntime($formRuntime);
        }

        /**
        * Manipulate the form configuration by a custom logic.
        *
        * @param ApplyCustomSettingsToViewEvent $event
        * @return void
        */
        public function applyCustomSettingsToViewEventListener(ApplyCustomSettingsToViewEvent $event): void
        {
            // do some crazy stuff ...

            $event->setEmailView($emailView);
        }

        /**
        * Manipulate the form configuration by a custom logic.
        *
        * @param BeforeFormDefinitionCreatesEvent $event
        * @return void
        */
        public function beforeFormDefinitionCreatesEventListener(BeforeFormDefinitionCreatesEvent $event): void
        {
            // do some crazy stuff ...

            $event->setFormDefinitionConfigArray($formconfiguration);
        }
    }

Your event registration would look like this.

.. code-block:: yaml
    :caption: EXT:my_extension/Configuration/Services.yaml

    MY\MyExtension\EventListener\Finisher\SetDefaultValueEventListener:
      tags:
        - name: event.listener
          identifier: 'my-extension/finisher-set-default-values-event'
          event: LIA\LiaForm\Event\Finisher\SetDefaultValueEvent
          method: 'finisherSetDefaultValueEventListener'
        - name: event.listener
          identifier: 'my-extension/apply-custom-settings-to-view-event'
          event: LIA\LiaForm\Event\ApplyCustomSettingsToViewEvent
          method: 'applyCustomSettingsToViewEventListener'
        - name: event.listener
          identifier: 'my-extension/before-form-definition-creates-event'
          event: LIA\LiaForm\Event\BeforeFormDefinitionCreatesEvent
          method: 'beforeFormDefinitionCreatesEventListener'
