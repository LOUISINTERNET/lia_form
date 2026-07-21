.. _liaDatePicker:

=============
LiaDatePicker
=============

A date field prepared for use with an external date picker library. The element
renders a plain text field with the additional CSS class ``have-datepicker``.

.. attention::
    This extension does not ship any date picker JavaScript. Attach your own
    date picker library (e.g. flatpickr, air-datepicker, ...) to the
    ``have-datepicker`` class in your site package.


.. confval:: label
    :name: lia-date-picker-label
    :required: false
    :type: string
    :propertyPath: label

    The field label

.. confval:: elementDescription
    :name: lia-date-picker-element-description
    :required: false
    :type: string
    :propertyPath: properties.elementDescription

    The field description.

.. confval:: gridColumnViewPortConfiguration
    :name: lia-date-picker-grid-column-view-port-configuration
    :required: false
    :type: GridColumnViewPortConfigurationEditor

    Grid settings.

.. confval:: requiredValidator
    :name: lia-date-picker-required-validator
    :required: false
    :type: RequiredValidatorEditor
    :propertyPath: properties.fluidAdditionalAttributes.required

    Field validation settings.
