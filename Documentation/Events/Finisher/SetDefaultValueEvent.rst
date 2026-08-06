..  _setDefaultValueEvent:

====================
SetDefaultValueEvent
====================

This Event allows you to set field with default values if needed.
This event is dispatched in the EmailFinisher in the `processView` function.

Attributes
==========

..  confval:: formRuntime
    :name: formRuntime
    :required: true
    :type: TYPO3\CMS\Form\Domain\Runtime\FormRuntime

    The send formRuntime.

..  confval:: shortFinisherIdentifier
    :name: shortFinisherIdentifier
    :required: true
    :type: string

    The identifier of the called EmailFinisher (EmailToReceiver or EmailToSender).

Subscribe this event
====================

First create an EventListener class in your Extension. It may look like this.

..  code-block:: php
    :caption: EXT:my_extension/Classes/EventListener/SetDefaultValueEventListener.php

    <?php
    declare(strict_types=1);

    namespace MY\MyExtension\EventListener;

    use LIA\LiaForm\Event\Finisher\SetDefaultValueEvent;

    final class SetDefaultValueEventListener {

        /**
        * Fill empty form values with defaults before the email is rendered.
        *
        * @param SetDefaultValueEvent $event
        * @return void
        */
        public function __invoke(SetDefaultValueEvent $event): void
        {
            $formRuntime = $event->getFormRuntime();
            $formState = $formRuntime->getFormState();
            if ($formState === null) {
                return;
            }

            // Compare against null and '' explicitly: empty() would also match a
            // legitimately submitted '0', for example the first salutation option.
            $salutation = $formRuntime->getElementValue('salutation');
            if ($salutation === null || $salutation === '') {
                $formState->setFormValue('salutation', 'Sir or Madam');
            }
        }
    }

..  note::
    The FormState is shared by all finishers of one submission. A default set
    here is visible to both mails (``EmailToReceiver`` and ``EmailToSender``) and
    to every following finisher such as ``SaveToDatabase`` — it cannot be scoped
    to a single mail. Use ``shortFinisherIdentifier`` to find out which mail is
    currently being rendered, not to limit a write.

..  note::
    Write through ``$formRuntime->getFormState()->setFormValue()``. ``FormRuntime``
    does implement ``ArrayAccess``, but the core marks ``offsetGet()`` and
    ``offsetSet()`` as ``@internal``, and ``offsetGet()`` falls back to the
    runtime's own ``get<Identifier>()`` methods: ``$formRuntime['type']`` returns
    the form type, not the value of an element identified ``type``.

Now register this EventListener in your `Services.yaml`.

..  code-block:: yaml
    :caption: EXT:my_extension/Configuration/Services.yaml

    MY\MyExtension\EventListener\SetDefaultValueEventListener:
      tags:
        - name: event.listener
          identifier: 'my-extension/finisher-set-default-values-event'
          event: LIA\LiaForm\Event\Finisher\SetDefaultValueEvent

