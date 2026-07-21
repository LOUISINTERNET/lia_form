.. _phoneAndAreaCode:

================
PhoneAndAreaCode
================

A phone number field with a selectable international area code. The field renders
a select box containing all international dial codes (e.g. ``+49``) next to a
text field for the phone number itself.

The list of area codes is loaded from
``EXT:lia_form/Configuration/Data/PhoneAreaCodeList.json``. Each entry consists
of a country name, the dial code and the ISO country code:

.. code-block:: json

    {
      "name": "Germany",
      "dial_code": "+49",
      "code": "DE"
    }

To use a custom data source, subscribe to the
:ref:`BeforePhoneAreaCodeInitializeEvent <BeforePhoneAreaCodeInitializeEvent>`
and set your own path to a JSON file with the same structure.


.. confval:: label
    :name: phone-and-area-code-label
    :required: false
    :type: string
    :propertyPath: label

    The field label

.. confval:: elementDescription
    :name: phone-and-area-code-element-description
    :required: false
    :type: string
    :propertyPath: properties.elementDescription

    The field description.

.. confval:: areaCodeClass
    :name: phone-and-area-code-area-code-class
    :required: false
    :type: string
    :propertyPath: properties.areaCodeClass

    CSS class added to the area code select box.

.. confval:: class
    :name: phone-and-area-code-class
    :required: false
    :type: string
    :propertyPath: properties.class

    CSS class added to the phone number text field.

.. confval:: gridColumnViewPortConfiguration
    :name: phone-and-area-code-grid-column-view-port-configuration
    :required: false
    :type: GridColumnViewPortConfigurationEditor

    Grid settings.

.. confval:: requiredValidator
    :name: phone-and-area-code-required-validator
    :required: false
    :type: RequiredValidatorEditor
    :propertyPath: properties.fluidAdditionalAttributes.required

    Field validation settings.
