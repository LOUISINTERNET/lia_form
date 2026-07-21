.. _liaParameterHidden:

==================
LiaParameterHidden
==================

A hidden field that can be filled with a URL parameter, e.g. to track campaign
parameters together with a form submission. The element renders a hidden input
with the additional attribute ``data-url-parameter-tracker``.

.. attention::
    This extension does not ship any JavaScript to fill the field. Provide a
    script in your site package that reads the desired URL parameter and writes
    it into inputs marked with ``data-url-parameter-tracker``.


.. confval:: label
    :name: lia-parameter-hidden-label
    :required: false
    :type: string
    :propertyPath: label

    The field label

.. confval:: elementDescription
    :name: lia-parameter-hidden-element-description
    :required: false
    :type: string
    :propertyPath: properties.elementDescription

    The field description.

.. confval:: gridColumnViewPortConfiguration
    :name: lia-parameter-hidden-grid-column-view-port-configuration
    :required: false
    :type: GridColumnViewPortConfigurationEditor

    Grid settings.

.. confval:: requiredValidator
    :name: lia-parameter-hidden-required-validator
    :required: false
    :type: RequiredValidatorEditor
    :propertyPath: properties.fluidAdditionalAttributes.required

    Field validation settings.
