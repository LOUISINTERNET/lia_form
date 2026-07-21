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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface as ExtbaseConfigurationManagerInterface;
use TYPO3\CMS\Form\Mvc\Configuration\ConfigurationManagerInterface as ExtFormConfigurationManagerInterface;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManagerInterface;

class FlexFormHook
{
    /**
     * Stores the value of settings.cssClass in formDefinitionOverrides to be used by the form
     */
    public function processDatamap_postProcessFieldArray(string $status, string $table, string|int $id, array &$fieldArray, DataHandler &$reference): void
    {
        if ($status !== 'update' || $table !== 'tt_content' || !isset($fieldArray['pi_flexform']) || empty($fieldArray['pi_flexform']) || $reference->checkValue_currentRecord['CType'] !== 'form_formframework') {
            return;
        }

        $flexFormTools = GeneralUtility::makeInstance(FlexFormTools::class);
        $flexFormArray = GeneralUtility::xml2array($fieldArray['pi_flexform']);

        $cssClass = '';
        $persistenceIdentifier = '';
        foreach ($flexFormArray['data']['sDEF']['lDEF'] as $key => $data) {
            if ($key === 'settings.cssClass') {
                $cssClass = $data['vDEF'];
            }

            if ($key === 'settings.persistenceIdentifier') {
                $persistenceIdentifier = $data['vDEF'];
            }

            if (str_contains((string)$key, 'settings.formDefinitionOverrides.') && str_contains((string)$key, '.renderingOptions.cssClass')) {
                unset($flexFormArray['data']['sDEF']['lDEF'][$key]);
            }
        }

        // presave deletion of keys
        $fieldArray['pi_flexform'] = $flexFormTools->flexArray2Xml($flexFormArray);
        if (!$cssClass || !$persistenceIdentifier) {
            return;
        }

        try {
            $extbaseConfigurationManager = GeneralUtility::makeInstance(ExtbaseConfigurationManagerInterface::class);
            $typoScriptSettings = $extbaseConfigurationManager->getConfiguration(ExtbaseConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'form');
            $extFormConfigurationManager = GeneralUtility::makeInstance(ExtFormConfigurationManagerInterface::class);
            $formSettings = $extFormConfigurationManager->getYamlConfiguration($typoScriptSettings, true);
            $formPersistenceManager = GeneralUtility::makeInstance(FormPersistenceManagerInterface::class);

            $form = $formPersistenceManager->load($persistenceIdentifier, $formSettings, $typoScriptSettings);
        } catch (\Throwable) {
            return;
        }

        if (!$formIdentifier = $form['identifier']) {
            return;
        }

        $flexFormArray['data']['sDEF']['lDEF'][sprintf('settings.formDefinitionOverrides.%s.renderingOptions.cssClass', $formIdentifier)]['vDEF'] = $cssClass;
        $fieldArray['pi_flexform']  = $flexFormTools->flexArray2Xml($flexFormArray);
    }
}
