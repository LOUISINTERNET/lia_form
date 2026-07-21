<?php

/*
* This file is part of the "lia_form" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*/

namespace LIA\LiaForm\Services;

use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * This service provide some function to get data from the typoscript of this Extension.
 */
class TypoScriptReaderService
{
    /**
     * Typoscript plugin settings.
     */
    protected static array $formPluginSettings;

    /**
     * Return the typoscript plugin settings.
     */
    public static function getFormSettings(): array
    {
        if (empty(self::$formPluginSettings)) {
            self::initializeService();
        }

        return self::$formPluginSettings;
    }

    /**
     * Check if the ajax is active for the form plugin.
     */
    public static function isAjaxActive(): bool
    {
        if (empty(self::$formPluginSettings)) {
            self::initializeService();
        }

        if (empty(self::$formPluginSettings['ajax'])) {
            return false;
        }

        return (bool)self::$formPluginSettings['ajax']['active'];
    }

    /**
     * Get the pageNum fallback value from the typoscript or return null if it does not exist.
     */
    public static function getContentPageTypeFallback(): ?int
    {
        if (empty(self::$formPluginSettings)) {
            self::initializeService();
        }

        if (empty(self::$formPluginSettings['ajax']['getContentPageTypeFallback'])) {
            return null;
        }

        return (int)self::$formPluginSettings['ajax']['getContentPageTypeFallback'];
    }

    /**
     * Initialize this service class.
     */
    private static function initializeService(): void
    {
        $coreTyposcriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $coreConfigurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);

        self::$formPluginSettings = $coreTyposcriptService->convertTypoScriptArrayToPlainArray(
            $coreConfigurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT)
        )['plugin']['tx_form']['settings'] ?? [];
    }
}
