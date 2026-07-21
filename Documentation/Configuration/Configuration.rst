:navigation-title: Configuration
..  _configuration:

=============
Configuration
=============

.. attention::
    First load the typoscript of this extension in your static template.

This extension provides also an :ref:`example configuration <exampleConfig>`.


.. contents::
    :local:
    :depth: 1


Override configuration
======================

The YAML configuration of this extension is registered via the form
framework's auto-discovery convention (`Configuration/Form/<SetName>/config.yaml`,
available since TYPO3 v14.2). Two configuration sets are provided:

* `lia/lia-form-setup` (priority 100) — base setup (`LiaFormSetup.yaml`)
* `lia/lia-form-elements` (priority 199) — form elements and finishers

To override the default yaml configuration create your own configuration set
in your extension. Use a priority between 101 and 198 so it loads after the
base setup and before the element definitions.

.. code-block:: yaml
    :caption: EXT:my_extension/Configuration/Form/MyExtensionForms/config.yaml

    name: my-vendor/my-extension-forms
    priority: 150

    imports:
      - { resource: 'EXT:my_extension/Configuration/Yaml/CustomFormSetup.yaml' }

No TypoScript registration is required — the set is discovered automatically
in frontend and backend.

.. note::
    The former TypoScript-based registration via
    `plugin.tx_form.settings.yamlConfigurations` /
    `module.tx_form.settings.yamlConfigurations` is deprecated since
    TYPO3 v14.2 and will be removed in v15. Existing TypoScript registrations
    still work during the deprecation period: legacy paths are loaded after
    all auto-discovered sets.

Yaml Configuration
==================

If you do not have a `CustomFormSetup.yaml` then create it in the path of the settings and past the following snippet.
Here you have to adjust the path to your extension.

.. code-block:: yaml
    :caption: EXT:my_extension/Configuration/Yaml/CustomFormSetup.yaml
    imports:
      - { resource: "./Form/Elements.yaml" }

    TYPO3:
      CMS:
        Form:
          persistenceManager:
            allowSaveToExtensionPaths: true
            allowedFileMounts:
              100: 1:/forms/
            allowedExtensionPaths:
              10: EXT:my_extension/Resource/Private/Extensions/LiaForm/
          prototypes:
            standard:
              formEditor:
                formEditorFluidConfiguration:
                  partialRootPaths:
                    20: "EXT:lia_form/Resources/Private/Backend/Partials/FormEditor/"
                    30: "EXT:my_extension/Resources/Private/Extensions/LiaForm/Backend/Partials/FormEditor/"
              formElementsDefinition:
                Form:
                  renderingOptions:
                    templateRootPaths:
                      100: "EXT:lia_form/Resources/Private/Frontend/Templates/"
                      200: "EXT:my_extension/Resources/Private/Extensions/LiaForm/Frontend/Templates/"
                    partialRootPaths:
                      100: "EXT:lia_form/Resources/Private/Frontend/Partials/"
                      200: "EXT:my_extension/Resources/Private/Extensions/LiaForm/Frontend/Partials/"
                    translation:
                      translationFiles:
                        # translation files for the frontend
                        10: "EXT:form/Resources/Private/Language/locallang.xlf"
                        20: "EXT:lia_form/Resources/Private/Language/locallang_forms.xlf"
                        30: "EXT:my_extension/Resources/Private/Extensions/LiaForm/Language/locallang_forms.xlf"
                GridRow:
                  properties:
                    elementClassAttribute: "container"
                    gridColumnClassAutoConfiguration:
                      gridSize: 24
                      viewPorts:
                        sd:
                          classPattern: "grid__col-sd-{@numbersOfColumnsToUse}"
                        md:
                          classPattern: "grid__col-md-{@numbersOfColumnsToUse}"
                        xs:
                          classPattern: "grid__col-xs-{@numbersOfColumnsToUse}"
                        sm:
                          classPattern: "grid__col-sm-{@numbersOfColumnsToUse}"
                        lg:
                          classPattern: "grid__col-lg-{@numbersOfColumnsToUse}"
                ### FORM ELEMENTS: UPLOADS ###
                FileUpload:
                  formEditor:
                    editors:
                      300:
                        selectOptions:
                          80:
                            value: "application/zip"
                            label: "Documents (zip)"
                          90:
                            value: "application/rar"
                            label: "Documents (rar)"
                      400:
                        selectOptions:
                          30:
                            value: "typo3temp/myFormUploadDestination/"
                            label: "typo3temp/myFormUploadDestination/"
                          30:
                            value: "typo3temp/myFormUploadDestination2/"
                            label: "typo3temp/myFormUploadDestination2/"

..  toctree::
    :local:
    :titlesonly:
    :hidden:

    ExampleConfig
