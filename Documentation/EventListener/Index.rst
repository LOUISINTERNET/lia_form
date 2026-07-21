.. _EventListener

=============
EventListener
=============

This extension listen to some events to modify the core functionality.
Here you see a list of EventListeners that are subscribed by this extension.

.. contents::
    :local:
    :depth: 1


AfterFlexFormDataStructureParsedEvent
=====================================

This event extends the default FlexForm of the core with a custom css field.
Here you can set a custom css class on the current form. Please register your
EventListener if you have also extend this FlexForm. This Subscriber
is registered by the `lia-form/flex-form-extension`. You can aline your EventListener
after this event by adding the following tag `after: 'lia-form/flex-form-extension'` 




