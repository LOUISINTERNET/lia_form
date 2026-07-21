.. _RenderFinisherPreviewEvent

==========================
RenderFinisherPreviewEvent
==========================

This event is dispatched once per finisher while the page module preview of a
``form_formframework`` content element is rendered (in the finisher overview
provided by this extension's ``FormPreviewRenderer``). It lets an extension
supply a custom preview for its own finisher types. If a listener handles the
event, its HTML is used and the built-in rendering for that finisher is
skipped.

.. _attributes

Attributes
==========

.. confval:: identifier
    :name: identifier
    :type: string

    The finisher identifier (e.g. ``EmailToReceiver`` or your own
    ``MyCustomFinisher``). Read-only, via ``getIdentifier()``.

.. confval:: options
    :name: options
    :type: array

    The finisher options from the YAML form definition. Read-only, via
    ``getOptions()``.

.. confval:: flexFormFinisher
    :name: flexFormFinisher
    :type: array

    The finisher settings coming from the content element's FlexForm
    overrides. Read-only, via ``getFlexFormFinisher()``.

.. confval:: overrideFinishers
    :name: overrideFinishers
    :type: bool

    Whether FlexForm overrides are enabled for this element. Read-only, via
    ``isOverrideFinishers()``.

.. confval:: previewHtml
    :name: previewHtml
    :type: string

    Set via ``setPreviewHtml()``. Calling it marks the event as handled
    (``isHandled()``), so the default rendering is skipped for this finisher.

.. _subscribe-this-event

Subscribe this event
====================

Create an EventListener in your extension that reacts to your own finisher
identifier and returns its preview markup.

.. code-block:: php
    :caption: EXT:my_extension/Classes/EventListener/RenderFinisherPreviewEventListener.php

    <?php
    declare(strict_types=1);

    namespace MY\MyExtension\EventListener;

    use LIA\LiaForm\Event\RenderFinisherPreviewEvent;

    final class RenderFinisherPreviewEventListener
    {
        public function __invoke(RenderFinisherPreviewEvent $event): void
        {
            if ($event->getIdentifier() !== 'MyCustomFinisher') {
                return;
            }

            $options = $event->getOptions();
            $event->setPreviewHtml(
                '<em>My custom finisher → ' . htmlspecialchars((string)($options['target'] ?? '')) . '</em>'
            );
        }
    }

Register the listener in your ``Services.yaml``.

.. code-block:: yaml
    :caption: EXT:my_extension/Configuration/Services.yaml

    MY\MyExtension\EventListener\RenderFinisherPreviewEventListener:
      tags:
        - name: event.listener
          identifier: 'my-extension/render-finisher-preview-event'
          event: LIA\LiaForm\Event\RenderFinisherPreviewEvent
