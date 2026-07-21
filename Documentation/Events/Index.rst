.. _Events

======
Events
======

This extension provide events which can be subscribed to modify the default functionality.

..  card-grid::
    :columns: 1
    :columns-md: 2
    :gap: 4
    :class: pb-4
    :card-height: 100

    ..  card:: :ref:`BeforeFormDefinitionCreatesEvent <BeforeFormDefinitionCreatesEvent>`

        This event is dispatched in a custom FormFactory provided by this extension.

    ..  card:: :ref:`ApplyCustomSettingsToViewEvent <ApplyCustomSettingsToViewEvent>`

        This event is dispatched in EmailFinisher of this extension.

    ..  card:: :ref:`SetDefaultValueEvent <SetDefaultValueEvent>`

        Set default values of field in EmailFinisher.

    ..  card:: :ref:`BeforePhoneAreaCodeInitializeEvent <BeforePhoneAreaCodeInitializeEvent>`

        Replace the area code list of the PhoneAndAreaCode element.

    ..  card:: :ref:`RenderFinisherPreviewEvent <RenderFinisherPreviewEvent>`

        Provide a custom page module preview for your own finisher types.

    ..  card:: :ref:`AllInOneEventListenerClass <AllInOneEventListenerClass>`

        How to register all events in just one class.

..  toctree::
    :hidden:
    :titlesonly:

    ./BeforeFormDefinitionCreatesEvent
    ./ApplyCustomSettingsToViewEvent
    ./Finisher/SetDefaultValueEvent
    ./BeforePhoneAreaCodeInitializeEvent
    ./RenderFinisherPreviewEvent
    ./AllInOneEventListenerClass
