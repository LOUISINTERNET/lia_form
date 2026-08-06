..  _BeforeFormDefinitionCreatesEvent:

================================
BeforeFormDefinitionCreatesEvent
================================

This event is dispatched in the ExtendedArrayFormFactory and provide the possibility
to manipulate the form configuration before FormDefinition class is created.

Attributes
==========

..  confval:: formDefintionConfigArray
    :name: formDefintionConfigArray
    :required: true
    :type: array

    This is the configuration array of the form definition.

..  confval:: request
    :name: request
    :required: true
    :type: ServerRequestInterface

    ..  attention::
        This attribute is readonly.

    This is the current server request object.


..  confval:: renderedForms
    :name: renderedForms
    :required: true
    :type: integer

    ..  attention::
        This attribute is readonly.

    This is the count of rendered form on the current page.

Subscribe this event
====================

First create an EventListener class in your Extension. It may look like this.

..  code-block:: php
    :caption: EXT:my_extension/Classes/EventListener/BeforeFormDefinitionCreatesEventListener.php

    <?php
    declare(strict_types=1);

    namespace MY\MyExtension\EventListener;

    use LIA\LiaForm\Event\BeforeFormDefinitionCreatesEvent;

    final class BeforeFormDefinitionCreatesEventListener {

        /**
        * Manipulate the form configuration by a custom logic.
        *
        * @param BeforeFormDefinitionCreatesEvent $event
        * @return void
        */
        public function __invoke(BeforeFormDefinitionCreatesEvent $event): void
        {
            $formConfiguration = $event->getFormDefinitionConfigArray();

            $formConfiguration['renderingOptions']['submitButtonLabel'] = 'Send';

            // The configuration is an array, so it is copied on read: the setter
            // is what makes the change take effect.
            $event->setFormDefinitionConfigArray($formConfiguration);
        }
    }

Now register this EventListener in your `Services.yaml`.

..  code-block:: yaml
    :caption: EXT:my_extension/Configuration/Services.yaml

    MY\MyExtension\EventListener\BeforeFormDefinitionCreatesEventListener:
      tags:
        - name: event.listener
          identifier: 'my-extension/before-form-definition-creates-event'
          event: LIA\LiaForm\Event\BeforeFormDefinitionCreatesEvent

