..  _EventListener:

=============
EventListener
=============

This extension listen to some events to modify the core functionality.
Here you see a list of EventListeners that are subscribed by this extension.

..  contents::
    :local:
    :depth: 1


AfterFlexFormDataStructureParsedEvent
=====================================

This event extends the default FlexForm of the core with a custom css field.
Here you can set a custom css class on the current form. Please register your
EventListener if you have also extend this FlexForm. This Subscriber
is registered by the `lia-form/flex-form-parsing`. You can align your EventListener
after this event by adding the following tag `after: 'lia-form/flex-form-parsing'`

..  attention::
    Changed in 2.0.0: this subscriber was renamed from
    ``lia-form/flex-form-extension`` to ``lia-form/flex-form-parsing``, and moved
    from ``Configuration/Services.yaml`` to an ``#[AsEventListener]`` attribute.
    If you upgrade from 1.x, update the ``after:`` or ``before:`` tags in your own
    ``Services.yaml``. A stale identifier is not reported as an error, your
    EventListener silently loses its ordering.


BeforeRenderableIsAddedToFormEvent
==================================

Sets the current page title as the ``defaultValue`` property of `LiaSiteTitle`
elements, at the moment the element is added to the form. This Subscriber is
registered by the `lia-form/before-renderable-is-added`. You can align your
EventListener after this event by adding the following tag
`after: 'lia-form/before-renderable-is-added'`

..  note::
    This writes the ``defaultValue`` *property* via ``setProperty()``, which is
    what the LiaSiteTitle partial renders into the hidden field's ``value``
    attribute. It is not the element's default value in the sense of
    ``setDefaultValue()``, so ``getDefaultValue()`` and
    ``FormRuntime::getElementValue()`` do not see it.


AfterCurrentPageIsResolvedEvent
===============================

Seeds the value of `LiaSiteTitle` elements in the FormState with the current page
title, as long as no value has been set yet. This Subscriber is registered by the
`lia-form/after-current-page-is-resolved`. You can align your EventListener after
this event by adding the following tag `after: 'lia-form/after-current-page-is-resolved'`

..  note::
    Two subscribers fill `LiaSiteTitle`, on different layers, and they do not
    compete. What the hidden field renders is always the ``defaultValue`` property
    set by `lia-form/before-renderable-is-added`, because the partial passes it as
    an explicit ``value`` attribute and Fluid prefers that over the mapped value.
    The FormState value seeded here is what ``FormRuntime::getElementValue()``
    returns, so it is the fallback that keeps finishers and mails populated when
    the hidden field is not submitted back.

..  attention::
    This subscriber only seeds FormState values, it does not modify
    ``$event->currentPage``. If your own EventListener uses
    ``after: 'lia-form/after-current-page-is-resolved'`` because it relies on the
    current page having been finally resolved at that point, that assumption no
    longer holds — align it against whichever subscriber resolves the page in your
    installation. Ordering against a stale identifier is not reported as an error,
    your EventListener silently loses its ordering.


BeforeRenderableIsValidatedEvent
================================

Prepends the submitted area code to the phone number of `PhoneAndAreaCode` elements,
before the value is validated. This Subscriber is registered by the
`lia-form/before-renderable-is-validated`. You can align your EventListener after
this event by adding the following tag `after: 'lia-form/before-renderable-is-validated'`
