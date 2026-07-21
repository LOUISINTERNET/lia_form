<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Hooks;

use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface as ExtbaseConfigurationManagerInterface;
use TYPO3\CMS\Form\Mvc\Configuration\ConfigurationManagerInterface as ExtFormConfigurationManagerInterface;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManager;

/**
 * DataHandler hook for form FlexForm processing.
 *
 * Stores the value of settings.cssClass in formDefinitionOverrides
 * to be used by the form.
 *
 * Note: This class uses GeneralUtility::makeInstance() instead of constructor injection
 * because DataHandler hooks are instantiated by TYPO3 Core without DI container support.
 * This is a documented TYPO3 limitation for hook classes.
 *
 * @author LOUIS INTERNET <devs@louis.info>
 */
class FlexFormHook
{
    /**
     * Process the FlexForm data after DataHandler field array processing.
     *
     * Note: The $id parameter uses string|int union type because TYPO3 DataHandler
     * passes string for new records (e.g., "NEW123") and int for existing records.
     * This is required by the TYPO3 DataHandler hook interface.
     *
     * @param string $status The status (new or update)
     * @param string $table The table name
     * @param string|int $id The record ID (string for new records, int for existing)
     * @param array<string, mixed> $fieldArray Reference to the field array
     * @param DataHandler $reference The DataHandler instance
     */
    public function processDatamap_postProcessFieldArray(
        string $status,
        string $table,
        string|int $id,
        array &$fieldArray,
        DataHandler &$reference
    ): void {
        if (
            $status !== 'update'
            || $table !== 'tt_content'
            || !isset($fieldArray['pi_flexform'])
            || empty($fieldArray['pi_flexform'])
            || $reference->checkValue_currentRecord['CType'] !== 'form_formframework'
        ) {
            return;
        }

        $flexFormTools = GeneralUtility::makeInstance(FlexFormTools::class);
        $flexFormArray = GeneralUtility::xml2array($fieldArray['pi_flexform']);

        if (!is_array($flexFormArray)) {
            return;
        }

        // Validate FlexForm structure before accessing nested keys
        if (
            !isset($flexFormArray['data']['sDEF']['lDEF'])
            || !is_array($flexFormArray['data']['sDEF']['lDEF'])
        ) {
            return;
        }

        $cssClass = '';
        $persistenceIdentifier = '';
        foreach ($flexFormArray['data']['sDEF']['lDEF'] as $key => $data) {
            if ($key === 'settings.cssClass') {
                $cssClass = $data['vDEF'];
            }

            if ($key === 'settings.persistenceIdentifier') {
                $persistenceIdentifier = $data['vDEF'];
            }

            // Clean up old override keys for cssClass
            if (
                str_contains((string)$key, 'settings.formDefinitionOverrides.')
                && str_contains((string)$key, '.renderingOptions.cssClass')
            ) {
                unset($flexFormArray['data']['sDEF']['lDEF'][$key]);
            }
        }

        // Pre-save deletion of keys.
        $fieldArray['pi_flexform'] = $flexFormTools->flexArray2Xml($flexFormArray);
        if ($cssClass === '' || $persistenceIdentifier === '') {
            return;
        }

        try {
            $extbaseConfigurationManager = GeneralUtility::makeInstance(ExtbaseConfigurationManagerInterface::class);
            $typoScriptSettings = $extbaseConfigurationManager->getConfiguration(
                ExtbaseConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'form'
            );
            $extFormConfigurationManager = GeneralUtility::makeInstance(ExtFormConfigurationManagerInterface::class);
            $formSettings = $extFormConfigurationManager->getYamlConfiguration($typoScriptSettings, true);
            $formPersistenceManager = GeneralUtility::makeInstance(FormPersistenceManager::class);

            $form = $formPersistenceManager->load($persistenceIdentifier, $formSettings, null);
        } catch (\Throwable $throwable) {
            // INTENTIONAL SILENT FAILURE: This hook processes form FlexForm during content save.
            // Re-throwing would block the entire save operation, which is worse than losing cssClass.
            // The error is logged so administrators can diagnose it.
            // Editors can still save content, and cssClass will be missing until fixed.
            GeneralUtility::makeInstance(LogManager::class)->getLogger(self::class)->error(
                'FlexFormHook: ' . $throwable->getMessage() . ' in ' . $throwable->getFile() . ':' . $throwable->getLine()
            );
            return;
        }

        $formIdentifier = $form['identifier'] ?? null;
        if ($formIdentifier === null) {
            return;
        }

        $flexFormArray['data']['sDEF']['lDEF'][sprintf(
            'settings.formDefinitionOverrides.%s.renderingOptions.cssClass',
            $formIdentifier
        )]['vDEF'] = $cssClass;
        $fieldArray['pi_flexform'] = $flexFormTools->flexArray2Xml($flexFormArray);
    }
}
