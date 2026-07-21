.. _BeforePhoneAreaCodeInitializeEvent

==================================
BeforePhoneAreaCodeInitializeEvent
==================================

This event is dispatched when the :ref:`PhoneAndAreaCode <phoneAndAreaCode>`
form element is initialized. It provides the possibility to replace the default
area code list (``EXT:lia_form/Configuration/Data/PhoneAreaCodeList.json``)
with a custom JSON data source.


.. _attributes

Attributes
==========

.. confval:: dataSourcePath
    :name: dataSourcePath
    :required: true
    :type: string

    Path to a JSON file containing the area code list. ``EXT:`` paths are
    resolved automatically.


.. _subscribe-this-event

Subscribe this event
====================

First create an EventListener class in your Extension. It may look like this.

.. code-block:: php
    :caption: EXT:my_extension/Classes/EventListeners/BeforePhoneAreaCodeInitializeEventListener.php

    <?php
    declare(strict_types=1);

    namespace MY\MyExtension\EventListener;

    use LIA\LiaForm\Event\BeforePhoneAreaCodeInitializeEvent;

    final class BeforePhoneAreaCodeInitializeEventListener {

        /**
        * Set a custom area code list.
        *
        * @param BeforePhoneAreaCodeInitializeEvent $event
        * @return void
        */
        public function __invoke(BeforePhoneAreaCodeInitializeEvent $event): void
        {
            $event->setDataSourcePath('EXT:my_extension/Configuration/Data/MyAreaCodeList.json');
        }
    }

Now register this EventListener in your `Services.yaml`.

.. code-block:: yaml
    :caption: EXT:my_extension/Configuration/Services.yaml

    MY\MyExtension\EventListener\BeforePhoneAreaCodeInitializeEventListener:
      tags:
        - name: event.listener
          identifier: 'my-extension/before-phone-area-code-initialize-event'
          event: LIA\LiaForm\Event\BeforePhoneAreaCodeInitializeEvent
