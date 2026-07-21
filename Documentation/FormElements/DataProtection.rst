.. _dataProtection:

==============
DataProtection
==============

.. contents::
    :local:
    :depth: 1


This is a checkbox field which provides a reference to the privacy policy
with a link to the corresponding page.


.. confval:: label
    :name: data-protection-label
    :required: false
    :type: string
    :propertyPath: label

    The field label

.. confval:: elementDescription
    :name: data-protection-element-description
    :required: false
    :type: string
    :propertyPath: properties.elementDescription

    The field description.

.. confval:: pageUid
    :name: data-protection-page-uid
    :required: false
    :type: Typo3WinBrowserEditor
    :propertyPath: renderingOptions.pageUid

    A page selection to choose the page with the privacy policy.

.. confval:: text
    :name: data-protection-text
    :required: false
    :type: string
    :propertyPath: properties.text

    This text is used as label for the checkbox. Use `%pagelink_text%` as placeholder for the link.

.. confval:: linktext
    :name: data-protection-linktext
    :required: false
    :type: string
    :propertyPath: properties.linktext

    This text is used for the link to the selected page.

.. confval:: gridColumnViewPortConfiguration
    :name: data-protection-grid-column-view-port-configuration
    :required: false
    :type: GridColumnViewPortConfigurationEditor

    Grid settings.

.. confval:: requiredValidator
    :name: data-protection-required-validator
    :required: false
    :type: RequiredValidatorEditor
    :propertyPath: properties.fluidAdditionalAttributes.required

    Field validation settings.
