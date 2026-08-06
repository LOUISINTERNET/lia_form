..  _allInOneEventListenerClass:

==============================
All in one EventListener Class
==============================

You can also listen to all events in one class. Here you have to use your own functions.
In this example you see a class containing all Events at once.

..  code-block:: php
    :caption: EXT:my_extension/Classes/EventListener/LiaFormEventListeners.php

    <?php
    declare(strict_types=1);

    namespace MY\MyExtension\EventListener;

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
            $formRuntime = $event->getFormRuntime();
            $formState = $formRuntime->getFormState();
            if ($formState === null) {
                return;
            }

            // The FormState is shared by all finishers of this submission, so this
            // default applies to every mail and cannot be scoped to one of them.
            // Compare against null and '' explicitly: empty() would also match a
            // legitimately submitted '0'.
            $salutation = $formRuntime->getElementValue('salutation');
            if ($salutation === null || $salutation === '') {
                $formState->setFormValue('salutation', 'Sir or Madam');
            }
        }

        /**
        * Manipulate the form configuration by a custom logic.
        *
        * @param ApplyCustomSettingsToViewEvent $event
        * @return void
        */
        public function applyCustomSettingsToViewEventListener(ApplyCustomSettingsToViewEvent $event): void
        {
            // The view is an object, so assigning to it is enough. Call
            // setEmailView() only to swap in a different FluidEmail instance.
            $event->getEmailView()->assign('myCustomVariable', 'someValue');
        }

        /**
        * Manipulate the form configuration by a custom logic.
        *
        * @param BeforeFormDefinitionCreatesEvent $event
        * @return void
        */
        public function beforeFormDefinitionCreatesEventListener(BeforeFormDefinitionCreatesEvent $event): void
        {
            $formConfiguration = $event->getFormDefinitionConfigArray();

            $formConfiguration['renderingOptions']['submitButtonLabel'] = 'Send';

            // The configuration is an array, so it is copied on read: the setter
            // is what makes the change take effect.
            $event->setFormDefinitionConfigArray($formConfiguration);
        }
    }

Your event registration would look like this.

..  code-block:: yaml
    :caption: EXT:my_extension/Configuration/Services.yaml

    MY\MyExtension\EventListener\LiaFormEventListeners:
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
