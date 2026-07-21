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

To override the default typoscript and yaml configuration create a `Setup.typoscript` in `EXT:my_extension/Configuration/TypoScript/Extensions/LiaForm/` folder.
Now copy this snippet in this file and adjust the path to your `CustomFormSetup.yaml` if you already have on otherwise create it in the set path and adjust the
extension name in this path.

.. code-block:: typoscript
    :caption: EXT:my_extension/Configuration/TypoScript/Extensions/LiaForm

    @import 'EXT:form/Configuration/TypoScript/'

    plugin.tx_form {
      settings {
        yamlConfigurations {
          100 = EXT:lia_form/Configuration/Yaml/LiaFormSetup.yaml
          150 = EXT:my_extension/Configuration/Yaml/CustomFormSetup.yaml
          199 = EXT:lia_form/Configuration/Yaml/FormElements/Form.yaml
          207 = EXT:lia_form/Configuration/Yaml/FormElements/LiaDatePicker.yaml
          208 = EXT:lia_form/Configuration/Yaml/FormElements/HtmlCode.yaml
          209 = EXT:lia_form/Configuration/Yaml/FormElements/DataProtection.yaml
          210 = EXT:lia_form/Configuration/Yaml/FormElements/LiaSiteTitle.yaml
          271 = EXT:lia_form/Configuration/Yaml/Finisher/EmailToSender.yaml
          281 = EXT:lia_form/Configuration/Yaml/FormElements/LiaParameterHidden.yaml
          282 = EXT:lia_form/Configuration/Yaml/FormElements/PhoneAndAreaCode.yaml
        }
      }
    }

    module.tx_form {
      settings {
        yamlConfigurations {
          100 = EXT:lia_form/Configuration/Yaml/LiaFormSetup.yaml
          150 = EXT:my_extension/Configuration/Yaml/CustomFormSetup.yaml
          199 = EXT:lia_form/Configuration/Yaml/FormElements/Form.yaml
          207 = EXT:lia_form/Configuration/Yaml/FormElements/LiaDatePicker.yaml
          208 = EXT:lia_form/Configuration/Yaml/FormElements/HtmlCode.yaml
          209 = EXT:lia_form/Configuration/Yaml/FormElements/DataProtection.yaml
          210 = EXT:lia_form/Configuration/Yaml/FormElements/LiaSiteTitle.yaml
          271 = EXT:lia_form/Configuration/Yaml/Finisher/EmailToSender.yaml
          281 = EXT:lia_form/Configuration/Yaml/FormElements/LiaParameterHidden.yaml
          282 = EXT:lia_form/Configuration/Yaml/FormElements/PhoneAndAreaCode.yaml
        }
      }
    }


Now you have to load this typoscript in you `setup.typoscript`.

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
