.. _exampleConfig:

=====================
Example configuration
=====================

In the root of this extension you find an `__EXAMPLE__` folder. This folder contains all the nessesary configuration
for this extension and a little bit more.

You can copy the example configuration into your sitepackage-extension and adjust some paths and values in it to get
it up and running.


YAML configuration
==================

In the `EXT:lia_form/__EXAMPLE__/my_extension/Configuration/Yaml` you find the form configuration files.

CustomFormSetup.yaml
--------------------

This file override and extends the default form configuration. It extends the root path configuration of the form.
After you copied this file into your sitepackage-extension you have to adjust the extension name in the paths.
All the paths contains the placeholder `my_extension`.


Form prototypes
---------------

In this file is also a register of three prototype form configurations. Yo use the provided prototypes copy them
in the same directory where you pasted the CustomFormSetup.yaml in.

If you do not need them you can delete this part of code out this file.

.. code-block:: yaml
    :caption: Prototype registration

    formManager:
      selectablePrototypesConfiguration:
        100:
          newFormTemplates:
            300:
              templatePath: "EXT:lia_form/Resources/Private/Backend/Templates/FormEditor/Yaml/NewForms/DefaultContactForm.yaml"
              label: "LIA Default contact form"
            301:
              templatePath: "EXT:my_extension/Configuration/Yaml/ContactFormWithFileUPloadPrototype.yaml"
              label: "LIA Contact form with file upload"
            302:
              templatePath: "EXT:my_extension/Configuration/Yaml/ContactFormWithImageUploadPrototype.yaml"
              label: "LIA Contact form with image upload"

Elements.yaml
-------------

This file extends all the form elements with a small grid configuration.

TypoScript configuration
========================

In the `EXT:lia_form/__EXAMPLE__/my_extension/Configuration/TypoScript` folder you find an example configuration.
The subfolder `Extensions` contains the typoscript to override the default `lia_form` configuration. You can copy
this configuration into your sitepackage-extension and load it.


Constants.typoscript
--------------------

In this file are the constants to set the template root paths of the form extension.


Setup.typoscript
----------------

This setup loads the default form framework TypoScript. The form YAML
configurations themselves are registered via auto-discovery — see
`EXT:lia_form/__EXAMPLE__/my_extension/Configuration/Form/MyExtensionForms/config.yaml`
for the project-specific configuration set (priority 150).


Resources
=========

In the folder `EXT:lia_form/__EXAMPLE__/my_extension/Resources` you find all the template, partials and translations which are used by this example configuration.
You can copy them into sitepackage-extension and modify them. If the Folder Structure does not match the folder structure
of your sitepackage-extension, you have to adjust the paths in the configurations.
